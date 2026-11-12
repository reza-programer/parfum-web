<?php

namespace App\Queries;

use App\Enums\Concentration;
use App\Enums\OrderStatus;
use App\Enums\ScentFamily;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

/**
 * Query katalog: search, filter, dan urutan. Nilai filter yang tidak dikenal diabaikan.
 */
final class CatalogQuery
{
    public const SORTS = [
        'terbaru' => 'Terbaru',
        'termurah' => 'Harga terendah',
        'termahal' => 'Harga tertinggi',
        'terlaris' => 'Terlaris',
    ];

    /**
     * @param  list<string>  $brands  slug brand
     * @param  list<string>  $scents  nilai ScentFamily
     * @param  list<string>  $sizes  size_ml sebagai string, atau "decant"
     * @param  list<string>  $concentrations  nilai Concentration
     */
    public function __construct(
        public ?string $search = null,
        public ?string $category = null,
        public array $brands = [],
        public array $scents = [],
        public array $sizes = [],
        public ?int $minPrice = null,
        public ?int $maxPrice = null,
        public string $sort = 'terbaru',
        public array $concentrations = [],
        public bool $inStock = false,
    ) {}

    public function sorted(string $sort): self
    {
        $clone = clone $this;
        $clone->sort = array_key_exists($sort, self::SORTS) ? $sort : 'terbaru';

        return $clone;
    }

    public function builder(): Builder
    {
        $query = Product::query()
            // Varian ikut dimuat untuk pilihan ukuran "Tambah cepat" di kartu.
            ->with(['brand:id,name,slug', 'category:id,name,slug', 'variants:id,product_id,size_ml,is_decant,price,stock'])
            ->withMin('variants as min_price', 'price')
            ->withMax('variants as max_price', 'price')
            ->withSum('variants as total_stock', 'stock')
            // Rating dan terjual untuk kartu produk (hanya ditampilkan kalau ada datanya).
            ->withAvg(['reviews as rating_avg' => fn (Builder $q) => $q->where('is_visible', true)], 'rating')
            ->withCount(['reviews as rating_count' => fn (Builder $q) => $q->where('is_visible', true)])
            ->addSelect(['sold_qty' => self::soldQuantity()]);

        $this->applySearch($query);
        $this->applyCategory($query);
        $this->applyBrands($query);
        $this->applyScents($query);
        $this->applyConcentrations($query);
        $this->applySizes($query);
        $this->applyPrice($query);
        $this->applyStock($query);
        $this->applySort($query);

        return $query;
    }

    private function applyConcentrations(Builder $query): void
    {
        $values = array_values(array_filter(
            $this->concentrations,
            fn ($c) => is_string($c) && Concentration::tryFrom($c) !== null,
        ));

        if ($values) {
            $query->whereIn('concentration', $values);
        }
    }

    private function applyStock(Builder $query): void
    {
        if ($this->inStock) {
            $query->where('is_active', true)->whereHas('variants', fn (Builder $q) => $q->where('stock', '>', 0));
        }
    }

    private function applySearch(Builder $query): void
    {
        $term = trim((string) $this->search);

        if ($term === '') {
            return;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        $query->where(fn (Builder $q) => $q
            ->where('name', 'like', $like)
            ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'like', $like)));
    }

    private function applyCategory(Builder $query): void
    {
        if ($this->category) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $this->category));
        }
    }

    private function applyBrands(Builder $query): void
    {
        $brands = array_values(array_filter($this->brands, 'is_string'));

        if ($brands) {
            $query->whereHas('brand', fn (Builder $q) => $q->whereIn('slug', $brands));
        }
    }

    private function applyScents(Builder $query): void
    {
        $scents = array_values(array_filter(
            $this->scents,
            fn ($s) => is_string($s) && ScentFamily::tryFrom($s) !== null,
        ));

        if ($scents) {
            $query->whereIn('scent_family', $scents);
        }
    }

    private function applySizes(Builder $query): void
    {
        $decant = in_array('decant', $this->sizes, true);
        $sizes = array_values(array_unique(array_map('intval', array_filter(
            $this->sizes,
            fn ($s) => is_string($s) && ctype_digit($s) && (int) $s > 0,
        ))));

        if (! $decant && ! $sizes) {
            return;
        }

        $query->whereHas('variants', function (Builder $q) use ($decant, $sizes) {
            $q->where(function (Builder $q) use ($decant, $sizes) {
                if ($sizes) {
                    $q->orWhere(fn (Builder $q) => $q->whereIn('size_ml', $sizes)->where('is_decant', false));
                }
                if ($decant) {
                    $q->orWhere('is_decant', true);
                }
            });
        });
    }

    private function applyPrice(Builder $query): void
    {
        if ($this->minPrice === null && $this->maxPrice === null) {
            return;
        }

        $query->whereHas('variants', function (Builder $q) {
            if ($this->minPrice !== null) {
                $q->where('price', '>=', $this->minPrice);
            }
            if ($this->maxPrice !== null) {
                $q->where('price', '<=', $this->maxPrice);
            }
        });
    }

    private function applySort(Builder $query): void
    {
        match ($this->sort) {
            'termurah' => $query->orderBy('min_price')->orderBy('products.id'),
            'termahal' => $query->orderByDesc('min_price')->orderBy('products.id'),
            'terlaris' => $query->orderByDesc('sold_qty')->orderByDesc('products.created_at'),
            default => $query->orderByDesc('products.created_at')->orderByDesc('products.id'),
        };
    }

    /**
     * Subquery jumlah terjual per produk (pesanan yang sudah dibayar dan tidak batal).
     */
    public static function soldQuantity(): Builder
    {
        return OrderItem::query()
            ->selectRaw('coalesce(sum(order_items.qty), 0)')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->whereIn('orders.status', OrderStatus::soldValues());
    }
}
