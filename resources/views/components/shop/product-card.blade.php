@props(['product', 'wished' => null, 'eager' => false])
{{--
    Kartu produk gaya marketplace. Semua label dihitung dari data (docs/plans/redesign-marketplace.md):
    Terlaris (5 teratas yang terjual), Baru (30 hari), Ada decant, Stok terbatas (1-3), Habis.
    Rating dan jumlah terjual hanya tampil kalau ada.
--}}
@php
    $soldOut = $product->isSoldOut();
    $wished ??= \App\Support\WishlistIds::has($product->id);
    $bestseller = ! $soldOut && \App\Support\BestsellerIds::has($product->id);
    $new = ! $soldOut && ! $bestseller && $product->isNew();
    $rating = $product->ratingAverage();
    $sold = $product->soldCount();
    $soldText = $sold >= 1000 ? rtrim(rtrim(number_format($sold / 1000, 1, ',', ''), '0'), ',').'rb' : (string) $sold;
    $url = route('products.show', $product);
@endphp
<article {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-lg border border-line bg-paper-card transition-shadow duration-200 hover:shadow-hover']) }}>
    <div class="relative">
        <a href="{{ $url }}" wire:navigate class="block" tabindex="-1" aria-hidden="true">
            <x-shop.product-image :product="$product" :eager="$eager" class="aspect-square" />
        </a>
        @if ($soldOut)
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-paper-card/50">
                <x-ui.badge tone="dark" size="pill" data-label="habis">Habis</x-ui.badge>
            </div>
        @elseif ($bestseller)
            <x-ui.badge tone="primary" class="absolute left-2 top-2" data-label="terlaris">Terlaris</x-ui.badge>
        @elseif ($new)
            <x-ui.badge tone="success" class="absolute left-2 top-2" data-label="baru">Baru</x-ui.badge>
        @endif
        <livewire:product-actions :product="$product" :wished="$wished" :key="'pa-'.$product->id" />
    </div>

    <a href="{{ $url }}" wire:navigate class="flex flex-1 flex-col px-2.5 pb-3 pt-2 sm:px-3">
        <h3 class="line-clamp-2 min-h-10 text-product text-ink transition-colors duration-150 group-hover:text-primary">{{ $product->name }}</h3>

        <p @class(['price mt-1 text-[15px] font-bold leading-6', 'text-primary' => ! $soldOut, 'text-ink-2' => $soldOut])>
            @if ($product->hasPriceRange())<span class="mr-0.5 text-caption font-medium text-ink-2">mulai</span>@endif{{ rupiah($product->startingPrice()) }}
        </p>

        @if (! $soldOut && ($product->hasDecant() || $product->isLowStock()))
            <div class="mt-1 flex flex-wrap items-center gap-1">
                @if ($product->hasDecant())
                    <span class="inline-flex h-5 items-center rounded border border-primary/40 px-1.5 text-caption font-medium text-primary" data-label="decant">Ada decant</span>
                @endif
                @if ($product->isLowStock())
                    <span class="inline-flex h-5 items-center rounded bg-warning-soft px-1.5 text-caption font-medium text-warning" data-label="stok-terbatas">Stok terbatas</span>
                @endif
            </div>
        @endif

        @if ($rating !== null || $sold > 0)
            <p class="mt-1.5 flex items-center gap-1 text-label text-ink-2">
                @if ($rating !== null)
                    <x-lucide-star class="size-3.5 fill-amber-400 text-amber-400" stroke-width="1.5" aria-hidden="true" />
                    <span class="font-medium text-ink" data-rating="{{ $rating }}">{{ number_format($rating, 1, ',', '') }}</span>
                @endif
                @if ($rating !== null && $sold > 0)
                    <span aria-hidden="true">·</span>
                @endif
                @if ($sold > 0)
                    <span>{{ $soldText }} terjual</span>
                @endif
            </p>
        @endif

        <p class="mt-auto truncate pt-1.5 text-label text-ink-2">{{ $product->brand->name }}<span aria-hidden="true"> · </span>{{ $product->scent_family->label() }}</p>
    </a>
</article>
