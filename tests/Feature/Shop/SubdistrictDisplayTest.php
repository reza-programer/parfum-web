<?php

use App\Filament\Resources\OrderResource;
use App\Livewire\Checkout;
use App\Livewire\Orders\OrderShow;
use App\Mail\OrderPlacedMail;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\FakeRajaOngkir;
use Livewire\Livewire;
use Tests\Support\FakeKomerce;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->snapshot = [
        'recipient' => 'Siti', 'phone' => '081311112222', 'detail' => 'Jl. Raya Darma No. 5',
        'subdistrict_id' => 16472, 'subdistrict_name' => 'SAKERTA TIMUR',
        'district_name' => 'DARMA', 'city_name' => 'KUNINGAN', 'province_name' => 'JAWA BARAT', 'postal_code' => '45562',
    ];
    $this->order = Order::factory()->for($this->user)->create(['address_snapshot' => $this->snapshot]);
    OrderItem::factory()->for($this->order)->create();
});

it('detail pesanan menampilkan kelurahan dari snapshot', function () {
    Livewire::actingAs($this->user)->test(OrderShow::class, ['order' => $this->order])
        ->assertSee('Jl. Raya Darma No. 5, Kel. Sakerta Timur, Darma, Kuningan, Jawa Barat 45562');
});

it('detail pesanan di admin menampilkan kelurahan', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(OrderResource::getUrl('view', ['record' => $this->order]))
        ->assertOk()
        ->assertSee('Kel. Sakerta Timur, Darma, Kuningan, Jawa Barat 45562');
});

it('email invoice menampilkan kelurahan', function () {
    $html = (new OrderPlacedMail($this->order->load('items')))->render();

    expect($html)->toContain('Kel. Sakerta Timur, Darma, Kuningan, Jawa Barat 45562');
});

it('kartu alamat di checkout menampilkan kelurahan', function () {
    config(['services.rajaongkir.base_url' => 'https://rajaongkir.test/api/v1']);
    FakeRajaOngkir::register('https://rajaongkir.test/api/v1');
    FakeKomerce::register();
    Address::factory()->for($this->user)->create(['is_default' => true]);
    CartItem::factory()->for($this->user)->create(['product_variant_id' => ProductVariant::factory()->create(['stock' => 3])->id]);

    Livewire::actingAs($this->user)->test(Checkout::class)->assertSee('Kel. Dago');
});
