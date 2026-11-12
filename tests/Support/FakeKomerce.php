<?php

namespace Tests\Support;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Tiruan Komerce Payment API untuk test. Bentuk request dan respons mengikuti hasil uji
 * sandbox asli (12 Nov 2026), termasuk payment_id bergaris miring dan QRIS yang tidak bisa dibatalkan.
 */
class FakeKomerce
{
    public const BASE = 'https://komerce.test/user';

    public const METHODS = [
        ['payment_type' => 'va', 'display_name' => 'Bank Central Asia', 'bank_code' => 'BCA', 'logo_url' => 'https://storage.googleapis.com/komerce/assets/logo/bca.png', 'min_amount' => 10000, 'max_amount' => 999999999999, 'currency' => 'IDR'],
        ['payment_type' => 'va', 'display_name' => 'Bank Negara Indonesia', 'bank_code' => 'BNI', 'logo_url' => 'https://storage.googleapis.com/komerce/assets/logo/bni.png', 'min_amount' => 10000, 'max_amount' => 50000000000, 'currency' => 'IDR'],
        ['payment_type' => 'va', 'display_name' => 'Bank Mandiri', 'bank_code' => 'MANDIRI', 'logo_url' => 'https://storage.googleapis.com/komerce/assets/logo/mandiri.png', 'min_amount' => 10000, 'max_amount' => 50000000000, 'currency' => 'IDR'],
        ['payment_type' => 'va', 'display_name' => 'Bank Rakyat Indonesia', 'bank_code' => 'BRI', 'logo_url' => 'https://storage.googleapis.com/komerce/assets/logo/bri.png', 'min_amount' => 10000, 'max_amount' => 50000000000, 'currency' => 'IDR'],
        ['payment_type' => 'qris', 'display_name' => 'QRIS', 'bank_code' => '', 'logo_url' => 'https://storage.googleapis.com/komerce/assets/logo/QRIS.png', 'min_amount' => 10000, 'max_amount' => 10000000, 'currency' => 'IDR'],
    ];

    /** @var array<string, array<string, mixed>> payment_id => data status */
    public array $payments = [];

    /** @var array<string, int> payment_id => jumlah panggilan cek status */
    public array $statusCalls = [];

    /** Semua endpoint menjawab 500. */
    public bool $down = false;

    private int $seq = 0;

    public static function register(): self
    {
        $fake = new self;

        Http::fake([self::BASE.'/*' => fn (Request $request) => $fake->handle($request)]);

        return $fake;
    }

    public function handle(Request $request)
    {
        if ($request->header('x-api-key') !== [config('services.komerce.payment_key')]) {
            return $this->fail('invalid api key', 401);
        }

        if ($this->down) {
            return $this->fail('internal server error', 500);
        }

        $path = substr(strtok($request->url(), '?'), strlen(self::BASE.'/api/v1/user/'));

        return match (true) {
            $path === 'methods' => $this->ok('success get payment methods', self::METHODS),
            $path === 'payment/create' => $this->create($request),
            str_starts_with($path, 'payment/status/') => $this->status(rawurldecode(substr($path, strlen('payment/status/')))),
            $path === 'payment/cancel' => $this->cancel($request),
            default => $this->fail('not found', 404),
        };
    }

    /** Simulasi customer membayar (seperti tombol simulate di sandbox). */
    public function pay(string $paymentId, ?int $amount = null): void
    {
        $this->payments[$paymentId]['status'] = 'PAID';
        $this->payments[$paymentId]['paid_at'] = now()->format('Y-m-d H:i:s');

        if ($amount !== null) {
            $this->payments[$paymentId]['amount'] = $amount;
        }
    }

    public function expire(string $paymentId): void
    {
        $this->payments[$paymentId]['status'] = 'EXPIRED';
    }

    public function totalStatusCalls(): int
    {
        return array_sum($this->statusCalls);
    }

