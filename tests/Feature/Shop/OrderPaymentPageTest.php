<?php

use App\Enums\OrderStatus;
use App\Livewire\Orders\OrderShow;
use App\Models\Order;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\Support\FakeKomerce;

beforeEach(function () {
    Mail::fake();
    $this->komerce = FakeKomerce::register();
    $this->user = User::factory()->create();
    $this->order = Order::factory()->for($this->user)->create(['total' => 250000, 'expires_at' => now()->addDay()]);
});

function orderPage(Order $order)
{
    return Livewire::actingAs($order->user)->test(OrderShow::class, ['order' => $order]);
}

it('menampilkan nomor VA, nominal, batas bayar, dan polling status', function () {
    $payment = app(PaymentService::class)->create($this->order, 'va:BCA');

    orderPage($this->order)
        ->assertSee('Transfer ke virtual account BCA')
        ->assertSee($payment->va_number)
        ->assertSee('Rp250.000')
        ->assertSee($payment->expires_at->translatedFormat('j F Y, H:i'))
        ->assertSee('Ganti metode')
        ->assertSeeHtml('wire:poll.5s="checkPayment"');
});

it('menampilkan kode QR untuk QRIS', function () {
    app(PaymentService::class)->create($this->order, 'qris');

    orderPage($this->order)
        ->assertSee('Scan kode QR')
        ->assertSeeHtml('<svg')
        ->assertSeeHtml('data-qr');
});

it('tautan simulasi bayar hanya muncul di sandbox', function () {
    app(PaymentService::class)->create($this->order, 'va:BCA');

    orderPage($this->order)->assertSee('Simulasikan pembayaran (sandbox)');

    config(['services.komerce.production' => true]);
    orderPage($this->order)->assertDontSee('Simulasikan pembayaran');
});

it('polling mengubah tampilan jadi pembayaran diterima setelah Komerce PAID', function () {
    $payment = app(PaymentService::class)->create($this->order, 'va:BCA');
    $this->komerce->pay($payment->provider_payment_id);

    orderPage($this->order)
        ->call('checkPayment')
        ->assertSee('Pembayaran diterima')
        ->assertDontSeeHtml('wire:poll.5s="checkPayment"');

    expect($this->order->fresh()->status)->toBe(OrderStatus::Paid);
});

it('ganti metode membuat pembayaran baru dan membatalkan VA lama', function () {
    $old = app(PaymentService::class)->create($this->order, 'va:BCA');

    orderPage($this->order)
        ->call('changeMethod')
        ->assertSee('Pilih metode pembayaran')
        ->set('paymentMethod', 'va:MANDIRI')
        ->call('pay')
        ->assertHasNoErrors()
        ->assertSee('Transfer ke virtual account Mandiri');

    expect($this->order->fresh()->payments)->toHaveCount(2)
        ->and($this->komerce->payments[$old->provider_payment_id]['status'])->toBe('CANCELED');
});

it('QR yang kedaluwarsa bisa dibuat ulang', function () {
    app(PaymentService::class)->create($this->order, 'qris');
    $this->travel(6)->minutes();

    orderPage($this->order)
        ->assertSee('Kode QR sudah tidak berlaku')
        ->assertSet('paymentMethod', 'qris')
        ->call('pay')
        ->assertSee('Scan kode QR');

    expect($this->order->fresh()->payments)->toHaveCount(2);
});

it('pesanan yang pembayarannya gagal dibuat menampilkan pilihan metode', function () {
    orderPage($this->order)
        ->assertSee('Pilih metode pembayaran')
        ->assertSee('BCA')
        ->call('pay')
        ->assertHasErrors('paymentMethod')
        ->set('paymentMethod', 'va:BNI')
        ->call('pay')
        ->assertHasNoErrors()
        ->assertSee('Transfer ke virtual account BNI');
});

it('error dari Komerce saat membuat pembayaran ditampilkan', function () {
    $page = orderPage($this->order)->set('paymentMethod', 'va:BCA');
    $this->komerce->down = true;

    $page->call('pay')
        ->assertHasErrors('payment')
        ->assertSee('Pembayaran belum bisa dibuat. Coba lagi sebentar.');
});

it('pesanan yang sudah dibayar tidak menampilkan pilihan bayar maupun polling', function () {
    $paid = Order::factory()->for($this->user)->status(OrderStatus::Paid)->create();

    orderPage($paid)
        ->assertDontSee('Pilih metode pembayaran')
        ->assertDontSeeHtml('wire:poll');
});
