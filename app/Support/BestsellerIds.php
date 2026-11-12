<?php

namespace App\Support;

use App\Models\Product;
use App\Queries\CatalogQuery;

/**
 * ID produk berlabel "Terlaris": 5 produk aktif dengan penjualan terbanyak (minimal 1 terjual).
 * Dihitung sekali per request (atribut request) supaya kartu produk tidak masing-masing menjalankan query.
 */
class BestsellerIds
{
    public const LIMIT = 5;

    private const KEY = 'bestseller_product_ids';

    /**
     * @return list<int>
     */
    public static function current(): array
    {
        $attributes = request()->attributes;

        if (! $attributes->has(self::KEY)) {
            $ids = Product::query()
                ->where('is_active', true)
                ->select('products.id')
                ->addSelect(['sold_qty' => CatalogQuery::soldQuantity()])
                ->having('sold_qty', '>', 0)
                ->orderByDesc('sold_qty')
                ->orderBy('products.id')
                ->limit(self::LIMIT)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $attributes->set(self::KEY, $ids);
        }

        return $attributes->get(self::KEY);
    }

    public static function has(int $productId): bool
    {
        return in_array($productId, self::current(), true);
    }
}
