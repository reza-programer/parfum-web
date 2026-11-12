<?php

use App\Enums\OrderStatus;
use App\Livewire\Catalog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Queries\CatalogQuery;
use App\Support\BestsellerIds;
use Livewire\Livewire;

/*
 | Label kartu produk (docs/plans/redesign-marketplace.md): semua dihitung dari database,
 | tidak ada label atau angka yang dikarang.
 */

/**
 * @param  list<array{0:int,1:int,2:int,3:bool}>  $variants  [ml, harga, stok, decant]
 */
function labeled(array $attrs = [], array $variants = [[50, 300000, 5, false]]): Product
{
    $product = Product::factory()->create($attrs);

    foreach ($variants as [$ml, $price, $stock, $decant]) {
        ProductVariant::factory()->for($product)->create(['size_ml' => $ml, 'price' => $price, 'stock' => $stock, 'is_decant' => $decant]);
    }

    return $product;
}

function sell(Product $product, int $qty, OrderStatus $status = OrderStatus::Paid): OrderItem
{
    $order = Order::factory()->create(['status' => $status]);

    return OrderItem::factory()->for($order)->create(['product_variant_id' => $product->variants()->first()->id, 'qty' => $qty]);
}

// Ulasan butuh order item sendiri (unik). Pesanannya dibiarkan menunggu bayar supaya tidak ikut terhitung terjual.
function reviewFor(Product $product, int $rating, bool $visible = true): Review
{
    return Review::factory()->for($product)->create([
        'order_item_id' => sell($product, 1, OrderStatus::PendingPayment)->id,
        'rating' => $rating,
        'is_visible' => $visible,
    ]);
}

function fromCatalog(Product $product): Product
{
    return (new CatalogQuery)->builder()->whereKey($product->id)->firstOrFail();
}

it('label Baru hanya untuk produk yang masuk 30 hari terakhir', function () {
    $new = labeled(['created_at' => now()->subDays(29)]);
    $old = labeled(['created_at' => now()->subDays(31)]);

    expect($new->isNew())->toBeTrue()
        ->and($old->isNew())->toBeFalse();
});

it('jumlah terjual hanya menghitung pesanan yang sudah dibayar', function () {
    $product = labeled();
    sell($product, 3);
    sell($product, 2, OrderStatus::Completed);
    sell($product, 5, OrderStatus::PendingPayment);
    sell($product, 4, OrderStatus::Cancelled);

    expect(fromCatalog($product)->soldCount())->toBe(5)
        ->and($product->fresh()->soldCount())->toBe(5);
});

it('rating dihitung dari ulasan yang tampil saja', function () {
    $product = labeled();
    reviewFor($product, 5);
    reviewFor($product, 4);
    reviewFor($product, 1, visible: false);

    $card = fromCatalog($product);

    expect($card->ratingAverage())->toBe(4.5)
        ->and($card->ratingCount())->toBe(2)
        ->and($product->fresh()->ratingAverage())->toBe(4.5)
        ->and(fromCatalog(labeled())->ratingAverage())->toBeNull();
});

it('Terlaris hanya untuk 5 produk teratas yang benar-benar terjual', function () {
    $sold = collect(range(1, 6))->map(function (int $qty) {
        $product = labeled();
        sell($product, $qty);

        return $product;
    });
    $unsold = labeled();

    expect(BestsellerIds::current())->toHaveCount(5)
        ->and(BestsellerIds::has($sold[0]->id))->toBeFalse() // terjual 1: peringkat 6
        ->and(BestsellerIds::has($sold[5]->id))->toBeTrue()
        ->and(BestsellerIds::has($unsold->id))->toBeFalse();
});

it('stok terbatas untuk total stok 1 sampai 3, dan ada decant dari varian decant', function () {
    $low = labeled([], [[50, 300000, 2, false], [5, 49000, 1, true]]);
    $plenty = labeled([], [[50, 300000, 4, false]]);
    $empty = labeled([], [[50, 300000, 0, false]]);

    expect(fromCatalog($low)->isLowStock())->toBeTrue()
        ->and(fromCatalog($plenty)->isLowStock())->toBeFalse()
        ->and(fromCatalog($empty)->isLowStock())->toBeFalse()
        ->and(fromCatalog($low)->hasDecant())->toBeTrue()
        ->and(fromCatalog($plenty)->hasDecant())->toBeFalse();
});

it('kartu katalog menampilkan label, rating, dan terjual dari data', function () {
    $product = labeled(['name' => 'Laris Manis', 'created_at' => now()->subDays(60)], [[50, 300000, 2, false], [5, 49000, 8, true]]);
    sell($product, 12);
    reviewFor($product, 5);
    reviewFor($product, 4);

    Livewire::test(Catalog::class)
        ->assertSeeHtml('data-label="terlaris"')
        ->assertSeeHtml('data-label="decant"')
        ->assertDontSeeHtml('data-label="baru"')
        ->assertDontSeeHtml('data-label="stok-terbatas"') // total stok 10
        ->assertSee('4,5')
        ->assertSee('12 terjual');
});

it('kartu tanpa ulasan dan penjualan tidak menampilkan rating atau terjual', function () {
    labeled(['name' => 'Baru Datang'], [[50, 300000, 3, false]]);

    Livewire::test(Catalog::class)
        ->assertSee('Baru Datang')
        ->assertSeeHtml('data-label="baru"')
        ->assertSeeHtml('data-label="stok-terbatas"')
        ->assertDontSeeHtml('data-label="terlaris"')
        ->assertDontSeeHtml('data-rating')
        ->assertDontSee('terjual');
});
