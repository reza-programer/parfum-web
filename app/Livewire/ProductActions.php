<?php

namespace App\Livewire;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Aksi di atas foto kartu produk: wishlist dan tambah cepat (pilih ukuran, langsung masuk keranjang).
 * Dipakai di katalog, beranda, wishlist, dan produk terkait.
 */
class ProductActions extends Component
{
    public Product $product;

    public bool $wished = false;

    public function mount(Product $product, bool $wished = false): void
    {
        $this->product = $product->loadMissing('variants');
        $this->wished = $wished;
    }

    public function toggleWishlist(WishlistService $wishlist): void
    {
        if (! auth()->check()) {
            $this->toLogin();

            return;
        }

        $this->wished = $wishlist->toggle(auth()->user(), $this->product);
        $this->dispatch('toast', message: $this->wished ? 'Disimpan ke wishlist' : 'Dihapus dari wishlist');
    }

    public function add(int $variantId, CartService $cart): void
    {
        if (! auth()->check()) {
            $this->toLogin();

            return;
        }

        $variant = $this->product->loadMissing('variants')->variants->firstWhere('id', $variantId);

        if (! $variant) {
            return;
        }

        try {
            $cart->add(auth()->user(), $variant, 1);
        } catch (CartException $e) {
            $this->dispatch('toast', message: $e->getMessage());

            return;
        }

        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: "{$this->product->name} {$variant->label()} masuk keranjang");
    }

    public function render(): View
    {
        return view('livewire.product-actions', [
            'variants' => $this->product->variants->sortBy([['is_decant', 'desc'], ['size_ml', 'asc']])->values(),
        ]);
    }

    private function toLogin(): void
    {
        session()->put('url.intended', url()->previous() ?: route('products.show', $this->product));
        $this->redirectRoute('login', navigate: true);
    }
}
