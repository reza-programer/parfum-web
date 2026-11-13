<?php

use App\Enums\OrderStatus;
use App\Livewire\ProductDetail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Livewire\Livewire;

function detailProduct(array $attrs = [], int $stock = 5): Product
{
    $product = Product::factory()->create($attrs);
    ProductVariant::factory()->for($product)->create(['size_ml' => 50, 'price' => 300000, 'stock' => $stock]);

    return $product;
}

it('Beli Sekarang memasukkan ke keranjang lalu membuka checkout', function () {
    $user = User::factory()->create();
    $product = detailProduct();

    Livewire::actingAs($user)->test(ProductDetail::class, ['product' => $product])
        ->call('increment')
        ->call('buyNow')
        ->assertRedirect(route('checkout'));

    expect((int) $user->cartItems()->sum('qty'))->toBe(2);
});

it('tamu yang menekan Beli Sekarang diarahkan ke login', function () {
    Livewire::test(ProductDetail::class, ['product' => detailProduct()])
        ->call('buyNow')
        ->assertRedirect(route('login'));
});

it('Beli Sekarang yang gagal tetap di halaman dan menampilkan pesan', function () {
    $user = User::factory()->create();
    $product = detailProduct(['is_active' => false]);

    Livewire::actingAs($user)->test(ProductDetail::class, ['product' => $product])
        ->call('buyNow')
        ->assertHasErrors('qty')
        ->assertNoRedirect();
});

it('tambah ke keranjang memunculkan toast', function () {
    $user = User::factory()->create();
    $product = detailProduct(['name' => 'Hutan Pagi']);

    Livewire::actingAs($user)->test(ProductDetail::class, ['product' => $product])
        ->call('addToCart')
        ->assertDispatched('toast', message: 'Hutan Pagi 50ml masuk keranjang');
});

it('menampilkan konsentrasi kalau sudah diisi', function () {
    Livewire::test(ProductDetail::class, ['product' => detailProduct(['concentration' => 'edt'])])
        ->assertSee('Eau de Toilette');
});

it('galeri dengan thumbnail dan zoom hanya muncul kalau ada foto', function () {
    $withPhotos = detailProduct(['images' => ['products/a.webp', 'products/b.webp']]);
    $illustration = detailProduct(['images' => []]);

    Livewire::test(ProductDetail::class, ['product' => $withPhotos])
        ->assertSeeHtml('data-zoom')
        ->assertSeeHtml('aria-label="Foto 2"');

    Livewire::test(ProductDetail::class, ['product' => $illustration])
        ->assertDontSeeHtml('data-zoom')
        ->assertDontSeeHtml('aria-label="Foto 2"');
});

it('produk terkait diambil dari aroma atau kategori yang sama', function () {
    $pria = Category::factory()->create();
    $wanita = Category::factory()->create();
    $product = detailProduct(['name' => 'Utama', 'scent_family' => 'woody', 'category_id' => $pria->id]);
    detailProduct(['name' => 'Sama Aroma', 'scent_family' => 'woody', 'category_id' => $wanita->id]);
    detailProduct(['name' => 'Sama Kategori', 'scent_family' => 'floral', 'category_id' => $pria->id]);
    detailProduct(['name' => 'Tidak Terkait', 'scent_family' => 'floral', 'category_id' => $wanita->id]);

    Livewire::test(ProductDetail::class, ['product' => $product])
        ->assertSee('Produk terkait')
        ->assertSeeInOrder(['Sama Aroma', 'Sama Kategori'])
        ->assertDontSee('Tidak Terkait');
});

it('detail menampilkan rating dan jumlah terjual kalau ada datanya', function () {
    $product = detailProduct();
    $variantId = $product->variants()->first()->id;
    $paid = Order::factory()->create(['status' => OrderStatus::Paid]);
    OrderItem::factory()->for($paid)->create(['product_variant_id' => $variantId, 'qty' => 3]);
    $pendingItem = OrderItem::factory()->for(Order::factory()->create())->create(['product_variant_id' => $variantId]);
    Review::factory()->for($product)->create(['order_item_id' => $pendingItem->id, 'rating' => 4]);

    Livewire::test(ProductDetail::class, ['product' => $product])
        ->assertSeeHtml('data-rating="4"')
        ->assertSee('3 terjual');
});

it('detail tanpa ulasan dan penjualan tidak menampilkan rating atau terjual', function () {
    Livewire::test(ProductDetail::class, ['product' => detailProduct()])
        ->assertDontSeeHtml('data-rating')
        ->assertDontSee('terjual');
});

it('detail menandai stok terbatas kalau sisa 1 sampai 3', function () {
    Livewire::test(ProductDetail::class, ['product' => detailProduct([], 2)])->assertSee('Stok terbatas');
    Livewire::test(ProductDetail::class, ['product' => detailProduct([], 9)])->assertDontSee('Stok terbatas');
});
