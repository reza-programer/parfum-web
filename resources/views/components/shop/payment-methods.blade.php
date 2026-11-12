@props(['methods', 'model' => 'paymentMethod', 'selected' => null])
@php
    $banks = collect($methods)->reject(fn ($m) => $m->isQris())->values();
    $qris = collect($methods)->first(fn ($m) => $m->isQris());
    $visible = 6;
    $selectedIndex = $banks->search(fn ($m) => $m->key() === $selected);
    $row = fn (string $key) => [
        'flex min-h-14 cursor-pointer items-center gap-3 rounded-md border px-3.5 py-3 transition-colors duration-150 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-primary/40',
        'border-primary bg-primary-soft' => $selected === $key,
        'border-line bg-paper-card hover:border-ink-faint' => $selected !== $key,
    ];
    $radio = 'size-4 shrink-0 border-ink-faint text-primary focus:ring-0 focus:ring-offset-0';
    $logo = 'h-5 w-12 shrink-0 object-contain object-left';
@endphp
{{-- Pilihan bersudut: VA per bank lalu QRIS. Bank di luar 6 teratas dilipat supaya ringkas. --}}
<div {{ $attributes->class('space-y-5') }} x-data="{ all: {{ $selectedIndex !== false && $selectedIndex >= $visible ? 'true' : 'false' }} }">
    @if ($banks->isNotEmpty())
        <div role="radiogroup" aria-label="Transfer virtual account">
            <p class="text-body font-semibold text-ink">Transfer virtual account</p>
            <div class="mt-2.5 grid gap-2 sm:grid-cols-2">
                @foreach ($banks as $i => $method)
                    <label wire:key="pm-{{ $method->key() }}" @class($row($method->key())) @if ($i >= $visible) x-show="all" x-cloak @endif>
                        <input type="radio" wire:model.live="{{ $model }}" value="{{ $method->key() }}" class="{{ $radio }}">
                        @if ($method->logoUrl)
                            <img src="{{ $method->logoUrl }}" alt="" class="{{ $logo }}" loading="lazy" referrerpolicy="no-referrer">
                        @endif
                        <span class="text-body text-ink">{{ $method->label() }}</span>
                    </label>
                @endforeach
            </div>
            @if ($banks->count() > $visible)
                <button type="button" x-show="! all" x-on:click="all = true" class="link mt-2.5 text-body">Tampilkan semua bank ({{ $banks->count() }})</button>
            @endif
        </div>
    @endif

    @if ($qris)
        <div role="radiogroup" aria-label="QRIS">
            <p class="text-body font-semibold text-ink">QRIS</p>
            <div class="mt-2.5">
                <label wire:key="pm-qris" @class($row('qris'))>
                    <input type="radio" wire:model.live="{{ $model }}" value="qris" class="{{ $radio }}">
                    @if ($qris->logoUrl)
                        <img src="{{ $qris->logoUrl }}" alt="" class="{{ $logo }}" loading="lazy" referrerpolicy="no-referrer">
                    @endif
                    <span class="min-w-0">
                        <span class="block text-body text-ink">QRIS</span>
                        <span class="meta block">Scan dari m-banking atau e-wallet. Kode berlaku 5 menit.</span>
                    </span>
                </label>
            </div>
        </div>
    @endif
</div>
