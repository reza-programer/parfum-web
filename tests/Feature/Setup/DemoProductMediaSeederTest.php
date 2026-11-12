<?php

use App\Enums\Concentration;
use App\Models\Product;
use Database\Seeders\CatalogDemoSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoProductMediaSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->seed([CategorySeeder::class, CatalogDemoSeeder::class]);
});

it('memasang 3 render dan konsentrasi ke setiap produk katalog contoh', function () {
    $this->seed(DemoProductMediaSeeder::class);

    $products = Product::all();
    expect($products)->toHaveCount(12);

    $products->each(function (Product $product) {
        expect($product->images)->toHaveCount(3)
            ->and($product->images[0])->toBe("products/demo/{$product->slug}-1.webp")
            ->and($product->concentration)->toBeInstanceOf(Concentration::class);

        foreach ($product->images as $path) {
            Storage::disk('public')->assertExists($path);
        }
    });

    expect(Product::firstWhere('slug', 'malam-rempah')->concentration)->toBe(Concentration::Extrait)
        ->and(Product::firstWhere('slug', 'laut-biru')->concentration)->toBe(Concentration::Edt);
});

it('tidak menimpa foto dan konsentrasi yang sudah diisi admin', function () {
    $product = Product::firstWhere('slug', 'hutan-pagi');
    $product->update(['images' => ['products/foto-asli.webp'], 'concentration' => 'edt']);

    $this->seed(DemoProductMediaSeeder::class);

    expect($product->fresh()->images)->toBe(['products/foto-asli.webp'])
        ->and($product->fresh()->concentration)->toBe(Concentration::Edt);
});

it('tidak mengubah harga maupun stok', function () {
    $before = Product::with('variants')->get()->flatMap->variants->map->only(['id', 'price', 'stock'])->all();

    $this->seed(DemoProductMediaSeeder::class);

    $after = Product::with('variants')->get()->flatMap->variants->map->only(['id', 'price', 'stock'])->all();
    expect($after)->toBe($before);
});

it('seeder utama ikut memasang render demo', function () {
    $this->seed();

    expect(Product::whereJsonLength('images', 3)->count())->toBe(12);
});
