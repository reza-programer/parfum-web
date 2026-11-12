@props(['product', 'wished' => null, 'eager' => false])
@php
    $soldOut = $product->isSoldOut();
    $wished ??= \App\Support\WishlistIds::has($product->id);
@endphp
<article {{ $attributes->merge(['class' => 'group relative']) }}>
    <div class="relative overflow-hidden bg-paper-raised">
        <a href="{{ route('products.show', $product) }}" wire:navigate class="block" tabindex="-1" aria-hidden="true">
            <x-shop.product-image :product="$product" :eager="$eager"
                                  class="aspect-square transition-transform duration-500 ease-out group-hover:scale-[1.03] motion-reduce:transition-none" />
        </a>
        @if ($soldOut)
            <x-ui.badge class="absolute left-0 top-0 bg-paper-card text-ink-2">Habis</x-ui.badge>
        @endif
        <livewire:product-actions :product="$product" :wished="$wished" :key="'pa-'.$product->id" />
    </div>

    <a href="{{ route('products.show', $product) }}" wire:navigate class="mt-4 block">
        <div class="flex items-baseline justify-between gap-3">
            <p class="eyebrow truncate">{{ $product->brand->name }}</p>
            <p class="shrink-0 text-caption uppercase tracking-label text-ink-2">{{ $product->scent_family->label() }}</p>
        </div>
        <h3 class="mt-1.5 text-product font-medium text-ink decoration-ink/30 underline-offset-4 group-hover:underline">{{ $product->name }}</h3>
        @if ($soldOut)
            <p class="mt-1 text-body text-ink-2">Stok habis</p>
        @else
            <p class="price mt-1 text-body text-ink"><span class="text-ink-2">mulai</span> {{ rupiah($product->startingPrice()) }}</p>
        @endif
    </a>
</article>
