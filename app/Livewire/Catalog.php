<?php

namespace App\Livewire;

use App\Enums\Concentration;
use App\Enums\ScentFamily;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Queries\CatalogQuery;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Catalog extends Component
{
    use WithPagination;

    public const PER_PAGE = 24;

    /** Pilihan harga cepat di kotak filter (dalam Rupiah). */
    public const PRICE_RANGES = [
        ['label' => 'Di bawah Rp100rb', 'min' => null, 'max' => 100_000],
        ['label' => 'Rp100rb – Rp250rb', 'min' => 100_000, 'max' => 250_000],
        ['label' => 'Di atas Rp250rb', 'min' => 250_000, 'max' => null],
    ];

    /** Tab urutan di atas hasil. Kunci sama dengan CatalogQuery::SORTS. */
    public const SORT_TABS = [
        'terbaru' => 'Terbaru',
        'terlaris' => 'Terlaris',
        'termurah' => 'Harga terendah',
        'termahal' => 'Harga tertinggi',
    ];

    #[Url(except: '')]
    public string $q = '';

    #[Url(as: 'kategori', except: '')]
    public string $category = '';

    /** @var list<string> */
    #[Url(as: 'brand', except: [])]
    public array $brands = [];

    /** @var list<string> */
    #[Url(as: 'aroma', except: [])]
    public array $scents = [];

    /** @var list<string> */
    #[Url(as: 'ukuran', except: [])]
    public array $sizes = [];

    /** @var list<string> */
    #[Url(as: 'konsentrasi', except: [])]
    public array $concentrations = [];

    #[Url(except: false)]
    public bool $tersedia = false;

    #[Url(except: '')]
    public string $min = '';

    #[Url(except: '')]
    public string $max = '';

    #[Url(as: 'urut', except: 'terbaru')]
    public string $sort = 'terbaru';

    /** Kategori/brand dari URL rute (/kategori/{slug}, /brand/{slug}) tidak bisa diubah dari filter. */
    public ?int $lockedCategoryId = null;

    public ?int $lockedBrandId = null;

    public function mount(?Category $category = null, ?Brand $brand = null): void
    {
        if ($category?->exists) {
            $this->lockedCategoryId = $category->id;
            $this->category = '';
        }

        if ($brand?->exists) {
            $this->lockedBrandId = $brand->id;
            $this->brands = [];
        }
    }

    public function updating(string $property): void
    {
        if ($property !== 'paginators') {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('q', 'brands', 'scents', 'sizes', 'concentrations', 'tersedia', 'min', 'max', 'sort');

        if (! $this->lockedCategoryId) {
            $this->category = '';
        }

        $this->resetPage();
    }

    public function setPriceRange(?int $min = null, ?int $max = null): void
    {
        $this->min = $min === null ? '' : (string) max(0, $min);
        $this->max = $max === null ? '' : (string) max(0, $max);
        $this->resetPage();
    }

    /**
     * Hapus satu filter dari chip filter aktif. Tipe yang tidak dikenal diabaikan.
     */
    public function removeFilter(string $type, string $value = ''): void
    {
        $without = fn (array $values) => array_values(array_filter($values, fn ($v) => $v !== $value));

        match ($type) {
            'q' => $this->q = '',
            'category' => $this->category = '',
            'brand' => $this->brands = $without($this->brands),
            'scent' => $this->scents = $without($this->scents),
            'size' => $this->sizes = $without($this->sizes),
            'concentration' => $this->concentrations = $without($this->concentrations),
            'price' => [$this->min, $this->max] = ['', ''],
            'tersedia' => $this->tersedia = false,
            default => null,
        };

        $this->resetPage();
    }

    /**
     * Chip filter aktif dengan nama yang mudah dibaca.
     *
     * @return list<array{key: string, type: string, value: string, label: string}>
     */
    #[Computed]
    public function activeChips(): array
    {
        $chips = [];
        $add = function (string $type, string $value, string $label) use (&$chips) {
            $chips[] = ['key' => $value === '' ? $type : "{$type}:{$value}", 'type' => $type, 'value' => $value, 'label' => $label];
        };
        $strings = fn (array $values) => array_values(array_filter($values, 'is_string'));

        if ($this->q !== '') {
            $add('q', '', '“'.$this->q.'”');
        }

        if ($this->category !== '' && ! $this->lockedCategoryId) {
            $add('category', '', Category::where('slug', $this->category)->value('name') ?? $this->category);
        }

        $brandNames = Brand::whereIn('slug', $strings($this->brands))->pluck('name', 'slug');
        foreach ($strings($this->brands) as $slug) {
            $add('brand', $slug, $brandNames[$slug] ?? $slug);
        }

        foreach ($strings($this->scents) as $scent) {
            $add('scent', $scent, ScentFamily::tryFrom($scent)?->label() ?? $scent);
        }

        foreach ($strings($this->sizes) as $size) {
            $add('size', $size, $size === 'decant' ? 'Decant' : "{$size}ml");
        }

        foreach ($strings($this->concentrations) as $concentration) {
            $add('concentration', $concentration, Concentration::tryFrom($concentration)?->label() ?? $concentration);
        }

        $min = $this->priceValue($this->min);
        $max = $this->priceValue($this->max);
        if ($min !== null || $max !== null) {
            $add('price', '', match (true) {
                $min !== null && $max !== null => rupiah($min).' – '.rupiah($max),
                $min !== null => 'Mulai '.rupiah($min),
                default => 'Sampai '.rupiah($max),
            });
        }

        if ($this->tersedia) {
            $add('tersedia', '', 'Tersedia');
        }

        return $chips;
    }

    #[Computed]
    public function lockedCategory(): ?Category
    {
        return $this->lockedCategoryId ? Category::find($this->lockedCategoryId) : null;
    }

    #[Computed]
    public function lockedBrand(): ?Brand
    {
        return $this->lockedBrandId ? Brand::withCount('products')->find($this->lockedBrandId) : null;
    }

    #[Computed]
    public function query(): CatalogQuery
    {
        return (new CatalogQuery(
            search: $this->q,
            category: $this->lockedCategory?->slug ?? ($this->category ?: null),
            brands: $this->lockedBrand ? [$this->lockedBrand->slug] : $this->brands,
            scents: $this->scents,
            sizes: $this->sizes,
            minPrice: $this->priceValue($this->min),
            maxPrice: $this->priceValue($this->max),
            concentrations: $this->concentrations,
            inStock: $this->tersedia,
        ))->sorted($this->sort);
    }

    #[Computed]
    public function activeFilterCount(): int
    {
        return count($this->brands) + count($this->scents) + count($this->sizes) + count($this->concentrations)
            + ($this->category && ! $this->lockedCategoryId ? 1 : 0)
            + ($this->tersedia ? 1 : 0)
            + ($this->min !== '' ? 1 : 0) + ($this->max !== '' ? 1 : 0);
    }

    /**
     * Konsentrasi yang benar-benar dipakai produk aktif. Kosong → filter tidak ditampilkan.
     *
     * @return list<Concentration>
     */
    #[Computed]
    public function concentrationOptions(): array
    {
        $used = Product::query()->where('is_active', true)->whereNotNull('concentration')
            ->distinct()->pluck('concentration')
            ->map(fn ($c) => $c instanceof Concentration ? $c->value : $c)
            ->all();

        return array_values(array_filter(Concentration::cases(), fn (Concentration $c) => in_array($c->value, $used, true)));
    }

    public function render(): View
    {
        $title = $this->lockedBrand?->name
            ?? ($this->lockedCategory ? 'Parfum '.mb_strtolower($this->lockedCategory->name) : 'Semua parfum');

        return view('livewire.catalog', [
            'products' => $this->query->builder()->paginate(self::PER_PAGE),
            'categories' => Category::orderBy('id')->get(['id', 'name', 'slug']),
            'brandOptions' => Brand::orderBy('name')->get(['id', 'name', 'slug']),
            'scentOptions' => ScentFamily::cases(),
            'sizeOptions' => ProductVariant::where('is_decant', false)->distinct()->orderBy('size_ml')->pluck('size_ml'),
            'heading' => $this->q !== '' ? "Hasil untuk “{$this->q}”" : $title,
        ])->title($title);
    }

    private function priceValue(string $value): ?int
    {
        $digits = preg_replace('/\D/', '', $value);

        return $digits === '' ? null : min((int) $digits, 100_000_000);
    }
}
