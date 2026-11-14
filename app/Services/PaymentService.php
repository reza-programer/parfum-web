<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\PaymentException;
use App\Models\Order;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use Throwable;

/**
 * Satu-satunya pintu ke Komerce Payment API (VA + QRIS). Livewire, Filament, dan controller
 * tidak memanggil API langsung.
 *
 * Status Dibayar hanya berasal dari server ke server: hasil API cek status Komerce, yang dipanggil
 * dari polling halaman pesanan, aksi admin, scheduler, atau callback yang tanda tangannya valid.
 */
class PaymentService
{
    public const SANDBOX_URL = 'https://api-sandbox.collaborator.komerce.id/user';

    public const PRODUCTION_URL = 'https://api.collaborator.komerce.id/user';

    /** Komerce membatasi cek status 1 request per 3 detik per payment. */
    public const STATUS_INTERVAL_SECONDS = 3;

    private const METHODS_TTL = 60 * 60;

    private const VA_MIN_SECONDS = 3600;

    public function __construct(private readonly OrderService $orders) {}

    public function baseUrl(): string
    {
        return rtrim((string) (config('services.komerce.base_url')
            ?: (config('services.komerce.production') ? self::PRODUCTION_URL : self::SANDBOX_URL)), '/');
    }

    public function isSandbox(): bool
    {
        return ! config('services.komerce.production');
    }

    /**
     * Metode bayar yang menerima nominal ini, urut seperti dari Komerce.
     *
     * @return list<PaymentMethod>
     */
    public function methods(int $amount): array
    {
        $rows = Cache::get('komerce.methods');

        if (! is_array($rows)) {
            $rows = $this->call(fn (PendingRequest $http) => $http->get('api/v1/user/methods'), 'Metode pembayaran belum bisa dimuat. Coba lagi sebentar.');

            if ($rows) {
                Cache::put('komerce.methods', $rows, self::METHODS_TTL);
            }
        }

        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map(fn (array $row) => PaymentMethod::fromArray($row))
            ->filter(fn (PaymentMethod $m) => $m->isValid() && $m->accepts($amount))
            ->values()
            ->all();
    }

    /**
     * Buat VA atau QRIS untuk pesanan. Percobaan VA sebelumnya yang masih menunggu dibatalkan.
     */
    public function create(Order $order, string $methodKey): Payment
    {
        $lock = Cache::lock("komerce.create.{$order->id}", 20);

        if (! $lock->get()) {
            throw new PaymentException('Pembayaran sedang dibuat. Tunggu sebentar.');
        }

        try {
            return $this->createLocked($order->fresh(['user']), $methodKey);
        } finally {
            $lock->release();
        }
    }

    /**
     * Cek status ke Komerce lalu terapkan. Tanpa $wait, cek dilewati kalau payment ini baru dicek
     * kurang dari 3 detik lalu. Dengan $wait (callback, admin), tunggu sampai boleh.
     */
    public function refresh(Payment $payment, bool $wait = false): Payment
    {
        if ($payment->status === PaymentStatus::Paid || ! $payment->provider_payment_id) {
            return $payment;
        }

        if (! $this->claimStatusSlot($payment, $wait)) {
            return $payment;
        }

        $data = $this->call(
            fn (PendingRequest $http) => $http->get('api/v1/user/payment/status/'.rawurlencode($payment->provider_payment_id)),
            'Status pembayaran belum bisa dicek. Coba lagi sebentar.',
        );

        return $this->apply($payment, $data);
    }

    /**
     * Cek semua percobaan bayar pesanan ini yang masih menunggu (termasuk QRIS lama yang masih berlaku).
     */
    public function refreshPending(Order $order, bool $wait = false): void
    {
        $order->payments()->where('status', PaymentStatus::Pending)->orderBy('id')->get()
            ->each(fn (Payment $payment) => $this->refresh($payment, $wait));
    }

