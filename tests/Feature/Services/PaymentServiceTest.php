<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\PaymentException;
use App\Mail\PaymentReceivedMail;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\FakeKomerce;

beforeEach(function () {
    Mail::fake();
    $this->komerce = FakeKomerce::register();
    $this->payments = app(PaymentService::class);
    $this->order = Order::factory()->create([
        'code' => 'INV-20261112-ABCDE',
        'subtotal' => 238000,
        'shipping_cost' => 12000,
        'total' => 250000,
        'expires_at' => now()->addDay(),
        'address_snapshot' => ['recipient' => 'Sari Dewi', 'phone' => '081311112222'],
    ]);
});

function createRequests(): Collection
{
    return collect(Http::recorded())->map(fn ($pair) => $pair[0])
        ->filter(fn (Request $r) => str_ends_with($r->url(), '/payment/create'))
        ->values();
}

it('memakai base URL sandbox kecuali mode produksi', function () {
    config(['services.komerce.base_url' => null, 'services.komerce.production' => false]);
    expect($this->payments->baseUrl())->toBe('https://api-sandbox.collaborator.komerce.id/user')
        ->and($this->payments->isSandbox())->toBeTrue();

    config(['services.komerce.production' => true]);
    expect($this->payments->baseUrl())->toBe('https://api.collaborator.komerce.id/user')
        ->and($this->payments->isSandbox())->toBeFalse();
});

it('mengirim nomor berawalan plus sebagai digit tanpa mengubah snapshot', function (string $method) {
    $this->order->update(['address_snapshot' => ['recipient' => 'Sari Dewi', 'phone' => '+6281311112222']]);

    $this->payments->create($this->order, $method);

    expect(createRequests()->sole()['customer']['phone'])->toBe('6281311112222')
        ->and($this->order->fresh()->address_snapshot['phone'])->toBe('+6281311112222');
})->with(['va:BCA', 'qris']);

it('daftar metode mengikuti batas nominal dan di-cache', function () {
    $keys = fn (int $amount) => collect($this->payments->methods($amount))->map->key()->all();

    expect($keys(250000))->toBe(['va:BCA', 'va:BNI', 'va:MANDIRI', 'va:BRI', 'qris'])
        ->and($keys(15_000_000))->not->toContain('qris')
        ->and($keys(5000))->toBe([]);

    Http::assertSentCount(1);
});

it('membuat VA dengan payload sesuai API Komerce', function () {
    $payment = $this->payments->create($this->order, 'va:BCA');

    $request = createRequests()->sole();
    expect($request->header('x-api-key'))->toBe(['test-komerce-key'])
        ->and($request['order_id'])->toBe('INV-20261112-ABCDE-1')
        ->and($request['payment_type'])->toBe('bank_transfer')
        ->and($request['channel_code'])->toBe('BCA')
        ->and($request['amount'])->toBe(250000)
        ->and($request['expiry_duration'])->toBeGreaterThan(86000)->toBeLessThanOrEqual(86400)
        ->and($request['items'])->toBe([['name' => 'Pesanan INV-20261112-ABCDE', 'quantity' => 1, 'price' => 250000]])
        ->and($request['customer'])->toBe(['name' => 'Sari Dewi', 'email' => $this->order->user->email, 'phone' => '081311112222'])
        ->and($request['callback_url'])->toBe(route('payments.komerce.callback'))
        ->and($request['callback_API_KEY'])->toBe('test-callback-key');

    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->provider_payment_id)->toBe('KPAY-0001/KM/2026')
        ->and($payment->reference)->toBe('INV-20261112-ABCDE-1')
        ->and($payment->isVirtualAccount())->toBeTrue()
        ->and($payment->channel_code)->toBe('BCA')
        ->and($payment->bank_name)->toBe('Bank Central Asia')
        ->and($payment->va_number)->toBe('381659900000001')
        ->and($payment->amount)->toBe(250000)
        ->and($payment->payment_url)->toStartWith('https://pay-sandbox.komerce.my.id/')
        ->and($payment->expires_at->between(now()->addHours(23), now()->addDay()->addMinute()))->toBeTrue();
});

