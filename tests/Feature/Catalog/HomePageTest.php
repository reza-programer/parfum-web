<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

/*
 | Beranda gaya marketplace (docs/plans/redesign-marketplace.md): teks banner memakai data asli.
 */

function homeProduct(array $attrs = [], array $variants = [[50, 300000, 5, false]]): Product
{
    $product = Product::factory()->create($attrs);

    foreach ($variants as [$ml, $price, $stock, $decant]) {
        ProductVariant::factory()->for($product)->create(['size_ml' => $ml, 'price' => $price, 'stock' => $stock, 'is_decant' => $decant]);
    }

    return $product;
}

it('banner decant memakai harga decant termurah yang masih ada stok', function () {
    homeProduct([], [[5, 59000, 3, true]]);
    homeProduct([], [[5, 39000, 0, true]]); // habis: tidak dihitung
    homeProduct(['is_active' => false], [[5, 29000, 5, true]]); // nonaktif: tidak dihitung
    homeProduct([], [[10, 49000, 2, true], [50, 300000, 2, false]]);

    $this->get('/')->assertOk()
        ->assertSee('Coba dulu lewat decant')
        ->assertSee('mulai Rp49.000')
        ->assertSee(route('catalog', ['ukuran' => ['decant']]), false);
});

it('banner oriental dan fresh menghitung produk aktif dan menautkan ke filter aroma', function () {
    homeProduct(['scent_family' => 'oriental', 'name' => 'Malam Rempah']);
    homeProduct(['scent_family' => 'oriental', 'name' => 'Dupa Keraton']);
    homeProduct(['scent_family' => 'oriental', 'is_active' => false]);
    homeProduct(['scent_family' => 'fresh', 'name' => 'Laut Biru']);

    $this->get('/')->assertOk()
        ->assertSee('Koleksi oriental')
        ->assertSee('2 parfum oriental')
        ->assertSee('1 parfum fresh')
        ->assertSee(route('catalog', ['aroma' => ['oriental']]), false)
        ->assertSee(route('catalog', ['aroma' => ['fresh']]), false);
});

it('banner dilewati kalau datanya tidak ada', function () {
    homeProduct(['scent_family' => 'woody']);

    $this->get('/')->assertOk()
        ->assertDontSee('Koleksi oriental')
        ->assertDontSee('Koleksi fresh')
        ->assertDontSee('Coba dulu lewat decant');
});

it('beranda menampilkan ubin aroma, ubin kategori dengan jumlah produk, dan produk baru', function () {
    $pria = Category::factory()->create(['name' => 'Pria', 'slug' => 'pria']);
    Category::factory()->create(['name' => 'Wanita', 'slug' => 'wanita']); // belum ada produk
    homeProduct(['name' => 'Hutan Pagi', 'category_id' => $pria->id, 'scent_family' => 'woody']);
    homeProduct(['name' => 'Laut Biru', 'category_id' => $pria->id, 'scent_family' => 'fresh'], [[5, 59000, 2, true]]);

    $this->get('/')->assertOk()
        ->assertSee('Belanja berdasarkan aroma')
        ->assertSeeHtml('data-tile="aroma-woody"')
        ->assertSeeHtml('data-tile="aroma-gourmand"')
        ->assertSeeHtml('data-tile="kategori-pria"')
        ->assertSee('2 parfum')
        ->assertDontSeeHtml('data-tile="kategori-wanita"') // kategori tanpa produk aktif tidak ditampilkan
        ->assertSeeHtml('data-tile="kategori-decant"')
        ->assertSee('Baru masuk')
        ->assertSee('Hutan Pagi');
});

it('ubin decant tidak muncul kalau tidak ada decant yang berstok', function () {
    homeProduct([], [[5, 59000, 0, true], [50, 300000, 3, false]]);

    $this->get('/')->assertOk()->assertDontSeeHtml('data-tile="kategori-decant"');
});

it('bagian terlaris tidak muncul sebelum ada penjualan', function () {
    homeProduct(['name' => 'Belum Laku']);

    $this->get('/')->assertOk()->assertDontSee('Paling laris');
});
