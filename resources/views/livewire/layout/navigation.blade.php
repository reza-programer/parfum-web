<?php

use App\Enums\ScentFamily;
use App\Livewire\Actions\Logout;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductVariant;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public int $cartCount = 0;

    public function mount(): void
    {
        $this->refreshCart();
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        $this->cartCount = (int) (auth()->user()?->cartItems()->sum('qty') ?? 0);
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function with(): array
    {
        return [
            'categories' => Category::orderBy('id')->get(['id', 'name', 'slug']),
            'brands' => Brand::withCount(['products' => fn ($q) => $q->where('is_active', true)])->orderBy('name')->get(['id', 'name', 'slug']),
            'sizes' => ProductVariant::where('is_decant', false)->distinct()->orderBy('size_ml')->pluck('size_ml'),
            'scents' => ScentFamily::cases(),
        ];
    }
}; ?>

@php
    // Mega menu (docs/plans/redesign-nb.md): tiap kategori punya kolom aroma, ukuran, dan brand yang tersaring ke kategori itu.
    $menus = $categories->map(fn ($category) => [
        'key' => $category->slug,
        'label' => $category->name,
        'url' => route('categories.show', $category),
        'active' => request()->is('kategori/'.$category->slug),
        'filter' => fn (array $query) => route('categories.show', ['category' => $category->slug] + $query),
        'all' => 'Semua parfum '.mb_strtolower($category->name),
        'image' => 'images/tiles/kategori-'.$category->slug.'.webp',
    ])->push([
        'key' => 'decant',
        'label' => 'Decant',
        'url' => route('catalog', ['ukuran' => ['decant']]),
        'active' => request()->routeIs('catalog') && in_array('decant', (array) request()->query('ukuran', []), true),
        'filter' => fn (array $query) => route('catalog', ['ukuran' => ['decant']] + $query),
        'all' => 'Semua decant',
        'image' => 'images/tiles/kategori-decant.webp',
    ]);
    $accountLinks = [
        ['Pesanan saya', route('orders.index')],
        ['Wishlist', route('wishlist')],
        ['Alamat', route('addresses')],
        ['Profil', route('profile')],
    ];
    $topLink = 'flex h-full items-center border-b-2 px-3 text-[13px] font-bold uppercase tracking-nav transition-colors duration-150';
    $megaHead = 'mb-3 text-label font-bold uppercase tracking-nav text-ink';
    $megaLink = 'block py-1 text-body text-ink-2 transition-colors duration-150 hover:text-ink hover:underline hover:underline-offset-4';
    $icon = 'relative inline-flex size-11 items-center justify-center text-ink transition-opacity duration-150 hover:opacity-60';
@endphp

