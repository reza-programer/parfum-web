@php
    $chips = $this->activeChips;
    $grid = 'grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3 xl:grid-cols-4';
@endphp
<div class="page pt-3 sm:pt-5">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-label text-ink-2">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-primary">Beranda</a>
        <x-lucide-chevron-right class="size-3.5" stroke-width="2" />
        @if ($this->lockedBrand)
            <a href="{{ route('brands.index') }}" wire:navigate class="hover:text-primary">Brand</a>
            <x-lucide-chevron-right class="size-3.5" stroke-width="2" />
        @elseif (! $this->lockedCategory && $q !== '')
            <a href="{{ route('catalog') }}" wire:navigate class="hover:text-primary">Semua parfum</a>
            <x-lucide-chevron-right class="size-3.5" stroke-width="2" />
        @endif
        <span class="truncate text-ink">{{ $heading }}</span>
    </nav>

    <div class="mt-3 lg:grid lg:grid-cols-[240px_minmax(0,1fr)] lg:items-start lg:gap-5">
        {{-- Kotak filter desktop: menempel saat discroll --}}
        <aside class="sticky top-[calc(var(--header-h)+1rem)] hidden max-h-[calc(100dvh-var(--header-h)-2rem)] overflow-y-auto overscroll-contain rounded-lg border border-line bg-paper-card no-scrollbar lg:block" aria-label="Filter">
            <div class="flex h-12 items-center justify-between border-b border-line px-4">
                <p class="text-[15px] font-bold text-ink">Filter</p>
                @if ($chips)
                    <button type="button" wire:click="resetFilters" class="link text-label">Reset</button>
                @endif
            </div>
            @include('livewire.partials.catalog-filters', ['prefix' => 'd'])
        </aside>

        <section class="min-w-0">
            <header class="mb-3">
                <h1 class="heading-page">{{ $heading }}</h1>
            </header>

            {{-- Bilah HP: Filter dan Urutkan, menempel di bawah header --}}
            <div class="sticky top-[var(--header-h)] z-20 -mx-3 mb-3 flex h-14 items-center gap-2 border-b border-line bg-paper px-3 sm:-mx-6 sm:px-6 lg:hidden">
                <button type="button" x-on:click="$dispatch('open-drawer', 'filters')" class="btn-outline btn-sm h-10 gap-1.5 px-3.5">
                    <x-lucide-sliders-horizontal class="size-4" stroke-width="2" />
                    Filter
                    @if ($this->activeFilterCount)
                        <span class="inline-flex size-5 items-center justify-center rounded-full bg-primary text-[10px] font-bold tabular-nums text-white">{{ $this->activeFilterCount }}</span>
                    @endif
                </button>
                <label for="sort-mobile" class="sr-only">Urutkan</label>
                <select id="sort-mobile" wire:model.live="sort" class="ms-auto h-10 rounded-md border-line bg-paper-card py-0 pl-3 pr-9 text-body font-medium text-ink focus:border-primary focus:ring-0">
                    @foreach (\App\Livewire\Catalog::SORT_TABS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Bilah urutan desktop --}}
            <div class="mb-3 hidden items-center gap-2 rounded-lg border border-line bg-paper-card px-4 py-2.5 lg:flex">
                <span class="mr-1 text-body text-ink-2">Urutkan</span>
                @foreach (\App\Livewire\Catalog::SORT_TABS as $value => $label)
                    <button type="button" wire:click="$set('sort', '{{ $value }}')" data-sort="{{ $value }}"
                            @class(['chip h-8', 'chip-active' => $sort === $value]) aria-pressed="{{ $sort === $value ? 'true' : 'false' }}">{{ $label }}</button>
                @endforeach
                <p class="meta ms-auto tabular-nums" data-testid="result-count">{{ $products->total() }} parfum</p>
            </div>

            {{-- Chip filter aktif --}}
            @if ($chips)
                <div class="mb-3 flex flex-wrap items-center gap-1.5">
                    @foreach ($chips as $chip)
                        <button type="button" wire:click="removeFilter('{{ $chip['type'] }}', @js($chip['value']))" data-chip="{{ $chip['key'] }}"
                                class="chip chip-active h-8 pr-2" aria-label="Hapus filter {{ $chip['label'] }}">
                            {{ $chip['label'] }}
                            <x-lucide-x class="size-3.5" stroke-width="2.25" />
                        </button>
                    @endforeach
                    <button type="button" wire:click="resetFilters" class="link ml-1 text-label">Hapus semua</button>
                </div>
            @endif

            <p class="meta mb-2 tabular-nums lg:hidden">{{ $products->total() }} parfum</p>

            @if ($products->isEmpty())
                <div wire:loading.remove class="card flex flex-col items-center px-6 py-14 text-center">
                    <span class="inline-flex size-14 items-center justify-center rounded-full bg-primary-soft text-primary">
                        <x-lucide-search-x class="size-6" stroke-width="1.75" />
                    </span>
                    <p class="mt-4 text-[15px] font-bold text-ink">Tidak ada parfum yang cocok</p>
                    <p class="mt-1 text-body text-ink-2">Coba kurangi filter atau cari dengan kata lain.</p>
                    <button type="button" wire:click="resetFilters" class="btn-secondary mt-5">Hapus filter</button>
                </div>
            @else
                <div wire:loading.remove class="{{ $grid }}">
                    @foreach ($products as $product)
                        <x-shop.product-card :product="$product" :eager="$loop->index < 4" wire:key="p-{{ $product->id }}" />
                    @endforeach
                </div>

                <div wire:loading.remove class="mt-8">
                    {{ $products->links('partials.pagination') }}
                </div>
            @endif

            {{-- Skeleton saat filter, urutan, atau halaman berubah --}}
            <div wire:loading.grid class="{{ $grid }}" aria-hidden="true">
                @foreach (range(1, min(8, max(4, $products->count()))) as $i)
                    <div class="overflow-hidden rounded-lg border border-line bg-paper-card">
                        <x-ui.skeleton class="aspect-square" />
                        <div class="space-y-2 p-3">
                            <x-ui.skeleton class="h-4 w-4/5 rounded" />
                            <x-ui.skeleton class="h-4 w-1/2 rounded" />
                            <x-ui.skeleton class="h-3 w-2/3 rounded" />
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Filter HP/tablet: panel dari bawah di ponsel, drawer samping di tablet --}}
    <x-ui.drawer name="filters" title="Filter" mobile-bottom class="lg:hidden">
        <div class="-mx-5 -my-5">
            @include('livewire.partials.catalog-filters', ['prefix' => 'm'])
        </div>

        <x-slot:footer>
            <div class="flex items-center gap-3">
                <button type="button" wire:click="resetFilters" class="btn-outline flex-1">Reset</button>
                <button type="button" x-on:click="$dispatch('close-drawer')" class="btn-primary flex-[2]">
                    Lihat {{ $products->total() }} parfum
                </button>
            </div>
        </x-slot:footer>
    </x-ui.drawer>
</div>
