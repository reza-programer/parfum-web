@props(['value', 'label' => 'Salin'])
<button type="button"
        x-data="{ copied: false }"
        x-on:click="navigator.clipboard.writeText(@js((string) $value)).then(() => { copied = true; setTimeout(() => copied = false, 1500) })"
        {{ $attributes->class('link text-body') }}>
    <span x-show="! copied">{{ $label }}</span>
    <span x-show="copied" x-cloak>Tersalin</span>
</button>