it('VA berlaku minimal 1 jam walau pesanan hampir kedaluwarsa', function () {
    $this->order->update(['expires_at' => now()->addMinutes(10)]);

    $this->payments->create($this->order, 'va:BNI');

    expect(createRequests()->sole()['expiry_duration'])->toBe(3600);
});

it('membuat QRIS yang berlaku 5 menit', function () {
    $payment = $this->payments->create($this->order, 'qris');

    $request = createRequests()->sole();
    expect($request['payment_type'])->toBe('qris')
        ->and($request->data())->not->toHaveKey('channel_code')
        ->and($request->data())->not->toHaveKey('expiry_duration');

    expect($payment->isVirtualAccount())->toBeFalse()
        ->and($payment->qr_string)->toBe('some-random-qr-string')
        ->and($payment->va_number)->toBeNull()
        ->and($payment->channel_code)->toBeNull()
        ->and($payment->expires_at->between(now()->addMinutes(4), now()->addMinutes(6)))->toBeTrue();
});

it('ganti metode membatalkan VA lama dan nomor referensi bertambah', function () {
    $first = $this->payments->create($this->order, 'va:BCA');
    $second = $this->payments->create($this->order->fresh(), 'va:MANDIRI');

    expect($first->fresh()->status)->toBe(PaymentStatus::Canceled)
        ->and($this->komerce->payments[$first->provider_payment_id]['status'])->toBe('CANCELED')
        ->and($second->reference)->toBe('INV-20261112-ABCDE-2')
        ->and($this->order->fresh()->payment->is($second))->toBeTrue();
});

