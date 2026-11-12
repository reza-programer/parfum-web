<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Sleep;
use Livewire\Livewire;
use Tests\Support\FakeKomerce;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('admin melihat daftar dan detail pesanan', function () {
    $orders = Order::factory()->count(3)->create();
    OrderItem::factory()->for($orders[0])->create(['product_name' => 'Hutan Pagi']);

    Livewire::test(ListOrders::class)->assertCanSeeTableRecords($orders);
    $this->get(OrderResource::getUrl('view', ['record' => $orders[0]]))->assertOk()->assertSee('Hutan Pagi');
});

it('tab status memfilter pesanan', function () {
    $pending = Order::factory()->create();
    $paid = Order::factory()->status(OrderStatus::Paid)->create();

    Livewire::test(ListOrders::class)
        ->set('activeTab', 'perlu_diproses')
        ->assertCanSeeTableRecords([$paid])
        ->assertCanNotSeeTableRecords([$pending]);
});

it('input resi mengubah status menjadi dikirim dan mengirim email', function () {
    Mail::fake();
    $order = Order::factory()->status(OrderStatus::Processing)->create();

    Livewire::test(ViewOrder::class, ['record' => $order->code])
        ->callAction('ship', data: ['tracking_number' => 'JNE999'])
        ->assertHasNoActionErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Shipped)
        ->and($order->fresh()->tracking_number)->toBe('JNE999');
    Mail::assertQueued(OrderShippedMail::class);
});

it('input resi wajib diisi', function () {
    $order = Order::factory()->status(OrderStatus::Paid)->create();

    Livewire::test(ViewOrder::class, ['record' => $order->code])
        ->callAction('ship', data: ['tracking_number' => ''])
        ->assertHasActionErrors(['tracking_number' => 'required']);

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
});

it('aksi tandai dikemas hanya untuk pesanan dibayar', function () {
    $paid = Order::factory()->status(OrderStatus::Paid)->create();
    $pending = Order::factory()->create();

    Livewire::test(ViewOrder::class, ['record' => $paid->code])->callAction('process');
    expect($paid->fresh()->status)->toBe(OrderStatus::Processing);

    Livewire::test(ViewOrder::class, ['record' => $pending->code])
        ->assertActionHidden('process')
        ->assertActionHidden('ship');
});

it('admin membatalkan pesanan dan stok kembali', function () {
    $variant = ProductVariant::factory()->create(['stock' => 0]);
    $order = Order::factory()->status(OrderStatus::Paid)->create();
    OrderItem::factory()->for($order)->create(['product_variant_id' => $variant->id, 'qty' => 2]);

    Livewire::test(ViewOrder::class, ['record' => $order->code])->callAction('cancel');

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($variant->fresh()->stock)->toBe(2);
});

it('tidak ada aksi admin untuk menandai dibayar', function () {
    $order = Order::factory()->create();

    Livewire::test(ViewOrder::class, ['record' => $order->code])
        ->assertActionDoesNotExist('markPaid');
});

it('aksi cek status bertanya ke Komerce dan menandai dibayar kalau sudah lunas', function () {
    Mail::fake();
    Sleep::fake();
    $komerce = FakeKomerce::register();
    $order = Order::factory()->create(['total' => 250000]);
    $payment = app(PaymentService::class)->create($order, 'va:BCA');
    $komerce->pay($payment->provider_payment_id);

    Livewire::test(ViewOrder::class, ['record' => $order->code])
        ->callAction('checkPayment')
        ->assertNotified('Pembayaran sudah masuk, pesanan ditandai dibayar');

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
});

it('aksi cek status memberi tahu kalau belum ada pembayaran masuk', function () {
    Sleep::fake();
    FakeKomerce::register();
    $order = Order::factory()->create(['total' => 250000]);
    app(PaymentService::class)->create($order, 'qris');

    Livewire::test(ViewOrder::class, ['record' => $order->code])
        ->callAction('checkPayment')
        ->assertNotified('Belum ada pembayaran masuk');

    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment);
});

it('aksi cek status hanya untuk pesanan menunggu bayar yang sudah punya pembayaran', function () {
    $withoutPayment = Order::factory()->create();
    $paid = Order::factory()->status(OrderStatus::Paid)->create();
    Payment::factory()->for($paid)->status(PaymentStatus::Paid)->create();

    Livewire::test(ViewOrder::class, ['record' => $withoutPayment->code])->assertActionHidden('checkPayment');
    Livewire::test(ViewOrder::class, ['record' => $paid->code])->assertActionHidden('checkPayment');
});

it('riwayat pembayaran tampil, termasuk penanda perlu refund', function () {
    $order = Order::factory()->status(OrderStatus::Cancelled)->create();
    Payment::factory()->for($order)->status(PaymentStatus::Paid)->create([
        'reference' => 'INV-20261112-ZZZZZ-1',
        'channel_code' => 'MANDIRI',
        'paid_at' => now(),
        'needs_refund' => true,
    ]);

    $this->get(OrderResource::getUrl('view', ['record' => $order]))
        ->assertOk()
        ->assertSee('INV-20261112-ZZZZZ-1')
        ->assertSee('VA Mandiri')
        ->assertSee('Perlu refund manual');
});
