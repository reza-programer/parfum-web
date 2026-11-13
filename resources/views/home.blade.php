@php
    $grid = 'grid grid-cols-2 gap-x-3 gap-y-8 sm:gap-x-5 md:grid-cols-3 lg:grid-cols-4';
    $sectionHead = 'flex items-end justify-between gap-4';
    $more = 'inline-flex shrink-0 items-center gap-0.5 text-label font-bold uppercase tracking-nav text-ink hover:text-primary';
@endphp
{{-- Beranda gaya newbalance.co.id (docs/plans/redesign-nb.md). --}}
<x-app-layout>
    <x-shop.banner-carousel :banners="$banners" />

    {{-- Belanja berdasarkan aroma --}}
    <section class="page mt-14 lg:mt-20" aria-labelledby="aroma-title">
        <h2 id="aroma-title" class="heading-section text-center">Belanja berdasarkan aroma</h2>
        <div class="no-scrollbar -mx-4 mt-8 flex snap-x gap-3 overflow-x-auto px-4 sm:mx-0 sm:grid sm:grid-cols-3 sm:gap-5 sm:px-0 lg:grid-cols-5">
            @foreach ($scentTiles as $tile)
                <a href="{{ $tile['url'] }}" wire:navigate data-tile="{{ $tile['key'] }}" class="group block w-[64vw] shrink-0 snap-start sm:w-auto">
                    <span class="block aspect-square overflow-hidden bg-paper-raised">
                        <img src="{{ asset($tile['image']) }}" alt="" class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]" loading="lazy" decoding="async">
                    </span>
                    <span class="mt-3 block text-[15px] font-bold text-ink">{{ $tile['label'] }}</span>
                    <span class="block text-label text-ink-2">{{ $tile['text'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Ubin kategori besar --}}
    @if ($categoryTiles)
        <section class="page mt-14 grid gap-x-5 gap-y-10 sm:grid-cols-2 lg:mt-20" aria-label="Kategori">
            @foreach ($categoryTiles as $tile)
                <a href="{{ $tile['url'] }}" wire:navigate data-tile="{{ $tile['key'] }}" class="group block text-center">
                    <span class="block aspect-[16/10] overflow-hidden bg-paper-raised">
                        <img src="{{ asset($tile['image']) }}" alt="" class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]" loading="lazy" decoding="async">
                    </span>
                    <span class="mt-5 block text-[18px] font-bold uppercase tracking-heading text-ink">{{ $tile['label'] }}</span>
                    <span class="mt-1 block text-body uppercase tracking-label text-ink-2">{{ $tile['text'] }}</span>
                    <span class="mt-3 inline-flex items-center gap-0.5 text-label font-bold uppercase tracking-nav text-ink group-hover:text-primary">Belanja <x-lucide-chevron-right class="size-4" stroke-width="2" /></span>
                </a>
            @endforeach
        </section>
    @endif

    {{-- Baru masuk --}}
    <section class="page mt-16 lg:mt-24" aria-labelledby="newest-title">
        <div class="{{ $sectionHead }}">
            <h2 id="newest-title" class="heading-section">Baru masuk</h2>
            <a href="{{ route('catalog') }}" wire:navigate class="{{ $more }}">Lihat semua <x-lucide-chevron-right class="size-4" stroke-width="2" /></a>
        </div>
        <div class="{{ $grid }} mt-6">
            @foreach ($newest as $product)
                <x-shop.product-card :product="$product" :eager="$loop->index < 2" />
            @endforeach
        </div>
    </section>

    @if ($bestsellers->isNotEmpty())
        <section class="page mt-16 lg:mt-24" aria-labelledby="best-title">
            <div class="{{ $sectionHead }}">
                <h2 id="best-title" class="heading-section">Paling laris</h2>
                <a href="{{ route('catalog', ['urut' => 'terlaris']) }}" wire:navigate class="{{ $more }}">Lihat semua <x-lucide-chevron-right class="size-4" stroke-width="2" /></a>
            </div>
            <div class="{{ $grid }} mt-6">
                @foreach ($bestsellers as $product)
                    <x-shop.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif
</x-app-layout>
