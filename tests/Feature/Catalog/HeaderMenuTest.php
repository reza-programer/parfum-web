<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

/*
 | Header gaya newbalance.co.id (docs/plans/redesign-nb.md): mega menu per kategori berisi
 | link aroma, ukuran, dan brand yang tersaring ke kategori itu.
 */

beforeEach(function () {
    $this->pria = Category::factory()->create(['name' => 'Pria', 'slug' => 'pria']);
    $this->brand = Brand::factory()->create(['name' => 'Rimba Atelier', 'slug' => 'rimba-atelier']);
    $product = Product::factory()->create(['category_id' => $this->pria->id, 'brand_id' => $this->brand->id]);
    ProductVariant::factory()->for($product)->create(['size_ml' => 50, 'is_decant' => false]);
});

it('mega menu kategori berisi link aroma, ukuran, dan brand yang tersaring ke kategori itu', function () {
    $this->get('/')->assertOk()
        ->assertSeeHtml('data-mega="pria"')
        ->assertSee(route('categories.show', ['category' => 'pria', 'aroma' => ['woody']]), false)
        ->assertSee(route('categories.show', ['category' => 'pria', 'ukuran' => ['50']]), false)
        ->assertSee(route('categories.show', ['category' => 'pria', 'ukuran' => ['decant']]), false)
        ->assertSee(route('categories.show', ['category' => 'pria', 'brand' => ['rimba-atelier']]), false);
});

it('mega menu decant menyaring aroma di dalam decant', function () {
    $this->get('/')->assertOk()
        ->assertSeeHtml('data-mega="decant"')
        ->assertSee(route('catalog', ['ukuran' => ['decant'], 'aroma' => ['fresh']])); // & di-escape jadi &amp; oleh Blade
});

it('mega menu brand menampilkan semua brand dengan jumlah produk aktif', function () {
    $this->get('/')->assertOk()
        ->assertSeeHtml('data-mega="brand"')
        ->assertSee(route('brands.show', 'rimba-atelier'), false)
        ->assertSee('Rimba Atelier');
});

it('bilah pengumuman hanya berisi fakta layanan, tanpa promo yang tidak ada', function () {
    $this->get('/')->assertOk()
        ->assertSee('Bayar VA bank &amp; QRIS', false)
        ->assertDontSee('Gratis ongkir')
        ->assertDontSee('GRATIS PENGIRIMAN');
});
