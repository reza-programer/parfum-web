<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Voucher;
use Illuminate\Database\QueryException;

it('label status sesuai PRD', function () {
    expect(array_map(fn (OrderStatus $s) => $s->label(), OrderStatus::cases()))
        ->toBe(['Menunggu Bayar', 'Dibayar', 'Dikemas', 'Dikirim', 'Selesai', 'Dibatalkan']);
});

it('order baru berstatus menunggu bayar', function () {
    expect(Order::factory()->create()->fresh()->status)->toBe(OrderStatus::PendingPayment);
});

it('item order tetap ada saat produk dihapus', function () {
    $item = OrderItem::factory()->create(['product_name' => 'Nama Parfum', 'size_ml' => 50, 'price' => 350000]);

    $item->variant->product->delete();
    $item->refresh();

    expect($item->product_variant_id)->toBeNull()
        ->and([$item->product_name, $item->size_ml, $item->price])->toBe(['Nama Parfum', 50, 350000]);
});

it('menyimpan snapshot alamat sebagai array', function () {
    $order = Order::factory()->create(['address_snapshot' => ['recipient' => 'Budi', 'city_name' => 'Bandung']]);

    expect($order->fresh()->address_snapshot['city_name'])->toBe('Bandung');
});

it('satu order bisa punya beberapa percobaan bayar, payment() adalah yang terbaru', function () {
    $order = Order::factory()->create();
    $first = Payment::factory()->for($order)->create(['status' => PaymentStatus::Expired]);
    $latest = Payment::factory()->for($order)->create();

    $order->refresh();

    expect($order->payments)->toHaveCount(2)
        ->and($order->payment->is($latest))->toBeTrue()
        ->and($first->fresh()->status)->toBe(PaymentStatus::Expired)
        ->and($latest->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('reference dan id pembayaran Komerce unik', function () {
    Payment::factory()->create(['reference' => 'INV-1-1', 'provider_payment_id' => 'KPAY-1/KM/2026']);

    expect(fn () => Payment::factory()->create(['reference' => 'INV-1-1']))->toThrow(QueryException::class)
        ->and(fn () => Payment::factory()->create(['provider_payment_id' => 'KPAY-1/KM/2026']))->toThrow(QueryException::class);
});

it('menyimpan detail VA dan QRIS dari Komerce', function () {
    $payment = Payment::factory()->create([
        'payment_type' => 'bank_transfer',
        'channel_code' => 'BCA',
        'bank_name' => 'Bank Central Asia',
        'va_number' => '381659999580702',
        'amount' => 250000,
        'expires_at' => now()->addHour(),
        'payment_url' => 'https://pay-sandbox.komerce.my.id/abc',
    ])->fresh();

    expect($payment->isVirtualAccount())->toBeTrue()
        ->and($payment->amount)->toBe(250000)
        ->and($payment->expires_at)->toBeInstanceOf(DateTimeInterface::class)
        ->and($payment->needs_refund)->toBeFalse();

    $qris = Payment::factory()->qris()->create()->fresh();

    expect($qris->isVirtualAccount())->toBeFalse()
        ->and($qris->qr_string)->not->toBeEmpty();
});

it('label status pembayaran dalam bahasa Indonesia', function () {
    expect(array_map(fn (PaymentStatus $s) => $s->label(), PaymentStatus::cases()))
        ->toBe(['Menunggu', 'Dibayar', 'Kedaluwarsa', 'Dibatalkan']);
});

it('menghubungkan order dengan item, payment, dan voucher', function () {
    $voucher = Voucher::factory()->create();
    $order = Order::factory()->for($voucher)->has(OrderItem::factory()->count(2), 'items')->create();
    Payment::factory()->for($order)->create();

    $order->refresh();

    expect($order->items)->toHaveCount(2)
        ->and($order->payment)->toBeInstanceOf(Payment::class)
        ->and($order->voucher->is($voucher))->toBeTrue()
        ->and($order->user->orders)->toHaveCount(1);
});

it('kode order unik', function () {
    Order::factory()->create(['code' => 'INV-20261111-AAAAA']);

    expect(fn () => Order::factory()->create(['code' => 'INV-20261111-AAAAA']))->toThrow(QueryException::class);
});
