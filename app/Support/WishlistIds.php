<?php

namespace App\Support;

/**
 * ID produk di wishlist user yang sedang login, dihitung sekali per request (disimpan di atribut
 * request, jadi tidak basi antar-request) supaya kartu produk tidak masing-masing menjalankan query.
 */
class WishlistIds
{
    private const KEY = 'wishlist_product_ids';

    /**
     * @return list<int>
     */
    public static function current(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $attributes = request()->attributes;

        if (! $attributes->has(self::KEY)) {
            $attributes->set(self::KEY, $user->wishlistItems()->pluck('product_id')->map(fn ($id) => (int) $id)->all());
        }

        return $attributes->get(self::KEY);
    }

    public static function has(int $productId): bool
    {
        return in_array($productId, self::current(), true);
    }
}
