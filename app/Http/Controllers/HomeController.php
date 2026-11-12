<?php

namespace App\Http\Controllers;

use App\Enums\ScentFamily;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Queries\CatalogQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Beranda gaya marketplace (docs/plans/redesign-marketplace.md). Teks banner dan shortcut memakai data asli:
 * banner yang datanya kosong dilewati, harga decant diambil dari varian yang masih ada stok.
 */
class HomeController extends Controller
{
    public function __invoke(): View
    {
        $active = fn () => (new CatalogQuery)->builder()->where('is_active', true);
        $bestsellers = (new CatalogQuery)->sorted('terlaris')->builder()->where('is_active', true)->limit(10)->get();

        return view('home', [
            'banners' => $this->banners(),
            'shortcuts' => $this->shortcuts(),
            'newest' => $active()->limit(10)->get(),
            'bestsellers' => $bestsellers->filter(fn (Product $p) => $p->soldCount() > 0)->values(),
            'brands' => $this->brands(),
        ]);
    }

    /**
     * @return list<array{key: string, eyebrow: string, title: string, text: string, cta: string, url: string, tone: string}>
     */
    private function banners(): array
    {
        $banners = [];

        foreach ([
            ScentFamily::Oriental->value => ['title' => 'Hangat dari rempah dan dupa', 'tone' => 'light'],
            ScentFamily::Fresh->value => ['title' => 'Segar dipakai seharian', 'tone' => 'light'],
        ] as $scent => $copy) {
            $names = Product::query()->where('is_active', true)->where('scent_family', $scent)
                ->latest()->orderByDesc('id')->pluck('name');

            if ($names->isEmpty()) {
                continue;
            }

            $banners[] = [
                'key' => $scent,
                'eyebrow' => 'Koleksi '.$scent,
                'title' => $copy['title'],
                'text' => $names->count().' parfum '.$scent.', termasuk '.$names->take(2)->join(' dan ').'.',
                'cta' => 'Lihat koleksi',
                'url' => route('catalog', ['aroma' => [$scent]]),
                'tone' => $copy['tone'],
            ];
        }

        $cheapestDecant = ProductVariant::query()
            ->where('is_decant', true)->where('stock', '>', 0)
            ->whereHas('product', fn (Builder $q) => $q->where('is_active', true))
            ->min('price');

        if ($cheapestDecant !== null) {
            $banners[] = [
                'key' => 'decant',
                'eyebrow' => 'Decant',
                'title' => 'Coba dulu lewat decant',
                'text' => 'Ukuran kecil untuk mengenal aromanya sebelum beli botol penuh, mulai '.rupiah((int) $cheapestDecant).'.',
                'cta' => 'Lihat decant',
                'url' => route('catalog', ['ukuran' => ['decant']]),
                'tone' => 'dark',
            ];
        }

        return $banners;
    }

    /**
     * Shortcut bulat: kategori, decant, lalu keluarga aroma. Fotonya diambil dari produk aktif di kelompok itu.
     *
     * @return list<array{key: string, label: string, url: string, image: ?string}>
     */
    private function shortcuts(): array
    {
        // Kolom images berupa JSON; disaring di PHP supaya tidak bergantung pada perbandingan JSON di database.
        // Produk yang sudah dipakai shortcut lain dilewati supaya fotonya beragam.
        $used = [];
        $cover = function (Builder $query) use (&$used): ?string {
            $candidates = $query->where('is_active', true)->orderBy('id')->get(['id', 'images'])
                ->filter(fn (Product $p) => $p->coverImage() !== null);
            $pick = $candidates->first(fn (Product $p) => ! in_array($p->id, $used, true)) ?? $candidates->first();

            if ($pick) {
                $used[] = $pick->id;
            }

            return $pick?->coverImage();
        };

        $items = Category::orderBy('id')->get()->map(fn (Category $category) => [
            'key' => $category->slug,
            'label' => $category->name,
            'url' => route('categories.show', $category),
            'image' => $cover(Product::query()->where('category_id', $category->id)),
        ])->all();

        $items[] = [
            'key' => 'decant',
            'label' => 'Decant',
            'url' => route('catalog', ['ukuran' => ['decant']]),
            'image' => null,
            'asset' => asset('images/categories/decant.webp'),
        ];

        foreach (ScentFamily::cases() as $scent) {
            $items[] = [
                'key' => $scent->value,
                'label' => $scent->label(),
                'url' => route('catalog', ['aroma' => [$scent->value]]),
                'image' => $cover(Product::query()->where('scent_family', $scent->value)),
                'scent' => $scent->value,
            ];
        }

        return $items;
    }

    /**
     * Brand yang punya produk aktif, dengan 3 foto produk sebagai pratinjau.
     *
     * @return Collection<int, Brand>
     */
    private function brands(): Collection
    {
        return Brand::query()
            ->withCount(['products' => fn (Builder $q) => $q->where('is_active', true)])
            ->having('products_count', '>', 0)
            ->orderByDesc('products_count')->orderBy('name')
            ->limit(4)
            ->get()
            ->each(fn (Brand $brand) => $brand->setRelation('preview', $brand->products()
                ->where('is_active', true)->orderBy('id')->limit(3)->get()));
    }
}
