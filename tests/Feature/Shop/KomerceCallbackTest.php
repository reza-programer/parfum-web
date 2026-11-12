<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\PaymentReceivedMail;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Sleep;
use Tests\Support\FakeKomerce;

beforeEach(function () {
    Mail::fake();
    Sleep::fake();
    $this->komerce = FakeKomerce::register();
    $this->order = Order::factory()->create(['total' => 250000, 'expires_at' => now()->addDay()]);
    $this->payment = app(PaymentService::class)->create($this->order, 'va:BCA');
});

function sendCallback(array $payload, ?string $signature = null)
{
    $body = json_encode($payload);
    $signature ??= hash_hmac('sha256', $body, 'test-callback-key');

    return test()->call('POST', route('payments.komerce.callback'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_CALLBACK_API_KEY' => $signature,
    ], $body);
}

it('menolak callback dengan tanda tangan salah tanpa mengubah apa pun', function () {
    $this->komerce->pay($this->payment->provider_payment_id);

    sendCallback(['payment_id' => $this->payment->provider_payment_id, 'status' => 'PAID'], 'salah')->assertUnauthorized();
    sendCallback(['payment_id' => $this->payment->provider_payment_id, 'status' => 'PAID'], '')->assertUnauthorized();

    expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and($this->komerce->totalStatusCalls())->toBe(0);
});

it('callback valid memicu cek status ke Komerce lalu pesanan jadi dibayar', function () {
    $this->komerce->pay($this->payment->provider_payment_id);

    sendCallback(['payment_id' => $this->payment->provider_payment_id, 'status' => 'PAID'])->assertOk();

    expect($this->order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Paid)
        ->and($this->komerce->totalStatusCalls())->toBe(1);
    Mail::assertQueued(PaymentReceivedMail::class, 1);
});

it('isi payload tidak dipercaya: status tetap dari API cek status', function () {
    // Komerce masih PENDING walau payload mengaku PAID.
    sendCallback(['payment_id' => $this->payment->provider_payment_id, 'status' => 'PAID', 'amount' => 250000])->assertOk();

    expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
    Mail::assertNothingQueued();
});

it('callback ganda hanya mengirim satu email', function () {
    $this->komerce->pay($this->payment->provider_payment_id);

    sendCallback(['payment_id' => $this->payment->provider_payment_id])->assertOk();
    sendCallback(['payment_id' => $this->payment->provider_payment_id])->assertOk();

    expect($this->order->fresh()->status)->toBe(OrderStatus::Paid);
    Mail::assertQueued(PaymentReceivedMail::class, 1);
});

it('mengenali pembayaran dari order_id (reference) atau payload bersarang di data', function () {
    $this->komerce->pay($this->payment->provider_payment_id);

    sendCallback(['data' => ['order_id' => $this->payment->reference, 'status' => 'PAID']])->assertOk();

    expect($this->order->fresh()->status)->toBe(OrderStatus::Paid);
});

it('pembayaran yang tidak dikenal dijawab 404', function () {
    sendCallback(['payment_id' => 'KPAY-ffff/KM/2026'])->assertNotFound();
    sendCallback(['status' => 'PAID'])->assertNotFound();
});

it('Komerce tidak bisa dihubungi saat cek status dijawab 503 supaya callback dikirim ulang', function () {
    $this->komerce->down = true;

    sendCallback(['payment_id' => $this->payment->provider_payment_id])->assertStatus(503);

    expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
});
