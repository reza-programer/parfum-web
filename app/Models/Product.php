<?php

namespace App\Models;

use App\Enums\Concentration;
use App\Enums\OrderStatus;
use App\Enums\ScentFamily;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /** Label "Baru" di kartu: masuk katalog dalam 30 hari terakhir. */
    public const NEW_DAYS = 30;

    /** Label "Stok terbatas": total stok semua ukuran 1 sampai 3. */
    public const LOW_STOCK = 3;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'description', 'scent_family', 'concentration',
        'top_notes', 'middle_notes', 'base_notes', 'images', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'scent_family' => ScentFamily::class,
            'concentration' => Concentration::class,
            'images' => 'array',
            'is_active' => 'boolean',
            'min_price' => 'integer',
            'max_price' => 'integer',
            'total_stock' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('size_ml');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function visibleReviews(): HasMany
    {
        return $this->reviews()->where('is_visible', true)->latest();
    }

    /**
     * Habis jika produk nonaktif atau total stok semua varian 0.
     */
    public function isSoldOut(): bool
    {
        if (! $this->is_active) {
            return true;
        }

        $stock = array_key_exists('total_stock', $this->attributes)
            ? (int) $this->attributes['total_stock']
            : (int) $this->variants()->sum('stock');

        return $stock <= 0;
    }

    public function startingPrice(): int
    {
        return array_key_exists('min_price', $this->attributes)
            ? (int) $this->attributes['min_price']
            : (int) $this->variants()->min('price');
    }

    /**
     * Ada beberapa harga (ukuran berbeda) → kartu menampilkan "mulai".
     */
    public function hasPriceRange(): bool
    {
        if (array_key_exists('max_price', $this->attributes) && array_key_exists('min_price', $this->attributes)) {
            return (int) $this->attributes['max_price'] > (int) $this->attributes['min_price'];
        }

        return $this->variants->pluck('price')->unique()->count() > 1;
    }

    public function isNew(): bool
    {
        return $this->created_at !== null && $this->created_at->gte(now()->subDays(self::NEW_DAYS));
    }

    public function totalStock(): int
    {
        return array_key_exists('total_stock', $this->attributes)
            ? (int) $this->attributes['total_stock']
            : (int) $this->variants->sum('stock');
    }

    /**
     * Stok tinggal sedikit (1 sampai 3 di semua ukuran). Produk habis tidak termasuk.
     */
    public function isLowStock(): bool
    {
        $stock = $this->totalStock();

        return $this->is_active && $stock > 0 && $stock <= self::LOW_STOCK;
    }

    /**
     * Jumlah terjual dari pesanan yang sudah dibayar. Memakai sold_qty dari CatalogQuery kalau ada.
     */
    public function soldCount(): int
    {
        if (array_key_exists('sold_qty', $this->attributes)) {
            return (int) $this->attributes['sold_qty'];
        }

        return (int) OrderItem::query()
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('product_variants.product_id', $this->id)
            ->whereIn('orders.status', OrderStatus::soldValues())
            ->sum('order_items.qty');
    }

    /**
     * Rata-rata rating ulasan yang tampil, dibulatkan 1 desimal. Null kalau belum ada ulasan.
     */
    public function ratingAverage(): ?float
    {
        $avg = array_key_exists('rating_avg', $this->attributes)
            ? $this->attributes['rating_avg']
            : $this->visibleReviews()->avg('rating');

        return $avg === null ? null : round((float) $avg, 1);
    }

    public function ratingCount(): int
    {
        return array_key_exists('rating_count', $this->attributes)
            ? (int) $this->attributes['rating_count']
            : $this->visibleReviews()->count();
    }

    public function coverImage(): ?string
    {
        return $this->images[0] ?? null;
    }

    /**
     * @return list<string>
     */
    public function notesList(string $layer): array
    {
        return collect(explode(',', (string) $this->{$layer.'_notes'}))
            ->map(fn (string $n) => trim($n))
            ->filter()
            ->values()
            ->all();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
