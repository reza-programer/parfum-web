<div class="page pt-10 lg:pt-14">
    @include('partials.account-nav')

    <div class="mt-10 flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
        <h1 class="heading-page">Wishlist</h1>
        @if ($items->isNotEmpty())
            <p class="meta tabular-nums">{{ $items->count() }} parfum</p>
        @endif
    </div>

    @if ($items->isEmpty())
        <div class="mt-10 max-w-xl border-t border-line pt-10">
            <p class="heading-section">Wishlist masih kosong</p>
            <p class="mt-2 text-body text-ink-2">Tekan ikon hati di kartu atau halaman produk untuk menyimpan parfum incaranmu.</p>
            <a href="{{ route('catalog') }}" wire:navigate class="btn-primary mt-6">Lihat katalog</a>
        </div>
    @else
        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-12 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($items as $wish)
                <div wire:key="w-{{ $wish->id }}">
                    <x-shop.product-card :product="$wish->product" :wished="true" />
                    <div class="mt-4 flex items-center gap-2">
                        @if ($wish->product->isSoldOut())
                            <button type="button" disabled class="btn-secondary btn-sm flex-1">Habis</button>
                        @else
                            <button type="button" wire:click="moveToCart({{ $wish->id }})" wire:loading.attr="disabled" wire:target="moveToCart({{ $wish->id }})" class="btn-primary btn-sm flex-1 px-3">Ke keranjang</button>
                        @endif
                        <button type="button" wire:click="remove({{ $wish->id }})" class="inline-flex size-11 shrink-0 items-center justify-center border border-line text-ink-2 hover:border-ink hover:text-ink" aria-label="Hapus dari wishlist">
                            <x-lucide-x class="size-4" stroke-width="1.25" />
                        </button>
                    </div>
                    @error('wish.'.$wish->id)
                        <p class="mt-2 text-label text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    @endif
</div>
