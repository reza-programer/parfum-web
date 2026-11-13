{{-- Ikon wishlist di pojok kanan atas foto kartu (x-shop.product-card). --}}
<div class="absolute right-0 top-0">
    <button type="button" wire:click="toggleWishlist" wire:loading.attr="disabled" wire:target="toggleWishlist"
            class="inline-flex size-11 items-center justify-center text-ink transition-transform duration-200 ease-out active:scale-90"
            aria-label="{{ $wished ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}" aria-pressed="{{ $wished ? 'true' : 'false' }}">
        <x-lucide-heart @class(['size-5 transition-[fill,color,transform] duration-200', 'fill-primary text-primary scale-110' => $wished]) stroke-width="1.5" />
    </button>
</div>
