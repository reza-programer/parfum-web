@php
    $sortSelect = 'h-10 border-line bg-paper-card py-0 pl-3 pr-9 text-[13px] text-ink focus:border-ink focus:ring-0';
@endphp
<div>
    <header class="page pt-10 lg:pt-14">
        <p class="eyebrow">
            @if ($this->lockedBrand) Brand @elseif ($this->lockedCategory) Kategori @else Katalog @endif
        </p>
        <div class="mt-3 flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
            <h1 class="heading-page">{{ $heading }}</h1>
            <p class="meta tabular-nums" data-testid="result-count">{{ $products->total() }} parfum</p>
        </div>
    </header>

    {{-- Bilah filter tablet/mobile --}}
    <div class="sticky top-16 z-20 mt-8 border-y border-line bg-paper/95 backdrop-blur lg:hidden">
        <div class="page flex h-14 items-center gap-3">
            <button type="button" x-on:click="$dispatch('open-drawer', 'filters')" class="inline-flex h-11 items-center gap-2 text-[13px] font-medium uppercase tracking-nav text-ink">
                <x-lucide-sliders-horizontal class="size-4" stroke-width="1.25" />
                Filter
                @if ($this->activeFilterCount)
                    <span class="inline-flex size-5 items-center justify-center rounded-full bg-ink text-[10px] tabular-nums text-white">{{ $this->activeFilterCount }}</span>
                @endif
            </button>
            <label for="sort-mobile" class="sr-only">Urutkan</label>
            <select id="sort-mobile" wire:model.live="sort" class="{{ $sortSelect }} ms-auto">
                @foreach (\App\Queries\CatalogQuery::SORTS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="page mt-8 lg:mt-12 lg:grid lg:grid-cols-[220px_1fr] lg:gap-12">
        <aside class="hidden lg:block">
            @include('livewire.partials.catalog-filters', ['prefix' => 'd'])
        </aside>

        <section>
            <div class="mb-8 hidden min-h-10 items-center justify-between gap-4 lg:flex">
                @if ($this->activeFilterCount || $q !== '')
                    <button type="button" wire:click="resetFilters" class="link text-[13px]">Hapus semua filter</button>
                @else
                    <span></span>
                @endif
                <div class="flex items-center gap-3">
                    <label for="sort" class="eyebrow">Urutkan</label>
                    <select id="sort" wire:model.live="sort" class="{{ $sortSelect }}">
                        @foreach (\App\Queries\CatalogQuery::SORTS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($products->isEmpty())
                <div wire:loading.remove class="border-t border-line py-16">
                    <p class="heading-section">Tidak ada parfum yang cocok</p>
                    <p class="mt-2 text-body text-ink-2">Coba kurangi filter atau cari dengan kata lain.</p>
                    <button type="button" wire:click="resetFilters" class="btn-secondary mt-6">Hapus filter</button>
                </div>
            @else
                <div wire:loading.remove class="grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <x-shop.product-card :product="$product" :eager="$loop->index < 4" wire:key="p-{{ $product->id }}" />
                    @endforeach
                </div>

                <div wire:loading.remove class="mt-14">
                    {{ $products->links('partials.pagination') }}
                </div>
            @endif

            {{-- Skeleton saat filter, urutan, atau halaman berubah --}}
            <div wire:loading.grid class="grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4" aria-hidden="true">
                @foreach (range(1, min(8, max(4, $products->count()))) as $i)
                    <div>
                        <x-ui.skeleton class="aspect-square" />
                        <x-ui.skeleton class="mt-4 h-3 w-1/3" />
                        <x-ui.skeleton class="mt-2 h-4 w-2/3" />
                        <x-ui.skeleton class="mt-2 h-3 w-1/2" />
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Filter tablet/mobile: drawer samping di tablet, panel dari bawah di ponsel (PRD wireframe 1) --}}
    <x-ui.drawer name="filters" title="Filter" mobile-bottom class="lg:hidden">
        @include('livewire.partials.catalog-filters', ['prefix' => 'm'])

        <x-slot:footer>
            <div class="flex items-center gap-3">
                <button type="button" wire:click="resetFilters" class="btn-ghost -ml-3">Hapus semua</button>
                <button type="button" x-on:click="$dispatch('close-drawer')" class="btn-primary ms-auto flex-1">
                    Lihat {{ $products->total() }} parfum
                </button>
            </div>
        </x-slot:footer>
    </x-ui.drawer>
</div>
