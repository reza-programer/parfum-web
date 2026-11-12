<?php

namespace App\Livewire;

use App\Exceptions\CartException;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class Wishlist extends Component
{
    /**
     * Hati di kartu ditekan dari halaman ini: render ulang daftarnya.
     */
    #[On('wishlist-updated')]
    public function refreshList(): void {}

    public function remove(int $id): void
    {
        if (auth()->user()->wishlistItems()->whereKey($id)->delete()) {
            $this->dispatch('toast', message: 'Dihapus dari wishlist');
        }
    }

    public function moveToCart(int $id, CartService $cart): void
    {
        $wish = auth()->user()->wishlistItems()->with('product.variants')->find($id);

        if (! $wish) {
            return;
        }

        $variant = $wish->product->is_active
            ? $wish->product->variants->where('stock', '>', 0)->sortBy('price')->first()
            : null;

        if (! $variant) {
            $this->addError("wish.{$id}", 'Semua ukuran sedang habis.');

            return;
        }

        try {
            $cart->add(auth()->user(), $variant, 1);
        } catch (CartException $e) {
            $this->addError("wish.{$id}", $e->getMessage());

            return;
        }

        $wish->delete();
        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: "{$wish->product->name} {$variant->label()} dipindah ke keranjang");
    }

    public function render(): View
    {
        $items = auth()->user()->wishlistItems()
            ->with(['product' => fn ($q) => $q
                ->with(['brand:id,name', 'variants:id,product_id,size_ml,is_decant,price,stock'])
                ->withMin('variants as min_price', 'price')
                ->withSum('variants as total_stock', 'stock')])
            ->latest()
            ->get();

        return view('livewire.wishlist', ['items' => $items])->title('Wishlist');
    }
}
