<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Mail;
use Tests\Support\FakeKomerce;

beforeEach(function () {
    Mail::fake();
    $this->komerce = FakeKomerce::register();
    $this->variant = ProductVariant::factory()->create(['stock' => 3]);
    $this->order = Order::factory()->create(['total' => 250000, 'expires_at' => now()->addDay()]);
    OrderItem::factory()->for($this->order)->create(['product_variant_id' => $this->variant->id, 'qty' => 2]);
    $this->payment = app(PaymentService::class)->create($this->order, 'va:BCA');
});

it('membatalkan pesanan juga membatalkan VA yang masih menunggu di Komerce', function () {
    app(OrderService::class)->cancelUnpaid($this->order);

    expect($this->order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Canceled)
        ->and($this->komerce->payments[$this->payment->provider_payment_id]['status'])->toBe('CANCELED');
});

it('Komerce bermasalah tidak menggagalkan pembatalan pesanan', function () {
    $this->komerce->down = true;

    app(OrderService::class)->cancel($this->order);

    expect($this->order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($this->variant->fresh()->stock)->toBe(5)
        ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('orders:expire mengecek pembayaran dulu: yang ternyata sudah dibayar tidak dibatalkan', function () {
    $this->komerce->pay($this->payment->provider_payment_id);
    $this->travel(25)->hours();

    $this->artisan('orders:expire')->assertSuccessful();

    expect($this->order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($this->variant->fresh()->stock)->toBe(3);
});

it('orders:expire membatalkan pesanan yang belum dibayar beserta VA-nya', function () {
    $this->travel(25)->hours();

    $this->artisan('orders:expire')->assertSuccessful();

    expect($this->order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($this->variant->fresh()->stock)->toBe(5)
        ->and($this->komerce->payments[$this->payment->provider_payment_id]['status'])->toBe('CANCELED');
});