<nav x-data="{ mega: null }" x-on:mouseleave="mega = null" x-on:keydown.escape.window="mega = null" x-on:livewire:navigating.window="mega = null"
     class="sticky top-0 z-30 border-b border-line bg-paper-card">
    <div class="page flex h-14 items-center gap-1 md:h-16 md:gap-4">
        <button type="button" x-on:click="$dispatch('open-drawer', 'menu')" class="{{ $icon }} -ml-3 lg:hidden" aria-label="Menu">
            <x-lucide-menu class="size-6" stroke-width="1.5" />
        </button>

        <a href="{{ route('home') }}" wire:navigate class="inline-flex h-11 shrink-0 items-center lg:mr-4" aria-label="{{ config('app.name') }}" x-on:mouseenter="mega = null">
            <x-application-logo class="max-sm:text-lg" />
        </a>

        {{-- Menu utama desktop dengan mega menu --}}
        <ul class="hidden h-16 items-stretch lg:flex" role="list">
            <li class="flex">
                <a href="{{ route('catalog') }}" wire:navigate x-on:mouseenter="mega = null" x-on:focus="mega = null"
                   @class([$topLink, 'border-ink text-ink' => request()->routeIs('catalog') && ! request()->query('ukuran'), 'border-transparent text-ink hover:border-ink' => ! (request()->routeIs('catalog') && ! request()->query('ukuran'))])>Baru</a>
            </li>
            @foreach ($menus as $menu)
                <li class="flex">
                    <a href="{{ $menu['url'] }}" wire:navigate x-on:mouseenter="mega = '{{ $menu['key'] }}'" x-on:focus="mega = '{{ $menu['key'] }}'"
                       :aria-expanded="mega === '{{ $menu['key'] }}'"
                       class="{{ $topLink }} {{ $menu['active'] ? 'border-ink' : 'border-transparent' }} text-ink"
                       :class="mega === '{{ $menu['key'] }}' && '!border-ink'">{{ $menu['label'] }}</a>
                </li>
            @endforeach
            <li class="flex">
                <a href="{{ route('brands.index') }}" wire:navigate x-on:mouseenter="mega = 'brand'" x-on:focus="mega = 'brand'"
                   :aria-expanded="mega === 'brand'"
                   class="{{ $topLink }} {{ request()->is('brand*') ? 'border-ink' : 'border-transparent' }} text-ink"
                   :class="mega === 'brand' && '!border-ink'">Brand</a>
            </li>
        </ul>

        {{-- Kolom cari bergaris (tablet dan desktop) --}}
        <form action="{{ route('catalog') }}" method="GET" role="search" class="hidden min-w-0 flex-1 md:block lg:ms-auto lg:max-w-xs" x-on:mouseenter="mega = null">
            <label for="nav-search" class="sr-only">Cari parfum</label>
            <div class="flex h-10 border border-line bg-paper-card transition-colors duration-150 focus-within:border-ink">
                <input id="nav-search" name="q" type="search" value="{{ request('q') }}" placeholder="Cari..."
                       class="h-full min-w-0 flex-1 border-0 bg-transparent px-3 text-body text-ink placeholder:text-ink-2 focus:ring-0"
                       autocomplete="off">
                <button type="submit" class="inline-flex w-11 shrink-0 items-center justify-center text-ink hover:text-primary" aria-label="Cari">
                    <x-lucide-search class="size-5" stroke-width="1.75" />
                </button>
            </div>
        </form>

        <div class="ms-auto flex items-center md:ms-0" x-on:mouseenter="mega = null">
            @auth
                <div class="relative hidden md:block" x-data="{ menu: false }" x-on:click.outside="menu = false" x-on:keydown.escape.window="menu = false">
                    <button type="button" x-on:click="menu = ! menu" class="inline-flex h-11 items-center gap-2 px-2 text-body text-ink hover:opacity-70" aria-label="Akun" :aria-expanded="menu">
                        <x-lucide-circle-user-round class="size-6" stroke-width="1.5" />
                        <span class="hidden max-w-[8rem] truncate lg:block" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name">{{ auth()->user()->name }}</span>
                    </button>
                    <div x-cloak x-show="menu" x-transition.opacity.duration.150ms
                         class="absolute right-0 mt-1 w-56 border border-line bg-paper-card py-2 shadow-float">
                        @foreach ($accountLinks as [$label, $url])
                            <a href="{{ $url }}" wire:navigate class="flex h-10 items-center px-4 text-body text-ink hover:bg-paper-raised">{{ $label }}</a>
                        @endforeach
                        @if (auth()->user()->isAdmin())
                            <a href="{{ url('/admin') }}" class="flex h-10 items-center px-4 text-body text-ink hover:bg-paper-raised">Panel admin</a>
                        @endif
                        <div class="divider my-1.5"></div>
                        <button wire:click="logout" class="flex h-10 w-full items-center px-4 text-body text-ink hover:bg-paper-raised">Keluar</button>
                    </div>
                </div>
                <a href="{{ route('wishlist') }}" wire:navigate class="{{ $icon }}" aria-label="Wishlist">
                    <x-lucide-heart class="size-[22px]" stroke-width="1.5" />
                </a>
            @else
                <div class="hidden items-center gap-1.5 pr-2 text-body text-ink md:flex">
                    <x-lucide-circle-user-round class="size-6" stroke-width="1.5" />
                    <a href="{{ route('login') }}" wire:navigate class="hover:underline hover:underline-offset-4">Masuk</a>
                    <span class="text-ink-faint" aria-hidden="true">|</span>
                    <a href="{{ route('register') }}" wire:navigate class="hover:underline hover:underline-offset-4">Daftar</a>
                </div>
            @endauth

            <a href="{{ route('cart') }}" wire:navigate class="{{ $icon }} -mr-3" aria-label="Keranjang">
                <x-lucide-shopping-bag class="size-[22px]" stroke-width="1.5" />
                @if ($cartCount > 0)
                    <span class="absolute right-1 top-1 min-w-[18px] rounded-full bg-primary px-1 text-center text-[10px] font-bold leading-[18px] text-white tabular-nums" data-testid="cart-count">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- Kolom cari HP: baris kedua --}}
    <div class="page flex h-[52px] items-start md:hidden">
        <form action="{{ route('catalog') }}" method="GET" role="search" class="w-full">
            <label for="nav-search-mobile" class="sr-only">Cari parfum</label>
            <div class="flex h-10 border border-line focus-within:border-ink">
                <input id="nav-search-mobile" name="q" type="search" value="{{ request('q') }}" placeholder="Cari..."
                       class="h-full min-w-0 flex-1 border-0 bg-transparent px-3 text-body text-ink placeholder:text-ink-2 focus:ring-0" autocomplete="off">
                <button type="submit" class="inline-flex w-11 shrink-0 items-center justify-center text-ink" aria-label="Cari">
                    <x-lucide-search class="size-5" stroke-width="1.75" />
                </button>
            </div>
        </form>
    </div>

    {{-- Panel mega menu (desktop) --}}
    @foreach ($menus as $menu)
        <div x-cloak x-show="mega === '{{ $menu['key'] }}'" x-transition.opacity.duration.150ms
             class="absolute inset-x-0 top-full hidden border-b border-t border-line bg-paper-card shadow-float lg:block" data-mega="{{ $menu['key'] }}">
            <div class="page grid grid-cols-[1fr_1fr_1fr_minmax(0,1.3fr)] gap-10 py-8">
                <div>
                    <p class="{{ $megaHead }}">Keluarga aroma</p>
                    @foreach ($scents as $scent)
                        <a href="{{ ($menu['filter'])(['aroma' => [$scent->value]]) }}" wire:navigate class="{{ $megaLink }}">{{ $scent->label() }}</a>
                    @endforeach
                </div>
                <div>
                    <p class="{{ $megaHead }}">Ukuran</p>
                    @if ($menu['key'] !== 'decant')
                        <a href="{{ ($menu['filter'])(['ukuran' => ['decant']]) }}" wire:navigate class="{{ $megaLink }}">Decant</a>
                        @foreach ($sizes as $size)
                            <a href="{{ ($menu['filter'])(['ukuran' => [(string) $size]]) }}" wire:navigate class="{{ $megaLink }}">{{ $size }}ml</a>
                        @endforeach
                    @else
                        <p class="text-body leading-6 text-ink-2">Botol kecil untuk mencoba aroma sebelum membeli ukuran penuh.</p>
                    @endif
                </div>
                <div>
                    <p class="{{ $megaHead }}">Brand</p>
                    @foreach ($brands as $brand)
                        <a href="{{ ($menu['filter'])(['brand' => [$brand->slug]]) }}" wire:navigate class="{{ $megaLink }}">{{ $brand->name }}</a>
                    @endforeach
                </div>
                <a href="{{ $menu['url'] }}" wire:navigate class="group block">
                    <span class="block aspect-[16/10] overflow-hidden bg-paper-raised">
                        <img src="{{ asset($menu['image']) }}" alt="" class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]" loading="lazy" decoding="async">
                    </span>
                    <span class="mt-3 inline-flex items-center gap-1 text-label font-bold uppercase tracking-nav text-ink">{{ $menu['all'] }} <x-lucide-chevron-right class="size-4" stroke-width="2" /></span>
                </a>
            </div>
        </div>
    @endforeach
    <div x-cloak x-show="mega === 'brand'" x-transition.opacity.duration.150ms
         class="absolute inset-x-0 top-full hidden border-b border-t border-line bg-paper-card shadow-float lg:block" data-mega="brand">
        <div class="page py-8">
            <p class="{{ $megaHead }}">Brand</p>
            <div class="grid grid-cols-4 gap-x-10">
                @foreach ($brands as $brand)
                    <a href="{{ route('brands.show', $brand) }}" wire:navigate class="{{ $megaLink }}">{{ $brand->name }} <span class="text-ink-faint">({{ $brand->products_count }})</span></a>
                @endforeach
            </div>
            <a href="{{ route('brands.index') }}" wire:navigate class="mt-5 inline-flex items-center gap-1 text-label font-bold uppercase tracking-nav text-ink">Semua brand <x-lucide-chevron-right class="size-4" stroke-width="2" /></a>
        </div>
    </div>

    {{-- Menu HP: berlapis (kategori → aroma, ukuran, brand). Di-teleport ke <body> supaya position: fixed tidak terkurung navbar sticky. --}}
    @php
        $menuRow = 'flex h-12 w-full items-center justify-between px-5 text-[13px] font-bold uppercase tracking-nav text-ink transition-colors duration-150 active:bg-paper-raised';
        $subLink = 'block py-2 pl-5 text-body text-ink-2 active:text-ink';
    @endphp
    <x-ui.drawer name="menu" title="Menu" teleport class="lg:hidden">
        <div class="-mx-5 -mt-5 divide-y divide-line border-b border-line" x-data="{ sub: null }">
            <a href="{{ route('catalog') }}" wire:navigate class="{{ $menuRow }}">Baru <x-lucide-chevron-right class="size-4" stroke-width="1.75" /></a>
            @foreach ($menus as $menu)
                <div>
                    <button type="button" x-on:click="sub = sub === '{{ $menu['key'] }}' ? null : '{{ $menu['key'] }}'" class="{{ $menuRow }}" :aria-expanded="sub === '{{ $menu['key'] }}'">
                        {{ $menu['label'] }}
                        <x-lucide-chevron-down class="size-4 transition-transform duration-200" x-bind:class="sub === '{{ $menu['key'] }}' ? 'rotate-180' : ''" stroke-width="1.75" />
                    </button>
                    <div x-show="sub === '{{ $menu['key'] }}'" x-collapse class="px-5 pb-4">
                        <a href="{{ $menu['url'] }}" wire:navigate class="block py-2 text-body font-bold text-ink">{{ $menu['all'] }}</a>
                        <p class="eyebrow mt-2">Keluarga aroma</p>
                        @foreach ($scents as $scent)
                            <a href="{{ ($menu['filter'])(['aroma' => [$scent->value]]) }}" wire:navigate class="{{ $subLink }}">{{ $scent->label() }}</a>
                        @endforeach
                        @if ($menu['key'] !== 'decant')
                            <p class="eyebrow mt-3">Ukuran</p>
                            <a href="{{ ($menu['filter'])(['ukuran' => ['decant']]) }}" wire:navigate class="{{ $subLink }}">Decant</a>
                            @foreach ($sizes as $size)
                                <a href="{{ ($menu['filter'])(['ukuran' => [(string) $size]]) }}" wire:navigate class="{{ $subLink }}">{{ $size }}ml</a>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
            <a href="{{ route('brands.index') }}" wire:navigate class="{{ $menuRow }}">Brand <x-lucide-chevron-right class="size-4" stroke-width="1.75" /></a>
            <a href="{{ route('catalog') }}" wire:navigate class="{{ $menuRow }}">Semua parfum <x-lucide-chevron-right class="size-4" stroke-width="1.75" /></a>
        </div>

        <div class="mt-5">
            @auth
                <p class="eyebrow mb-1">Halo, {{ auth()->user()->name }}</p>
                <div class="-mx-5">
                    @foreach ($accountLinks as [$label, $url])
                        <a href="{{ $url }}" wire:navigate class="flex h-11 items-center px-5 text-body text-ink active:bg-paper-raised">{{ $label }}</a>
                    @endforeach
                    @if (auth()->user()->isAdmin())
                        <a href="{{ url('/admin') }}" class="flex h-11 items-center px-5 text-body text-ink active:bg-paper-raised">Panel admin</a>
                    @endif
                    <button type="button" wire:click="logout" class="flex h-11 w-full items-center px-5 text-body text-ink active:bg-paper-raised">Keluar</button>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" wire:navigate class="btn-secondary">Masuk</a>
                    <a href="{{ route('register') }}" wire:navigate class="btn-primary">Daftar</a>
                </div>
            @endauth
        </div>
    </x-ui.drawer>
</nav>
