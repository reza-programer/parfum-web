<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Ikon wishlist di atas foto kartu produk (kartu polos gaya NB, docs/plans/redesign-nb.md).
 * Dipakai di katalog, beranda, wishlist, dan produk terkait.
 */
class ProductActions extends Component
{
    public Product $product;

    public bool $wished = false;

    public function mount(Product $product, bool $wished = false): void
    {
        $this->product = $product;
        $this->wished = $wished;
    }

    public function toggleWishlist(WishlistService $wishlist): void
    {
        if (! auth()->check()) {
            session()->put('url.intended', url()->previous() ?: route('products.show', $this->product));
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $this->wished = $wishlist->toggle(auth()->user(), $this->product);
        $this->dispatch('toast', message: $this->wished ? 'Disimpan ke wishlist' : 'Dihapus dari wishlist');
        // Halaman wishlist mendengarkan ini supaya daftarnya ikut diperbarui.
        $this->dispatch('wishlist-updated');
    }

    public function render(): View
    {
        return view('livewire.product-actions');
    }
}
