<div class="page pt-4 sm:pt-6">
    @include('partials.account-nav')

    <div class="mt-5 flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
        <h1 class="heading-page">Wishlist</h1>
        @if ($items->isNotEmpty())
            <p class="meta tabular-nums">{{ $items->count() }} parfum</p>
        @endif
    </div>

    @if ($items->isEmpty())
        <div class="card mt-3 flex flex-col items-center px-6 py-14 text-center">
            <span class="inline-flex size-16 items-center justify-center rounded-full bg-paper-raised text-ink">
                <x-lucide-heart class="size-7" stroke-width="1.75" />
            </span>
            <p class="mt-4 text-[16px] font-bold text-ink">Wishlist masih kosong</p>
            <p class="mt-1 max-w-sm text-body text-ink-2">Tekan ikon hati di kartu atau halaman produk untuk menyimpan parfum incaranmu.</p>
            <a href="{{ route('catalog') }}" wire:navigate class="btn-primary mt-5">Lihat katalog</a>
        </div>
    @else
        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3 md:grid-cols-4 lg:grid-cols-5">
            @foreach ($items as $wish)
                <div wire:key="w-{{ $wish->id }}" class="flex flex-col">
                    <x-shop.product-card :product="$wish->product" :wished="true" class="flex-1" />
                    <div class="mt-2 flex items-center gap-1.5">
                        @if ($wish->product->isSoldOut())
                            <button type="button" disabled class="btn-outline btn-sm flex-1">Habis</button>
                        @else
                            <button type="button" wire:click="moveToCart({{ $wish->id }})" wire:loading.attr="disabled" wire:target="moveToCart({{ $wish->id }})" class="btn-primary btn-sm flex-1 px-3">Ke keranjang</button>
                        @endif
                        <button type="button" wire:click="remove({{ $wish->id }})" class="btn-outline btn-sm size-11 shrink-0 px-0 text-ink-2 hover:text-danger sm:size-9" aria-label="Hapus dari wishlist">
                            <x-lucide-trash-2 class="size-4" stroke-width="1.75" />
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
