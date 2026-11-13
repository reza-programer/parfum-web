<?php

use App\Livewire\ProductActions;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create(['name' => 'Malam Rempah']);
    $this->decant = ProductVariant::factory()->for($this->product)->create(['size_ml' => 10, 'is_decant' => true, 'price' => 79000, 'stock' => 5]);
    $this->soldOut = ProductVariant::factory()->for($this->product)->create(['size_ml' => 50, 'is_decant' => false, 'price' => 389000, 'stock' => 0]);
    $this->bottle = ProductVariant::factory()->for($this->product)->create(['size_ml' => 100, 'is_decant' => false, 'price' => 589000, 'stock' => 2]);
});

function actions(?User $user = null)
{
    $product = test()->product->load('variants');

    return $user
        ? Livewire::actingAs($user)->test(ProductActions::class, ['product' => $product])
        : Livewire::test(ProductActions::class, ['product' => $product]);
}

it('tamu yang menekan wishlist diarahkan ke login', function () {
    actions()->call('toggleWishlist')->assertRedirect(route('login'));
});

it('customer menyimpan lalu menghapus wishlist dari kartu', function () {
    $card = actions($this->user)
        ->call('toggleWishlist')
        ->assertSet('wished', true)
        ->assertDispatched('toast', message: 'Disimpan ke wishlist')
        ->assertDispatched('wishlist-updated');

    expect($this->user->wishlistItems()->count())->toBe(1);

    $card->call('toggleWishlist')
        ->assertSet('wished', false)
        ->assertDispatched('toast', message: 'Dihapus dari wishlist');

    expect($this->user->wishlistItems()->count())->toBe(0);
});

it('kartu tidak lagi punya tambah cepat (gaya NB: kartu polos + wishlist)', function () {
    actions()
        ->assertDontSeeHtml('data-variant')
        ->assertDontSee('Tambah cepat');

    expect(method_exists(ProductActions::class, 'add'))->toBeFalse();
});
