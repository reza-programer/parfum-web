<?php

namespace App\Http\Controllers;

use App\Enums\ScentFamily;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Queries\CatalogQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

/**
 * Beranda gaya newbalance.co.id (docs/plans/redesign-nb.md): banner selebar layar, ubin aroma, ubin kategori,
 * lalu produk. Semua teks memakai data asli: banner dan ubin yang datanya kosong dilewati,
 * harga decant diambil dari varian yang masih ada stok.
 */
class HomeController extends Controller
{
    /** Gambaran singkat tiap keluarga aroma untuk ubin di beranda. */
    public const SCENT_COPY = [
        'woody' => 'Kayu, akar wangi, cedar',
        'floral' => 'Melati, mawar, kenanga',
        'fresh' => 'Citrus, teh, udara laut',
        'oriental' => 'Rempah, amber, oud',
        'gourmand' => 'Vanila, kopi, karamel',
    ];

    public function __invoke(): View
    {
        $cheapestDecant = ProductVariant::query()
            ->where('is_decant', true)->where('stock', '>', 0)
            ->whereHas('product', fn (Builder $q) => $q->where('is_active', true))
            ->min('price');
        $cheapestDecant = $cheapestDecant === null ? null : (int) $cheapestDecant;

        $bestsellers = (new CatalogQuery)->sorted('terlaris')->builder()->where('is_active', true)->limit(4)->get();

        return view('home', [
            'banners' => $this->banners($cheapestDecant),
            'scentTiles' => $this->scentTiles(),
            'categoryTiles' => $this->categoryTiles($cheapestDecant),
            'newest' => (new CatalogQuery)->builder()->where('is_active', true)->limit(8)->get(),
            'bestsellers' => $bestsellers->filter(fn (Product $p) => $p->soldCount() > 0)->values(),
        ]);
    }

    /**
     * @return list<array{key: string, eyebrow: string, title: string, text: string, cta: string, url: string}>
     */
    private function banners(?int $cheapestDecant): array
    {
        $banners = [];

        foreach ([
            ScentFamily::Oriental->value => 'Hangat dari rempah dan dupa',
            ScentFamily::Fresh->value => 'Segar dipakai seharian',
        ] as $scent => $title) {
            $names = Product::query()->where('is_active', true)->where('scent_family', $scent)
                ->latest()->orderByDesc('id')->pluck('name');

            if ($names->isEmpty()) {
                continue;
            }

            $banners[] = [
                'key' => $scent,
                'eyebrow' => 'Koleksi '.$scent,
                'title' => $title,
                'text' => $names->count().' parfum '.$scent.', termasuk '.$names->take(2)->join(' dan ').'.',
                'cta' => 'Belanja sekarang',
                'url' => route('catalog', ['aroma' => [$scent]]),
            ];
        }

        if ($cheapestDecant !== null) {
            $banners[] = [
                'key' => 'decant',
                'eyebrow' => 'Decant',
                'title' => 'Coba dulu lewat decant',
                'text' => 'Ukuran kecil untuk mengenal aromanya sebelum beli botol penuh, mulai '.rupiah($cheapestDecant).'.',
                'cta' => 'Belanja sekarang',
                'url' => route('catalog', ['ukuran' => ['decant']]),
            ];
        }

        return $banners;
    }

    /**
     * @return list<array{key: string, label: string, text: string, url: string, image: string}>
     */
    private function scentTiles(): array
    {
        return array_map(fn (ScentFamily $scent) => [
            'key' => 'aroma-'.$scent->value,
            'label' => $scent->label(),
            'text' => self::SCENT_COPY[$scent->value] ?? '',
            'url' => route('catalog', ['aroma' => [$scent->value]]),
            'image' => 'images/tiles/aroma-'.$scent->value.'.webp',
        ], ScentFamily::cases());
    }

    /**
     * Ubin kategori besar: kategori yang punya produk aktif, ditambah Decant kalau ada decant berstok.
     *
     * @return list<array{key: string, label: string, text: string, url: string, image: string}>
     */
    private function categoryTiles(?int $cheapestDecant): array
    {
        $tiles = Category::query()
            ->withCount(['products' => fn (Builder $q) => $q->where('is_active', true)])
            ->orderBy('id')
            ->get()
            ->filter(fn (Category $category) => $category->products_count > 0)
            ->map(fn (Category $category) => [
                'key' => 'kategori-'.$category->slug,
                'label' => $category->name,
                'text' => $category->products_count.' parfum',
                'url' => route('categories.show', $category),
                'image' => 'images/tiles/kategori-'.$category->slug.'.webp',
            ])
            ->values()
            ->all();

        if ($cheapestDecant !== null) {
            $tiles[] = [
                'key' => 'kategori-decant',
                'label' => 'Decant',
                'text' => 'Mulai '.rupiah($cheapestDecant),
                'url' => route('catalog', ['ukuran' => ['decant']]),
                'image' => 'images/tiles/kategori-decant.webp',
            ];
        }

        return $tiles;
    }
}