    private function create(Request $request)
    {
        $type = $request['payment_type'] ?? null;

        if (! in_array($type, ['bank_transfer', 'qris'], true)) {
            return $this->fail('payment_type must be bank_transfer or qris', 422);
        }

        if ((int) ($request['amount'] ?? 0) < 10000) {
            return $this->fail('amount must be at least 10000', 422);
        }

        $isVa = $type === 'bank_transfer';
        $bank = $isVa ? collect(self::METHODS)->firstWhere('bank_code', $request['channel_code'] ?? null) : null;

        if ($isVa && ! $bank) {
            return $this->fail('channel_code is invalid', 422);
        }

        $id = sprintf('KPAY-%04x/KM/2026', ++$this->seq);
        $expiresAt = $isVa ? now()->addSeconds((int) $request['expiry_duration']) : now()->addMinutes(5);

        $this->payments[$id] = [
            'payment_id' => $id,
            'order_id' => $request['order_id'],
            'bank_code' => $bank['bank_code'] ?? '',
            'va_number' => $isVa ? '3816599'.str_pad((string) $this->seq, 8, '0', STR_PAD_LEFT) : '',
            'amount' => (int) $request['amount'],
            'merchant_receives' => (int) $request['amount'] - 4440,
            'currency' => 'IDR',
            'payment_type' => $type,
            'status' => 'PENDING',
            'sender_name' => '',
            'expired_at' => $expiresAt->format('Y-m-d H:i:s'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'paid_at' => '',
            'cancel_reason' => '',
            'canceled_at' => '',
        ];

        return $this->ok('success create payment', [
            'payment_id' => $id,
            'external_id' => 'openapi-'.Str::random(40),
            'payment_url' => 'https://pay-sandbox.komerce.my.id/'.md5($id),
            'va_number' => $this->payments[$id]['va_number'],
            'qr_string' => $isVa ? '' : 'some-random-qr-string',
            'bank_code' => $bank['bank_code'] ?? '',
            'bank_name' => $bank['display_name'] ?? '',
            'amount' => (int) $request['amount'],
            'status' => 'PENDING',
            'expired_at' => $expiresAt->toIso8601String(),
            'created_at' => now()->toIso8601String(),
        ]);
    }

    private function status(string $id)
    {
        $this->statusCalls[$id] = ($this->statusCalls[$id] ?? 0) + 1;

        return isset($this->payments[$id])
            ? $this->ok('success get payment', $this->payments[$id])
            : $this->fail('payment not found', 404);
    }

    private function cancel(Request $request)
    {
        $id = (string) ($request['payment_id'] ?? '');
        $payment = $this->payments[$id] ?? null;

        // Sandbox asli: QRIS tidak bisa dibatalkan.
        if (! $payment || $payment['payment_type'] === 'qris') {
            return Http::response(['meta' => ['message' => 'failed cancel payment', 'code' => 404, 'status' => 'error'], 'data' => 'item does not exist'], 404);
        }

        if ($payment['status'] !== 'PENDING') {
            return Http::response(['meta' => ['message' => 'failed cancel payment', 'code' => 400, 'status' => 'error'], 'data' => "Cancellation cannot be made: payment status {$payment['status']}"], 400);
        }

        $this->payments[$id]['status'] = 'CANCELED';
        $this->payments[$id]['cancel_reason'] = (string) ($request['reason'] ?? '');

        return $this->ok('success cancel payment', ['payment_id' => $id, 'status' => 'CANCELED', 'reason' => $request['reason'] ?? '']);
    }

    private function ok(string $message, array $data)
    {
        return Http::response(['meta' => ['message' => $message, 'code' => 200, 'status' => 'success'], 'data' => $data]);
    }

    private function fail(string $message, int $status)
    {
        return Http::response(['meta' => ['message' => $message, 'code' => $status, 'status' => 'failed'], 'data' => null], $status);
    }
}
