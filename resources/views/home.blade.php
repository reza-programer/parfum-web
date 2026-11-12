@php
    $grid = 'grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3 md:grid-cols-4 lg:grid-cols-5';
@endphp
<x-app-layout>
    <div class="page space-y-4 pt-3 sm:space-y-6 sm:pt-5">
        <x-shop.banner-carousel :banners="$banners" />

        {{-- Shortcut kategori dan keluarga aroma --}}
        <section class="card p-4 sm:p-5" aria-labelledby="shortcut-title">
            <h2 id="shortcut-title" class="heading-section">Kategori pilihan</h2>
            <div class="no-scrollbar -mx-4 mt-4 flex gap-1 overflow-x-auto px-4 sm:mx-0 sm:grid sm:grid-cols-5 sm:gap-y-5 sm:px-0 lg:grid-cols-9">
                @foreach ($shortcuts as $item)
                    <a href="{{ $item['url'] }}" wire:navigate data-shortcut="{{ $item['key'] }}"
                       class="group flex w-[76px] shrink-0 flex-col items-center gap-2 text-center sm:w-auto">
                        <span class="relative size-16 overflow-hidden rounded-full bg-paper-raised ring-1 ring-line transition duration-200 group-hover:ring-2 group-hover:ring-primary sm:size-20">
                            @if (! empty($item['asset']))
                                <img src="{{ $item['asset'] }}" alt="" class="size-full scale-[1.3] object-cover object-[50%_62%] transition-transform duration-300 group-hover:scale-[1.4]" loading="lazy" decoding="async">
                            @elseif ($item['image'])
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item['image']) }}" alt=""
                                     class="size-full scale-[1.45] object-cover object-[50%_58%] transition-transform duration-300 group-hover:scale-[1.55]" loading="lazy" decoding="async">
                            @else
                                <span class="flex size-full items-center justify-center p-3">
                                    <x-shop.bottle :family="$item['scent'] ?? 'woody'" :seed="$loop->index" />
                                </span>
                            @endif
                        </span>
                        <span class="text-label font-medium text-ink transition-colors duration-150 group-hover:text-primary">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Baru masuk --}}
        <section aria-labelledby="newest-title">
            <div class="mb-3 flex items-center justify-between gap-4">
                <h2 id="newest-title" class="heading-section">Baru masuk</h2>
                <a href="{{ route('catalog') }}" wire:navigate class="link inline-flex items-center gap-0.5 text-body">Lihat semua <x-lucide-chevron-right class="size-4" stroke-width="2" /></a>
            </div>
            <div class="{{ $grid }}">
                @foreach ($newest as $product)
                    <x-shop.product-card :product="$product" :eager="$loop->index < 2" />
                @endforeach
            </div>
        </section>

        @if ($bestsellers->isNotEmpty())
            <section aria-labelledby="best-title">
                <div class="mb-3 flex items-center justify-between gap-4">
                    <h2 id="best-title" class="heading-section">Paling laris</h2>
                    <a href="{{ route('catalog', ['urut' => 'terlaris']) }}" wire:navigate class="link inline-flex items-center gap-0.5 text-body">Lihat semua <x-lucide-chevron-right class="size-4" stroke-width="2" /></a>
                </div>
                <div class="{{ $grid }}">
                    @foreach ($bestsellers as $product)
                        <x-shop.product-card :product="$product" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($brands->isNotEmpty())
            <section aria-labelledby="brand-title">
                <div class="mb-3 flex items-center justify-between gap-4">
                    <h2 id="brand-title" class="heading-section">Brand pilihan</h2>
                    <a href="{{ route('brands.index') }}" wire:navigate class="link inline-flex items-center gap-0.5 text-body">Semua brand <x-lucide-chevron-right class="size-4" stroke-width="2" /></a>
                </div>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3 lg:grid-cols-4">
                    @foreach ($brands as $brand)
                        <a href="{{ route('brands.show', $brand) }}" wire:navigate class="card group block p-3 transition-shadow duration-200 hover:shadow-hover">
                            <div class="grid grid-cols-3 gap-1.5">
                                @foreach ($brand->preview as $item)
                                    <x-shop.product-image :product="$item" class="aspect-square rounded-md" />
                                @endforeach
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <span class="min-w-0">
                                    <span class="block truncate text-body font-bold text-ink group-hover:text-primary">{{ $brand->name }}</span>
                                    <span class="block text-label text-ink-2">{{ $brand->products_count }} parfum</span>
                                </span>
                                <x-lucide-chevron-right class="size-5 shrink-0 text-ink-2 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-primary" stroke-width="2" />
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
