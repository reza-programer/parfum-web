@props(['value', 'label' => 'Salin'])
<button type="button"
        x-data="{ copied: false }"
        x-on:click="navigator.clipboard.writeText(@js((string) $value)).then(() => { copied = true; setTimeout(() => copied = false, 1500) })"
        {{ $attributes->class('text-sm text-ink-2 underline decoration-ink/20 underline-offset-4 hover:text-ink') }}>
    <span x-show="! copied">{{ $label }}</span>
    <span x-show="copied" x-cloak>Tersalin</span>
</button>
