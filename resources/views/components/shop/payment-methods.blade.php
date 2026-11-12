@props(['methods', 'model' => 'paymentMethod', 'selected' => null])
@php
    $banks = collect($methods)->reject(fn ($m) => $m->isQris());
    $qris = collect($methods)->first(fn ($m) => $m->isQris());
    $tile = fn (string $key) => [
        'card flex cursor-pointer items-center gap-3 px-3 py-2.5 transition-colors duration-150 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ember/60',
        'border-ink' => $selected === $key,
        'hover:border-ink/30' => $selected !== $key,
    ];
@endphp
<div {{ $attributes->class('space-y-5') }}>
    @if ($banks->isNotEmpty())
        <fieldset>
            <legend class="meta">Transfer virtual account</legend>
            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($banks as $method)
                    <label wire:key="pm-{{ $method->key() }}" @class($tile($method->key()))>
                        <input type="radio" wire:model.live="{{ $model }}" value="{{ $method->key() }}" class="sr-only">
                        @if ($method->logoUrl)
                            <img src="{{ $method->logoUrl }}" alt="" class="h-5 w-10 shrink-0 object-contain" loading="lazy" referrerpolicy="no-referrer">
                        @endif
                        <span class="truncate text-sm">{{ $method->label() }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    @if ($qris)
        <fieldset>
            <legend class="meta">QRIS</legend>
            <label wire:key="pm-qris" @class([...$tile('qris'), 'mt-2'])>
                <input type="radio" wire:model.live="{{ $model }}" value="qris" class="sr-only">
                @if ($qris->logoUrl)
                    <img src="{{ $qris->logoUrl }}" alt="" class="h-5 w-10 shrink-0 object-contain" loading="lazy" referrerpolicy="no-referrer">
                @endif
                <span class="min-w-0">
                    <span class="block text-sm">QRIS</span>
                    <span class="meta block">Scan dari m-banking atau e-wallet. Kode berlaku 5 menit.</span>
                </span>
            </label>
        </fieldset>
    @endif
</div>
