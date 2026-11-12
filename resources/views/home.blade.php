@php
    $scentCopy = [
        'woody' => 'Kayu, akar wangi, cedar',
        'floral' => 'Melati, mawar, kenanga',
        'fresh' => 'Citrus, teh, udara laut',
        'oriental' => 'Rempah, amber, oud',
        'gourmand' => 'Vanila, kopi, karamel',
    ];
@endphp
<x-app-layout>
    {{-- Hero --}}
    <section class="page pt-10 sm:pt-16 lg:pt-20">
        <div class="grid items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-20">
            <div class="max-w-xl">
                <p class="eyebrow">Parfum original &amp; decant</p>
                <h1 class="mt-5 font-serif text-hero font-medium tracking-[-0.01em] text-ink">Cari parfum dari aromanya, bukan dari tebakan</h1>
                <p class="mt-6 max-w-md text-[15px] leading-7 text-ink-2">Pilih keluarga aroma, lihat top, middle, dan base notes-nya, lalu coba lewat decant sebelum membeli botol penuh.</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('catalog') }}" wire:navigate class="btn-primary">Lihat katalog</a>
                    <a href="{{ route('catalog', ['ukuran' => ['decant']]) }}" wire:navigate class="btn-secondary">Coba decant</a>
                </div>
            </div>

            <div class="relative bg-paper-raised">
                <div class="grid aspect-[5/4] grid-cols-3 items-end gap-3 px-8 pb-10 pt-14 sm:px-14">
                    <x-shop.bottle family="fresh" :seed="2" class="h-[76%] w-full" />
                    <x-shop.bottle family="oriental" :seed="0" class="h-full w-full" />
                    <x-shop.bottle family="floral" :seed="1" class="h-[68%] w-full" />
                </div>
            </div>
        </div>
    </section>

    {{-- Keluarga aroma --}}
    <section class="page mt-24 lg:mt-32">
        <div class="flex items-end justify-between gap-4">
            <h2 class="heading-section">Mulai dari keluarga aroma</h2>
        </div>
        <div class="mt-8 grid border-t border-line sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($scents as $scent)
                <a href="{{ route('catalog', ['aroma' => [$scent->value]]) }}" wire:navigate
                   class="group flex items-center justify-between gap-4 border-b border-line py-5 transition-colors duration-150 sm:block sm:border-r sm:px-5 sm:py-7 sm:[&:nth-child(3n)]:border-r-0 lg:[&:nth-child(3n)]:border-r lg:[&:nth-child(5n)]:border-r-0 sm:first:pl-0 lg:[&:nth-child(4)]:pl-5">
                    <span class="block">
                        <span class="block font-serif text-[26px] leading-none text-ink">{{ $scent->label() }}</span>
                        <span class="mt-2 block text-label text-ink-2">{{ $scentCopy[$scent->value] }}</span>
                    </span>
                    <x-lucide-arrow-up-right class="size-4 shrink-0 text-ink-2 transition-transform duration-200 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-ink sm:mt-6" stroke-width="1.25" />
                </a>
            @endforeach
        </div>
    </section>

    {{-- Kategori --}}
    <section class="page mt-24 lg:mt-32">
        <div class="grid gap-px bg-line sm:grid-cols-3">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category) }}" wire:navigate
                   class="group flex items-end justify-between bg-paper-card px-6 py-8 transition-colors duration-200 hover:bg-ink hover:text-white sm:aspect-[4/3] sm:px-8 sm:py-8">
                    <span>
                        <span class="eyebrow block transition-colors duration-200 group-hover:text-white/70">Parfum</span>
                        <span class="mt-2 block font-serif text-[34px] leading-none">{{ $category->name }}</span>
                        <span class="mt-3 block text-label tabular-nums text-ink-2 transition-colors duration-200 group-hover:text-white/70">{{ $category->products_count }} parfum</span>
                    </span>
                    <x-lucide-arrow-right class="size-5 transition-transform duration-200 group-hover:translate-x-1" stroke-width="1.25" />
                </a>
            @endforeach
        </div>
    </section>

    {{-- Terbaru --}}
    <section class="page mt-24 lg:mt-32">
        <div class="flex items-end justify-between gap-4">
            <h2 class="heading-section">Baru masuk</h2>
            <a href="{{ route('catalog') }}" wire:navigate class="link shrink-0 text-label uppercase tracking-label">Semua parfum</a>
        </div>
        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 md:grid-cols-4">
            @foreach ($newest as $product)
                <x-shop.product-card :product="$product" :eager="$loop->index < 4" />
            @endforeach
        </div>
    </section>

    @if ($bestsellers->contains(fn ($p) => (int) $p->sold_qty > 0))
        <section class="page mt-24 lg:mt-32">
            <div class="flex items-end justify-between gap-4">
                <h2 class="heading-section">Paling sering dibeli</h2>
                <a href="{{ route('catalog', ['urut' => 'terlaris']) }}" wire:navigate class="link shrink-0 text-label uppercase tracking-label">Lihat terlaris</a>
            </div>
            <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 md:grid-cols-4">
                @foreach ($bestsellers as $product)
                    <x-shop.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif
</x-app-layout>