it('QRIS lama tetap menunggu karena Komerce tidak bisa membatalkan QRIS', function () {
    $qr = $this->payments->create($this->order, 'qris');
    $this->payments->create($this->order->fresh(), 'va:BCA');

    expect($qr->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('menolak metode yang tidak tersedia dan pesanan yang tidak menunggu bayar', function () {
    expect(fn () => $this->payments->create($this->order, 'va:XYZ'))->toThrow(PaymentException::class);

    $paid = Order::factory()->status(OrderStatus::Paid)->create(['total' => 250000]);
    expect(fn () => $this->payments->create($paid, 'va:BCA'))->toThrow(PaymentException::class);

    $expired = Order::factory()->create(['total' => 250000, 'expires_at' => now()->subMinute()]);
    expect(fn () => $this->payments->create($expired, 'va:BCA'))->toThrow(PaymentException::class);

    expect(createRequests())->toBeEmpty()
        ->and(Payment::count())->toBe(0);
});

it('error dari Komerce dilempar sebagai PaymentException tanpa menyimpan payment', function () {
    $this->payments->methods(250000); // metode sudah di-cache
    $this->komerce->down = true;

    expect(fn () => $this->payments->create($this->order, 'va:BCA'))
        ->toThrow(PaymentException::class, 'Pembayaran belum bisa dibuat. Coba lagi sebentar.');

    expect(Payment::count())->toBe(0);
});

it('status PAID dengan nominal cocok menandai pesanan dibayar dan email terkirim sekali', function () {
    $payment = $this->payments->create($this->order, 'va:BCA');
    $this->komerce->pay($payment->provider_payment_id);

    $this->payments->refresh($payment);
    $this->travel(5)->seconds();
    $this->payments->refresh($payment->fresh());

    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::Paid)
        ->and($payment->paid_at)->not->toBeNull()
        ->and($payment->needs_refund)->toBeFalse()
        ->and($this->order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($this->komerce->statusCalls[$payment->provider_payment_id])->toBe(1);

    Mail::assertQueued(PaymentReceivedMail::class, 1);
});

it('payment_id bergaris miring di-encode saat cek status', function () {
    $payment = $this->payments->create($this->order, 'va:BCA');

    $this->payments->refresh($payment);

    Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/payment/status/KPAY-0001%2FKM%2F2026'));
});

it('nominal yang tidak cocok tidak menandai dibayar', function () {
    $payment = $this->payments->create($this->order, 'va:BCA');
    $this->komerce->pay($payment->provider_payment_id, amount: 10000);

    $this->payments->refresh($payment);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
    Mail::assertNothingQueued();
});

it('status EXPIRED menandai percobaan kedaluwarsa tapi pesanan tetap menunggu bayar', function () {
    $payment = $this->payments->create($this->order, 'qris');
    $this->komerce->expire($payment->provider_payment_id);

    $this->payments->refresh($payment);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Expired)
        ->and($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
});

it('cek status ke Komerce paling sering sekali per 3 detik per pembayaran', function () {
    $payment = $this->payments->create($this->order, 'va:BCA');

    $this->payments->refresh($payment);
    $this->payments->refresh($payment);
    expect($this->komerce->totalStatusCalls())->toBe(1);

    $this->travel(4)->seconds();
    $this->payments->refresh($payment);
    expect($this->komerce->totalStatusCalls())->toBe(2);
});

it('uang masuk setelah pesanan dibatalkan ditandai perlu refund', function () {
    $payment = $this->payments->create($this->order, 'va:BCA');
    $this->komerce->pay($payment->provider_payment_id);
    $this->order->forceFill(['status' => OrderStatus::Cancelled])->save();

    $this->payments->refresh($payment);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Paid)
        ->and($payment->fresh()->needs_refund)->toBeTrue()
        ->and($this->order->fresh()->status)->toBe(OrderStatus::Cancelled);
    Mail::assertNothingQueued();
});

it('bayar dua kali hanya satu yang melunasi, sisanya perlu refund', function () {
    $qr = $this->payments->create($this->order, 'qris');
    $va = $this->payments->create($this->order->fresh(), 'va:BCA');
    $this->komerce->pay($qr->provider_payment_id);
    $this->komerce->pay($va->provider_payment_id);

    $this->payments->refreshPending($this->order->fresh());

    expect($qr->fresh()->status)->toBe(PaymentStatus::Paid)
        ->and($qr->fresh()->needs_refund)->toBeFalse()
        ->and($va->fresh()->status)->toBe(PaymentStatus::Paid)
        ->and($va->fresh()->needs_refund)->toBeTrue()
        ->and($this->order->fresh()->status)->toBe(OrderStatus::Paid);
    Mail::assertQueued(PaymentReceivedMail::class, 1);
});

it('setelah lunas, VA lain yang masih menunggu dibatalkan', function () {
    $qr = $this->payments->create($this->order, 'qris');
    $va = $this->payments->create($this->order->fresh(), 'va:BCA');
    $this->komerce->pay($qr->provider_payment_id);

    $this->payments->refresh($qr);

    expect($va->fresh()->status)->toBe(PaymentStatus::Canceled)
        ->and($this->komerce->payments[$va->provider_payment_id]['status'])->toBe('CANCELED');
});

it('memverifikasi tanda tangan callback HMAC-SHA256 (hex atau base64)', function () {
    $body = '{"payment_id":"KPAY-0001/KM/2026","status":"PAID"}';
    $raw = hash_hmac('sha256', $body, 'test-callback-key', true);

    expect($this->payments->verifyCallback($body, bin2hex($raw)))->toBeTrue()
        ->and($this->payments->verifyCallback($body, strtoupper(bin2hex($raw))))->toBeTrue()
        ->and($this->payments->verifyCallback($body, base64_encode($raw)))->toBeTrue()
        ->and($this->payments->verifyCallback($body.' ', bin2hex($raw)))->toBeFalse()
        ->and($this->payments->verifyCallback($body, null))->toBeFalse()
        ->and($this->payments->verifyCallback($body, 'test-callback-key'))->toBeFalse();

    config(['services.komerce.callback_key' => '']);
    expect($this->payments->verifyCallback($body, hash_hmac('sha256', $body, '')))->toBeFalse();
});
