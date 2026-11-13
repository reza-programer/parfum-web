<?php

use App\Livewire\Catalog;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Livewire;

/*
 | Katalog gaya marketplace (docs/plans/redesign-marketplace.md): pilihan harga cepat,
 | chip filter aktif yang bisa dihapus satu per satu, dan tab urutan.
 */

function priced(string $name, int $price, array $attrs = []): Product
{
    $product = Product::factory()->create(['name' => $name] + $attrs);
    ProductVariant::factory()->for($product)->create(['size_ml' => 50, 'price' => $price, 'stock' => 5]);

    return $product;
}

it('pilihan harga cepat mengisi batas harga dan menyaring produk', function () {
    priced('Murah Sekali', 80000);
    priced('Menengah', 180000);
    priced('Mahal Sekali', 400000);

    Livewire::test(Catalog::class)
        ->call('setPriceRange', 100000, 250000)
        ->assertSet('min', '100000')
        ->assertSet('max', '250000')
        ->assertSee('Menengah')
        ->assertDontSee('Murah Sekali')
        ->assertDontSee('Mahal Sekali')
        ->call('setPriceRange', 250000, null)
        ->assertSet('min', '250000')
        ->assertSet('max', '')
        ->assertSee('Mahal Sekali')
        ->assertDontSee('Menengah');
});

it('chip filter aktif tampil dengan nama yang mudah dibaca', function () {
    $brand = Brand::factory()->create(['name' => 'Rimba Atelier', 'slug' => 'rimba-atelier']);
    priced('Hutan Pagi', 300000, ['brand_id' => $brand->id, 'scent_family' => 'woody']);

    Livewire::test(Catalog::class)
        ->set('brands', ['rimba-atelier'])
        ->set('scents', ['woody'])
        ->set('sizes', ['decant'])
        ->set('min', '100000')
        ->set('tersedia', true)
        ->assertSeeHtml('data-chip="brand:rimba-atelier"')
        ->assertSeeHtml('data-chip="scent:woody"')
        ->assertSeeHtml('data-chip="size:decant"')
        ->assertSeeHtml('data-chip="price"')
        ->assertSeeHtml('data-chip="tersedia"')
        ->assertSee('Rimba Atelier')
        ->assertSee('Mulai Rp100.000');
});

it('chip menghapus filternya sendiri saja', function () {
    $component = Livewire::test(Catalog::class)
        ->set('q', 'melati')
        ->set('brands', ['a', 'b'])
        ->set('scents', ['woody', 'fresh'])
        ->set('sizes', ['50', 'decant'])
        ->set('concentrations', ['edp'])
        ->set('min', '100000')
        ->set('max', '300000')
        ->set('tersedia', true);

    $component->call('removeFilter', 'brand', 'a')->assertSet('brands', ['b'])
        ->call('removeFilter', 'scent', 'fresh')->assertSet('scents', ['woody'])
        ->call('removeFilter', 'size', 'decant')->assertSet('sizes', ['50'])
        ->call('removeFilter', 'concentration', 'edp')->assertSet('concentrations', [])
        ->call('removeFilter', 'price')->assertSet('min', '')->assertSet('max', '')
        ->call('removeFilter', 'tersedia')->assertSet('tersedia', false)
        ->call('removeFilter', 'q')->assertSet('q', '')
        ->call('removeFilter', 'tidak-dikenal', 'x')
        ->assertSet('brands', ['b'])
        ->assertSet('scents', ['woody']);
});

it('tab urutan mengganti urutan produk', function () {
    priced('Paling Murah', 50000);
    priced('Paling Mahal', 900000);

    Livewire::test(Catalog::class)
        ->assertSeeHtml('data-sort="termurah"')
        ->call('$set', 'sort', 'termahal')
        ->assertSeeInOrder(['Paling Mahal', 'Paling Murah'])
        ->call('$set', 'sort', 'termurah')
        ->assertSeeInOrder(['Paling Murah', 'Paling Mahal']);
});

it('ubin promo decant muncul di grid hanya kalau ada decant berstok dan filter decant belum dipakai', function () {
    foreach (range(1, 9) as $i) {
        priced("Botol {$i}", 300000);
    }
    $withDecant = Product::factory()->create(['name' => 'Ada Decant']);
    ProductVariant::factory()->for($withDecant)->create(['size_ml' => 5, 'is_decant' => true, 'price' => 49000, 'stock' => 2]);

    Livewire::test(Catalog::class)
        ->assertSeeHtml('data-promo="decant"')
        ->assertSee('mulai Rp49.000')
        ->set('sizes', ['decant'])
        ->assertDontSeeHtml('data-promo="decant"');
});

it('ubin promo decant tidak muncul kalau semua decant habis', function () {
    foreach (range(1, 9) as $i) {
        priced("Botol {$i}", 300000);
    }
    $empty = Product::factory()->create();
    ProductVariant::factory()->for($empty)->create(['size_ml' => 5, 'is_decant' => true, 'price' => 49000, 'stock' => 0]);

    Livewire::test(Catalog::class)->assertDontSeeHtml('data-promo="decant"');
});

it('urutan dipilih lewat dropdown', function () {
    Livewire::test(Catalog::class)
        ->assertSeeHtml('<select id="sort"')
        ->assertSeeHtml('value="termahal"');
});