    /**
     * Dipanggil scheduler sebelum orders:expire: pesanan yang lewat batas tapi ternyata sudah dibayar
     * tidak ikut dibatalkan. Error satu payment tidak menghentikan yang lain.
     */
    public function syncOverdue(): void
    {
        Payment::query()
            ->where('status', PaymentStatus::Pending)
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::PendingPayment)->where('expires_at', '<=', now()))
            ->lazyById(100)
            ->each(function (Payment $payment) {
                try {
                    $this->refresh($payment);
                } catch (PaymentException) {
                    // Sudah dicatat di log; dicoba lagi menit berikutnya.
                }
            });
    }

    /**
     * Batalkan percobaan bayar yang masih menunggu. QRIS tidak bisa dibatalkan di Komerce,
     * jadi dibiarkan sampai kedaluwarsa sendiri (5 menit).
     */
    public function cancelPending(Order $order, ?Payment $except = null): void
    {
        $order->payments()
            ->where('status', PaymentStatus::Pending)
            ->where('payment_type', Payment::TYPE_VA)
            ->when($except, fn ($q) => $q->whereKeyNot($except->id))
            ->get()
            ->each(fn (Payment $payment) => $this->cancel($payment));
    }

    /**
     * HMAC-SHA256 dari raw body dengan KOMERCE_CALLBACK_KEY, dikirim di header X-Callback-Api-Key.
     * Dokumentasi tidak menyebut encoding-nya, jadi hex dan base64 sama-sama diterima.
     */
    public function verifyCallback(string $rawBody, ?string $signature): bool
    {
        $key = (string) config('services.komerce.callback_key');
        $signature = trim((string) $signature);

        if ($key === '' || $signature === '') {
            return false;
        }

        $hash = hash_hmac('sha256', $rawBody, $key, true);

        return hash_equals(bin2hex($hash), strtolower($signature))
            || hash_equals(base64_encode($hash), $signature);
    }

    private function createLocked(Order $order, string $methodKey): Payment
    {
        if ($order->status !== OrderStatus::PendingPayment || $order->expires_at->isPast()) {
            throw new PaymentException('Pesanan ini sudah tidak bisa dibayar.');
        }

        $method = collect($this->methods($order->total))->first(fn (PaymentMethod $m) => $m->key() === $methodKey);

        if (! $method) {
            throw new PaymentException('Metode pembayaran ini tidak tersedia untuk pesananmu. Pilih metode lain.');
        }

        $this->cancelPending($order);

        $reference = $order->code.'-'.($order->payments()->count() + 1);
        $snapshot = $order->address_snapshot ?? [];

        $payload = array_filter([
            'order_id' => $reference,
            'payment_type' => $method->type,
            'channel_code' => $method->bankCode,
            'amount' => $order->total,
            'expiry_duration' => $method->isQris() ? null : max(self::VA_MIN_SECONDS, (int) now()->diffInSeconds($order->expires_at)),
            'customer' => [
                'name' => (string) ($snapshot['recipient'] ?? $order->user->name),
                'email' => $order->user->email,
                // Form alamat menerima +62; Komerce hanya menerima digit.
                'phone' => ltrim((string) ($snapshot['phone'] ?? $order->user->phone ?? ''), '+'),
            ],
            'items' => [['name' => "Pesanan {$order->code}", 'quantity' => 1, 'price' => $order->total]],
            ...$this->callbackFields(),
        ], fn ($value) => $value !== null);

        $data = $this->call(
            fn (PendingRequest $http) => $http->asJson()->post('api/v1/user/payment/create', $payload),
            'Pembayaran belum bisa dibuat. Coba lagi sebentar.',
        );

        return $order->payments()->create([
            'reference' => $reference,
            'provider_payment_id' => (string) $data['payment_id'],
            'payment_type' => $method->type,
            'channel_code' => ($data['bank_code'] ?? '') ?: $method->bankCode,
            'bank_name' => ($data['bank_name'] ?? '') ?: ($method->isQris() ? null : $method->displayName),
            'va_number' => ($data['va_number'] ?? '') ?: null,
            'qr_string' => ($data['qr_string'] ?? '') ?: null,
            'payment_url' => ($data['payment_url'] ?? '') ?: null,
            'amount' => (int) ($data['amount'] ?? $order->total),
            'status' => PaymentStatus::fromKomerce($data['status'] ?? null) ?? PaymentStatus::Pending,
            'expires_at' => $this->parseTime($data['expired_at'] ?? null),
            'raw_response' => $data,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function callbackFields(): array
    {
        $key = (string) config('services.komerce.callback_key');

        if ($key === '') {
            return [];
        }

        return [
            'callback_url' => (string) (config('services.komerce.callback_url') ?: route('payments.komerce.callback')),
            'callback_API_KEY' => $key,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function apply(Payment $payment, array $data): Payment
    {
        $status = PaymentStatus::fromKomerce($data['status'] ?? null);

        if ($status === PaymentStatus::Paid) {
            if ((int) ($data['amount'] ?? 0) !== $payment->amount) {
                Log::warning('Komerce: nominal dibayar tidak sama dengan tagihan, tidak ditandai dibayar', [
                    'payment_id' => $payment->id, 'expected' => $payment->amount, 'received' => $data['amount'] ?? null,
                ]);

                return $payment;
            }

            return $this->settle($payment, $data);
        }

        if (in_array($status, [PaymentStatus::Expired, PaymentStatus::Canceled], true) && $payment->isPending()) {
            $payment->update(['status' => $status, 'raw_response' => $data]);
        }

        return $payment;
    }

    /**
     * Uang sudah masuk. Kalau pesanan masih menunggu bayar dan belum dilunasi percobaan lain,
     * pesanan jadi Dibayar. Kalau tidak (sudah batal atau bayar dua kali), tandai perlu refund manual.
     *
     * @param  array<string, mixed>  $data
     */
    private function settle(Payment $payment, array $data): Payment
    {
        $paidOrder = DB::transaction(function () use ($payment, $data) {
            // Urutan lock sama dengan OrderService: order dulu, baru payment.
            $order = Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === PaymentStatus::Paid) {
                return null;
            }

            $paidByOther = $order->payments()->where('status', PaymentStatus::Paid)->whereKeyNot($locked->id)->exists();
            $paysOrder = $order->status === OrderStatus::PendingPayment && ! $paidByOther;

            $locked->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => $this->parseTime($data['paid_at'] ?? null) ?? now(),
                'needs_refund' => ! $paysOrder,
                'raw_response' => $data,
            ]);

            if (! $paysOrder) {
                Log::warning('Komerce: pembayaran masuk untuk pesanan yang tidak menunggu bayar, perlu refund manual', [
                    'order' => $order->code, 'payment' => $locked->reference, 'order_status' => $order->status->value,
                ]);

                return null;
            }

            return $this->orders->markPaid($order);
        });

        if ($paidOrder) {
            try {
                $this->cancelPending($paidOrder, except: $payment);
            } catch (Throwable $e) {
                Log::warning('Komerce: gagal membatalkan VA lain setelah lunas', ['order' => $paidOrder->code, 'error' => $e->getMessage()]);
            }
        }

        return $payment->refresh();
    }

    private function cancel(Payment $payment): void
    {
        try {
            $this->call(
                fn (PendingRequest $http) => $http->asJson()->post('api/v1/user/payment/cancel', [
                    'payment_id' => $payment->provider_payment_id,
                    'reason' => 'Diganti atau pesanan dibatalkan',
                ]),
                'Pembayaran lama belum bisa dibatalkan.',
            );
        } catch (PaymentException) {
            // Misalnya sudah dibayar barusan: biarkan pending, cek status berikutnya yang menentukan.
            return;
        }

        $payment->update(['status' => PaymentStatus::Canceled]);
    }

    private function claimStatusSlot(Payment $payment, bool $wait): bool
    {
        $key = "komerce.status.{$payment->id}";

        for ($attempt = 0; $attempt <= self::STATUS_INTERVAL_SECONDS; $attempt++) {
            if (Cache::add($key, true, self::STATUS_INTERVAL_SECONDS)) {
                return true;
            }

            if (! $wait) {
                return false;
            }

            Sleep::sleep(1);
        }

        return false;
    }

    /**
     * @param  callable(PendingRequest): Response  $request
     * @return array<int|string, mixed>
     */
    private function call(callable $request, string $message): array
    {
        try {
            $response = $request(
                Http::baseUrl($this->baseUrl())
                    ->withHeaders(['x-api-key' => (string) config('services.komerce.payment_key')])
                    ->acceptJson()
                    ->timeout(15)
            );
        } catch (Throwable $e) {
            Log::warning('Komerce tidak bisa dihubungi', ['error' => $e->getMessage()]);
            throw new PaymentException($message, previous: $e);
        }

        if (! $response->successful() || ! is_array($response->json('data'))) {
            Log::warning('Komerce mengembalikan error', [
                'status' => $response->status(),
                'meta' => $response->json('meta'),
                'data' => $response->json('data'),
            ]);
            throw new PaymentException($message);
        }

        return $response->json('data');
    }

    /**
     * Komerce memakai dua format: ISO 8601 (+07:00) di create, "Y-m-d H:i:s" (WIB) di status.
     */
    private function parseTime(mixed $value): ?CarbonImmutable
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value, 'Asia/Jakarta')->setTimezone(config('app.timezone'));
        } catch (Throwable) {
            return null;
        }
    }
}
