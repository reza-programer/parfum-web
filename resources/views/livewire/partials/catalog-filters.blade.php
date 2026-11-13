{{--
    Isi SARING di katalog (sidebar desktop dan drawer HP), gaya polos NB: tiap bagian bisa dibuka-tutup.
    $prefix membedakan id input desktop (d) dan HP (m).
--}}
@php
    $check = 'size-4 rounded-none border-ink/40 text-ink focus:ring-0 focus:ring-offset-0';
    $radio = 'size-4 border-ink/40 text-ink focus:ring-0 focus:ring-offset-0';
    $row = 'flex min-h-9 cursor-pointer items-center gap-3 text-body text-ink-2 transition-colors duration-150 hover:text-ink has-[:checked]:font-medium has-[:checked]:text-ink';
    $sizeChip = 'chip h-9 peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-ink';
    $digits = fn (string $v) => preg_replace('/\D/', '', $v);
@endphp
<div class="divide-y divide-line">
    @unless ($this->lockedCategory)
        <div x-data="{ open: true }" class="py-3" role="group" aria-labelledby="{{ $prefix }}-f-category">
            <button type="button" id="{{ $prefix }}-f-category" x-on:click="open = ! open" :aria-expanded="open" class="flex h-9 w-full items-center justify-between text-label font-bold uppercase tracking-nav text-ink">
                Kategori
                <x-lucide-chevron-down class="size-4 text-ink transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" stroke-width="1.75" />
            </button>
            <div x-show="open" x-collapse>
                <label class="{{ $row }}">
                    <input type="radio" wire:model.live="category" value="" class="{{ $radio }}">
                    Semua
                </label>
                @foreach ($categories as $cat)
                    <label class="{{ $row }}">
                        <input type="radio" wire:model.live="category" value="{{ $cat->slug }}" class="{{ $radio }}">
                        {{ $cat->name }}
                    </label>
                @endforeach
            </div>
        </div>
    @endunless

    <div x-data="{ open: true }" class="py-3" role="group" aria-labelledby="{{ $prefix }}-f-scent">
        <button type="button" id="{{ $prefix }}-f-scent" x-on:click="open = ! open" :aria-expanded="open" class="flex h-9 w-full items-center justify-between text-label font-bold uppercase tracking-nav text-ink">
            Keluarga aroma
            <x-lucide-chevron-down class="size-4 text-ink transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" stroke-width="1.75" />
        </button>
        <div x-show="open" x-collapse>
            @foreach ($scentOptions as $scent)
                <label class="{{ $row }}">
                    <input type="checkbox" wire:model.live="scents" value="{{ $scent->value }}" class="{{ $check }}">
                    {{ $scent->label() }}
                </label>
            @endforeach
        </div>
    </div>

    @unless ($this->lockedBrand)
        <div x-data="{ open: true, more: false }" class="py-3" role="group" aria-labelledby="{{ $prefix }}-f-brand">
            <button type="button" id="{{ $prefix }}-f-brand" x-on:click="open = ! open" :aria-expanded="open" class="flex h-9 w-full items-center justify-between text-label font-bold uppercase tracking-nav text-ink">
                Brand
                <x-lucide-chevron-down class="size-4 text-ink transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" stroke-width="1.75" />
            </button>
            <div x-show="open" x-collapse>
                @foreach ($brandOptions as $brandOption)
                    @php $alwaysShown = $loop->index < 5 || in_array($brandOption->slug, $brands, true); @endphp
                    <label class="{{ $row }}" @unless ($alwaysShown) x-show="more" x-cloak @endunless>
                        <input type="checkbox" wire:model.live="brands" value="{{ $brandOption->slug }}" class="{{ $check }}">
                        {{ $brandOption->name }}
                    </label>
                @endforeach
                @if ($brandOptions->count() > 5)
                    <button type="button" x-on:click="more = ! more" class="link mt-1 h-8 text-label" x-text="more ? 'Lebih sedikit' : 'Lihat semua ({{ $brandOptions->count() }})'">Lihat semua ({{ $brandOptions->count() }})</button>
                @endif
            </div>
        </div>
    @endunless

    <div x-data="{ open: true }" class="py-3" role="group" aria-labelledby="{{ $prefix }}-f-price">
        <button type="button" id="{{ $prefix }}-f-price" x-on:click="open = ! open" :aria-expanded="open" class="flex h-9 w-full items-center justify-between text-label font-bold uppercase tracking-nav text-ink">
            Harga
            <x-lucide-chevron-down class="size-4 text-ink transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" stroke-width="1.75" />
        </button>
        <div x-show="open" x-collapse>
            <div class="space-y-2 pb-1 pt-1">
                <div class="relative">
                    <label for="{{ $prefix }}-min" class="sr-only">Harga minimum</label>
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-label font-semibold text-ink-2">Rp</span>
                    <input id="{{ $prefix }}-min" type="text" inputmode="numeric" wire:model.live.debounce.500ms="min" placeholder="Harga minimum" class="field pl-9 tabular-nums sm:h-10">
                </div>
                <div class="relative">
                    <label for="{{ $prefix }}-max" class="sr-only">Harga maksimum</label>
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-label font-semibold text-ink-2">Rp</span>
                    <input id="{{ $prefix }}-max" type="text" inputmode="numeric" wire:model.live.debounce.500ms="max" placeholder="Harga maksimum" class="field pl-9 tabular-nums sm:h-10">
                </div>
            </div>
            <div class="flex flex-wrap gap-1.5 pb-1 pt-2">
                @foreach (\App\Livewire\Catalog::PRICE_RANGES as $range)
                    @php $isActive = $digits($min) === (string) ($range['min'] ?? '') && $digits($max) === (string) ($range['max'] ?? ''); @endphp
                    <button type="button" wire:click="setPriceRange({{ $range['min'] ?? 'null' }}, {{ $range['max'] ?? 'null' }})"
                            @class(['chip h-9', 'chip-active' => $isActive]) aria-pressed="{{ $isActive ? 'true' : 'false' }}">{{ $range['label'] }}</button>
                @endforeach
            </div>
        </div>
    </div>

    <div x-data="{ open: true }" class="py-3" role="group" aria-labelledby="{{ $prefix }}-f-size">
        <button type="button" id="{{ $prefix }}-f-size" x-on:click="open = ! open" :aria-expanded="open" class="flex h-9 w-full items-center justify-between text-label font-bold uppercase tracking-nav text-ink">
            Ukuran
            <x-lucide-chevron-down class="size-4 text-ink transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" stroke-width="1.75" />
        </button>
        <div x-show="open" x-collapse>
            <div class="flex flex-wrap gap-1.5 pb-1 pt-1">
                <label class="cursor-pointer">
                    <input type="checkbox" wire:model.live="sizes" value="decant" class="peer sr-only">
                    <span class="{{ $sizeChip }}">Decant</span>
                </label>
                @foreach ($sizeOptions as $size)
                    <label class="cursor-pointer">
                        <input type="checkbox" wire:model.live="sizes" value="{{ $size }}" class="peer sr-only">
                        <span class="{{ $sizeChip }} tabular-nums">{{ $size }}ml</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    @if ($this->concentrationOptions)
        <div x-data="{ open: true }" class="py-3" role="group" aria-labelledby="{{ $prefix }}-f-concentration">
            <button type="button" id="{{ $prefix }}-f-concentration" x-on:click="open = ! open" :aria-expanded="open" class="flex h-9 w-full items-center justify-between text-label font-bold uppercase tracking-nav text-ink">
                Konsentrasi
                <x-lucide-chevron-down class="size-4 text-ink transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" stroke-width="1.75" />
            </button>
            <div x-show="open" x-collapse>
                @foreach ($this->concentrationOptions as $concentration)
                    <label class="{{ $row }}">
                        <input type="checkbox" wire:model.live="concentrations" value="{{ $concentration->value }}" class="{{ $check }}">
                        {{ $concentration->label() }}
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div class="py-3" role="group" aria-label="Ketersediaan">
        <p class="flex h-9 items-center text-label font-bold uppercase tracking-nav text-ink">Ketersediaan</p>
        <label class="{{ $row }}">
            <input type="checkbox" wire:model.live="tersedia" class="{{ $check }}">
            Hanya yang tersedia
        </label>
    </div>
</div>
