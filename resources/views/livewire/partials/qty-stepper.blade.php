{{-- Pengatur jumlah untuk ProductDetail (memanggil increment/decrement). --}}
<div class="inline-flex h-10 items-center rounded-md border border-line bg-paper-card">
    <button type="button" wire:click="decrement" @disabled($qty <= 1) class="inline-flex size-10 items-center justify-center text-primary transition-colors duration-150 disabled:text-ink-faint" aria-label="Kurangi jumlah">
        <x-lucide-minus class="size-4" stroke-width="2.25" />
    </button>
    <span class="w-9 text-center text-body font-medium tabular-nums text-ink" data-testid="qty">{{ $qty }}</span>
    <button type="button" wire:click="increment" @disabled($this->variant && $qty >= $this->variant->stock) class="inline-flex size-10 items-center justify-center text-primary transition-colors duration-150 disabled:text-ink-faint" aria-label="Tambah jumlah">
        <x-lucide-plus class="size-4" stroke-width="2.25" />
    </button>
</div>
