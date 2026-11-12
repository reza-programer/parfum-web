<?php

use App\Livewire\ProductDetail;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
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
