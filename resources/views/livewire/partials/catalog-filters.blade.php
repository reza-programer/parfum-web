@php
    $check = 'size-4 rounded-none border-ink/40 text-ink focus:ring-0 focus:ring-offset-0';
    $row = 'flex min-h-9 cursor-pointer items-center gap-3 text-body text-ink-2 transition-colors duration-150 hover:text-ink has-[:checked]:text-ink';
    $chip = 'inline-flex h-9 items-center border border-line bg-paper-card px-3 text-label tabular-nums text-ink-2 transition-colors duration-150 peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white peer-focus-visible:ring-1 peer-focus-visible:ring-ink';
@endphp
<div class="divide-y divide-line [&>*]:py-6 [&>*:first-child]:pt-0">
    <div>
        <label for="{{ $prefix }}-q" class="eyebrow mb-3 block text-ink">Cari</label>
        <div class="relative">
            <x-lucide-search class="pointer-events-none absolute left-0 top-1/2 size-4 -translate-y-1/2 text-ink-2" stroke-width="1.25" />
            <input id="{{ $prefix }}-q" type="search" wire:model.live.debounce.400ms="q" placeholder="Nama atau brand"
                   class="h-10 w-full border-0 border-b border-line bg-transparent pl-6 pr-0 text-body text-ink placeholder:text-ink-faint focus:border-ink focus:ring-0">
        </div>
    </div>

    @unless ($this->lockedCategory)
        <fieldset>
            <legend class="eyebrow mb-3 text-ink">Kategori</legend>
            <div>
                <label class="{{ $row }}">
                    <input type="radio" wire:model.live="category" value="" class="size-4 border-ink/40 text-ink focus:ring-0 focus:ring-offset-0">
                    Semua
                </label>
                @foreach ($categories as $cat)
                    <label class="{{ $row }}">
                        <input type="radio" wire:model.live="category" value="{{ $cat->slug }}" class="size-4 border-ink/40 text-ink focus:ring-0 focus:ring-offset-0">
                        {{ $cat->name }}
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endunless

    <fieldset>
        <legend class="eyebrow mb-3 text-ink">Keluarga aroma</legend>
        <div>
            @foreach ($scentOptions as $scent)
                <label class="{{ $row }}">
                    <input type="checkbox" wire:model.live="scents" value="{{ $scent->value }}" class="{{ $check }}">
                    {{ $scent->label() }}
                </label>
            @endforeach
        </div>
    </fieldset>

    @unless ($this->lockedBrand)
        <fieldset>
            <legend class="eyebrow mb-3 text-ink">Brand</legend>
            <div>
                @foreach ($brandOptions as $brandOption)
                    <label class="{{ $row }}">
                        <input type="checkbox" wire:model.live="brands" value="{{ $brandOption->slug }}" class="{{ $check }}">
                        {{ $brandOption->name }}
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endunless

    <fieldset>
        <legend class="eyebrow mb-3 text-ink">Harga</legend>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}-min" class="sr-only">Harga minimum</label>
                <input id="{{ $prefix }}-min" type="text" inputmode="numeric" wire:model.live.debounce.500ms="min" placeholder="Min" class="field h-10 tabular-nums">
            </div>
            <div>
                <label for="{{ $prefix }}-max" class="sr-only">Harga maksimum</label>
                <input id="{{ $prefix }}-max" type="text" inputmode="numeric" wire:model.live.debounce.500ms="max" placeholder="Maks" class="field h-10 tabular-nums">
            </div>
        </div>
        <p class="meta mt-2">Dalam Rupiah, misalnya 200000</p>
    </fieldset>

    <fieldset>
        <legend class="eyebrow mb-3 text-ink">Ukuran</legend>
        <div class="flex flex-wrap gap-2">
            @foreach ($sizeOptions as $size)
                <label class="cursor-pointer">
                    <input type="checkbox" wire:model.live="sizes" value="{{ $size }}" class="peer sr-only">
                    <span class="{{ $chip }}">{{ $size }}ml</span>
                </label>
            @endforeach
            <label class="cursor-pointer">
                <input type="checkbox" wire:model.live="sizes" value="decant" class="peer sr-only">
                <span class="{{ $chip }}">Decant</span>
            </label>
        </div>
    </fieldset>

    @if ($this->concentrationOptions)
        <fieldset>
            <legend class="eyebrow mb-3 text-ink">Konsentrasi</legend>
            <div>
                @foreach ($this->concentrationOptions as $concentration)
                    <label class="{{ $row }}">
                        <input type="checkbox" wire:model.live="concentrations" value="{{ $concentration->value }}" class="{{ $check }}">
                        {{ $concentration->label() }}
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    <fieldset>
        <legend class="eyebrow mb-3 text-ink">Ketersediaan</legend>
        <label class="{{ $row }}">
            <input type="checkbox" wire:model.live="tersedia" class="{{ $check }}">
            Hanya yang tersedia
        </label>
    </fieldset>
</div>
