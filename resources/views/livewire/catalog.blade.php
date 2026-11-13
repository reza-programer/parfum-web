@php
    $chips = $this->activeChips;
    // grid-flow-row-dense: ubin promo selebar grid tidak meninggalkan sel kosong di baris sebelumnya.
    $grid = 'grid grid-flow-row-dense grid-cols-2 gap-x-3 gap-y-8 sm:gap-x-5 md:grid-cols-3 xl:grid-cols-4';
    $promoAfter = min(8, $products->count());
    $crumb = 'hover:text-ink hover:underline hover:underline-offset-4';
@endphp
{{-- Katalog gaya newbalance.co.id (docs/plans/redesign-nb.md): sidebar SARING, judul kapital, dropdown urutan. --}}
<div class="page pt-5 lg:pt-6">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-caption font-bold uppercase tracking-nav text-ink-2">
        <a href="{{ route('home') }}" wire:navigate class="{{ $crumb }}">Beranda</a>
        <span aria-hidden="true">/</span>
        @if ($this->lockedBrand)
            <a href="{{ route('brands.index') }}" wire:navigate class="{{ $crumb }}">Brand</a>
            <span aria-hidden="true">/</span>
        @elseif (! $this->lockedCategory && $q !== '')
            <a href="{{ route('catalog') }}" wire:navigate class="{{ $crumb }}">Semua parfum</a>
            <span aria-hidden="true">/</span>
        @endif
        <span class="truncate text-ink">{{ $heading }}</span>
    </nav>

    <div class="mt-5 lg:grid lg:grid-cols-[220px_minmax(0,1fr)] lg:items-start lg:gap-10">
        {{-- Sidebar SARING (desktop), menempel saat discroll --}}
        <aside class="no-scrollbar sticky top-[calc(var(--header-h)+1.5rem)] hidden max-h-[calc(100dvh-var(--header-h)-3rem)] overflow-y-auto overscroll-contain lg:block" aria-label="Saring">
            <div class="flex items-baseline justify-between border-b border-ink pb-3">
                <p class="text-[22px] font-bold uppercase tracking-heading text-ink">Saring</p>
                @if ($chips)
                    <button type="button" wire:click="resetFilters" class="text-label underline underline-offset-4 hover:text-primary">Reset</button>
                @endif
            </div>
            @include('livewire.partials.catalog-filters', ['prefix' => 'd'])
        </aside>

        <section class="min-w-0">
            <div class="flex flex-wrap items-end justify-between gap-x-6 gap-y-3">
                <h1 class="heading-page">{{ $heading }} <span class="font-normal normal-case text-ink-2 tabular-nums" data-testid="result-count">({{ $products->total() }} parfum)</span></h1>
                <div class="hidden items-center gap-2 lg:flex">
                    <label for="sort" class="text-label font-bold uppercase tracking-nav text-ink">Urutkan</label>
                    <select id="sort" wire:model.live="sort" class="h-10 border-line bg-paper-card py-0 pl-3 pr-9 text-body text-ink focus:border-ink focus:ring-0">
                        @foreach (\App\Livewire\Catalog::SORT_TABS as $value => $label)
                            <option value="{{ $value }}" data-sort="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Bilah HP/tablet: SARING dan URUTKAN, menempel di bawah header --}}
            <div class="sticky top-[var(--header-h)] z-20 -mx-4 mt-4 grid h-12 grid-cols-2 border-y border-line bg-paper-card sm:-mx-6 lg:hidden">
                <button type="button" x-on:click="$dispatch('open-drawer', 'filters')" class="inline-flex items-center justify-center gap-2 border-r border-line text-label font-bold uppercase tracking-nav text-ink">
                    <x-lucide-sliders-horizontal class="size-4" stroke-width="1.75" />
                    Saring
                    @if ($this->activeFilterCount)
                        <span class="inline-flex size-5 items-center justify-center rounded-full bg-ink text-[10px] tabular-nums text-white">{{ $this->activeFilterCount }}</span>
                    @endif
                </button>
                <div class="relative">
                    <label for="sort-mobile" class="sr-only">Urutkan</label>
                    <select id="sort-mobile" wire:model.live="sort" class="h-full w-full border-0 bg-transparent py-0 pl-4 pr-9 text-center text-label font-bold uppercase tracking-nav text-ink focus:ring-0">
                        @foreach (\App\Livewire\Catalog::SORT_TABS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Chip filter aktif --}}
            @if ($chips)
                <div class="mt-5 flex flex-wrap items-center gap-2">
                    @foreach ($chips as $chip)
                        <button type="button" wire:click="removeFilter('{{ $chip['type'] }}', @js($chip['value']))" data-chip="{{ $chip['key'] }}"
                                class="chip h-8 pr-2" aria-label="Hapus filter {{ $chip['label'] }}">
                            {{ $chip['label'] }}
                            <x-lucide-x class="size-3.5" stroke-width="2" />
                        </button>
                    @endforeach
                    <button type="button" wire:click="resetFilters" class="ml-1 text-label underline underline-offset-4 hover:text-primary">Hapus semua</button>
                </div>
            @endif

            <div class="mt-6">
                @if ($products->isEmpty())
                    <div wire:loading.remove class="border-t border-line py-16 text-center">
                        <p class="text-[18px] font-bold uppercase tracking-heading text-ink">Tidak ada parfum yang cocok</p>
                        <p class="mt-2 text-body text-ink-2">Coba kurangi filter atau cari dengan kata lain.</p>
                        <button type="button" wire:click="resetFilters" class="btn-secondary mt-6">Hapus filter</button>
                    </div>
                @else
                    <div wire:loading.remove class="{{ $grid }}">
                        @foreach ($products as $product)
                            <x-shop.product-card :product="$product" :eager="$loop->index < 4" wire:key="p-{{ $product->id }}" />

                            {{-- Ubin promo decant di tengah grid (hanya kalau ada decant berstok) --}}
                            @if ($loop->iteration === $promoAfter && $promoAfter >= 4 && $this->promoDecant)
                                <a href="{{ route('catalog', ['ukuran' => ['decant']]) }}" wire:navigate data-promo="decant" wire:key="promo-decant"
                                   class="group col-span-full grid items-center overflow-hidden bg-paper-raised sm:grid-cols-[1.4fr_1fr]">
                                    <span class="block aspect-[16/7] overflow-hidden sm:aspect-auto sm:h-full">
                                        <img src="{{ asset('images/tiles/kategori-decant.webp') }}" alt="" class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]" loading="lazy" decoding="async">
                                    </span>
                                    <span class="block px-6 py-6 sm:px-10">
                                        <span class="block text-label font-bold uppercase tracking-nav text-ink-2">Decant</span>
                                        <span class="mt-2 block text-[24px] font-semibold leading-tight tracking-tight text-ink">Coba dulu lewat decant</span>
                                        <span class="mt-2 block text-body text-ink">Ukuran kecil untuk mengenal aromanya, mulai {{ rupiah($this->promoDecant) }}.</span>
                                        <span class="mt-4 inline-block border-b-2 border-ink pb-0.5 text-body font-bold text-ink group-hover:border-primary group-hover:text-primary">Belanja sekarang</span>
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    </div>

                    <div wire:loading.remove class="mt-12">
                        {{ $products->links('partials.pagination') }}
                    </div>
                @endif

                {{-- Skeleton saat filter, urutan, atau halaman berubah --}}
                <div wire:loading.grid class="{{ $grid }}" aria-hidden="true">
                    @foreach (range(1, min(8, max(4, $products->count()))) as $i)
                        <div>
                            <x-ui.skeleton class="aspect-square" />
                            <x-ui.skeleton class="mt-3 h-4 w-4/5" />
                            <x-ui.skeleton class="mt-2 h-3 w-1/2" />
                            <x-ui.skeleton class="mt-2 h-4 w-1/3" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    {{-- Saring di HP/tablet: panel dari bawah di ponsel, drawer samping di tablet --}}
    <x-ui.drawer name="filters" title="Saring" mobile-bottom class="lg:hidden">
        <div class="-mx-5 -my-5 px-5">
            @include('livewire.partials.catalog-filters', ['prefix' => 'm'])
        </div>

        <x-slot:footer>
            <div class="grid grid-cols-[1fr_2fr] gap-2">
                <button type="button" wire:click="resetFilters" class="btn-outline">Reset</button>
                <button type="button" x-on:click="$dispatch('close-drawer')" class="btn-primary">
                    Lihat {{ $products->total() }} parfum
                </button>
            </div>
        </x-slot:footer>
    </x-ui.drawer>
</div>
