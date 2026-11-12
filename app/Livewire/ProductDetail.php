<?php

namespace App\Livewire;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Queries\CatalogQuery;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProductDetail extends Component
{
    public Product $product;

    public ?int $variantId = null;

    public int $qty = 1;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['brand', 'category', 'variants']);

        $this->variantId = $this->product->variants->firstWhere('stock', '>', 0)?->id
            ?? $this->product->variants->first()?->id;
    }

    #[Computed]
    public function variant(): ?ProductVariant
    {
        return $this->product->variants->firstWhere('id', $this->variantId);
    }

    public function selectVariant(int $id): void
    {
        if ($this->product->variants->contains('id', $id)) {
            $this->variantId = $id;
            $this->qty = 1;
            unset($this->variant);
        }
    }

    public function increment(): void
    {
        $this->qty = min($this->qty + 1, max(1, $this->variant?->stock ?? 1));
    }

    public function decrement(): void
    {
        $this->qty = max(1, $this->qty - 1);
    }

    public function addToCart(CartService $cart): void
    {
        if (! $this->putInCart($cart)) {
            return;
        }

        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: "{$this->product->name} {$this->variant->label()} masuk keranjang");
    }

    /**
     * Masukkan ke keranjang lalu langsung ke checkout.
     */
    public function buyNow(CartService $cart): void
    {
        if ($this->putInCart($cart)) {
            $this->redirectRoute('checkout', navigate: true);
        }
    }

    private function putInCart(CartService $cart): bool
    {
        if (! auth()->check()) {
            session()->put('url.intended', route('products.show', $this->product));
            $this->redirectRoute('login', navigate: true);

            return false;
        }

        if (! $this->variant) {
            return false;
        }

        try {
            $cart->add(auth()->user(), $this->variant, $this->qty);
        } catch (CartException $e) {
            $this->addError('qty', $e->getMessage());

            return false;
        }

        $this->resetErrorBag();

        return true;
    }

    public function toggleWishlist(WishlistService $wishlist): void
    {
        if (! auth()->check()) {
            session()->put('url.intended', route('products.show', $this->product));
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $wishlist->toggle(auth()->user(), $this->product);
        unset($this->inWishlist);
    }

    #[Computed]
    public function inWishlist(): bool
    {
        return (bool) auth()->user()?->wishlistItems()->where('product_id', $this->product->id)->exists();
    }

    public function render(): View
    {
        $reviews = $this->product->visibleReviews()->with('user:id,name')->limit(20)->get();

        return view('livewire.product-detail', [
            'reviews' => $reviews,
            'rating' => $reviews->isEmpty() ? null : round($this->product->visibleReviews()->avg('rating'), 1),
            'reviewCount' => $this->product->visibleReviews()->count(),
            'soldOut' => $this->product->isSoldOut(),
            'sold' => $this->product->soldCount(),
            'brandProductCount' => $this->product->brand->products()->where('is_active', true)->count(),
            // Aroma yang sama didahulukan, lalu kategori yang sama. Lewat CatalogQuery supaya kartu punya label, rating, dan terjual.
            'related' => (new CatalogQuery)->builder()
                ->reorder()
                ->where('products.id', '!=', $this->product->id)
                ->where('is_active', true)
                ->where(fn ($q) => $q
                    ->where('scent_family', $this->product->scent_family)
                    ->orWhere('category_id', $this->product->category_id))
                ->orderByRaw('scent_family = ? desc', [$this->product->scent_family->value])
                ->orderByDesc('products.created_at')
                ->limit(5)
                ->get(),
        ])->title($this->product->name);
    }
}
