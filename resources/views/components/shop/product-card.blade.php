@props(['product', 'wished' => null, 'eager' => false])
{{--
    Kartu produk polos gaya newbalance.co.id (docs/plans/redesign-nb.md): foto, satu tag hitam, wishlist, nama, harga.
    Tag dari data: TERLARIS (5 teratas yang terjual), BARU (30 hari), HABIS. Rating dan terjual hanya di halaman detail.
--}}
@php
    $soldOut = $product->isSoldOut();
    $wished ??= \App\Support\WishlistIds::has($product->id);
    $tag = match (true) {
        $soldOut => ['habis', 'Habis'],
        \App\Support\BestsellerIds::has($product->id) => ['terlaris', 'Terlaris'],
        $product->isNew() => ['baru', 'Baru'],
        default => null,
    };
    $url = route('products.show', $product);
@endphp
<article {{ $attributes->merge(['class' => 'group relative flex flex-col']) }}>
    <div class="relative">
        <a href="{{ $url }}" wire:navigate class="block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-shop.product-image :product="$product" :eager="$eager"
                                  @class(['aspect-square transition-transform duration-500 ease-out group-hover:scale-[1.03] motion-reduce:transition-none', 'opacity-60' => $soldOut]) />
        </a>
        @if ($tag)
            <span @class(['tag absolute left-0 top-3', 'bg-ink-2' => $tag[0] === 'habis']) data-label="{{ $tag[0] }}">{{ $tag[1] }}</span>
        @endif
        <livewire:product-actions :product="$product" :wished="$wished" :key="'pa-'.$product->id" />
    </div>

    <a href="{{ $url }}" wire:navigate class="block pt-3">
        <h3 class="line-clamp-2 text-product font-semibold text-ink group-hover:underline group-hover:underline-offset-4">{{ $product->name }}</h3>
        <p class="mt-0.5 truncate text-label text-ink-2">{{ $product->brand->name }} · {{ $product->scent_family->label() }}</p>
        <p @class(['price mt-1.5 text-[15px] font-bold', 'text-ink' => ! $soldOut, 'text-ink-2' => $soldOut])>
            @if ($product->hasPriceRange())<span class="mr-1 text-label font-normal text-ink-2">Mulai</span>@endif{{ rupiah($product->startingPrice()) }}
        </p>
        @if ($soldOut)
            <p class="mt-0.5 text-label font-medium text-primary">Stok habis</p>
        @endif
    </a>
</article>
