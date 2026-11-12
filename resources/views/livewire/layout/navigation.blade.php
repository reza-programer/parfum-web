<?php

use App\Livewire\Actions\Logout;
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
}; ?>

@php
    $links = [
        ['label' => 'Pria', 'url' => route('categories.show', 'pria'), 'active' => request()->is('kategori/pria')],
        ['label' => 'Wanita', 'url' => route('categories.show', 'wanita'), 'active' => request()->is('kategori/wanita')],
        ['label' => 'Unisex', 'url' => route('categories.show', 'unisex'), 'active' => request()->is('kategori/unisex')],
        ['label' => 'Brand', 'url' => route('brands.index'), 'active' => request()->is('brand*')],
    ];
    $icon = 'inline-flex size-11 items-center justify-center text-ink transition-opacity duration-150 hover:opacity-60';
@endphp

<nav x-data="{ search: false }" class="sticky top-0 z-30 border-b border-line bg-paper-card/95 backdrop-blur supports-[backdrop-filter]:bg-paper-card/85">
    <div class="page relative flex h-16 items-center gap-2 lg:h-[72px] lg:gap-8">
        {{-- Mobile: hamburger --}}
        <button type="button" x-on:click="$dispatch('open-drawer', 'menu')" class="{{ $icon }} -ml-3 lg:hidden" aria-label="Menu">
            <x-lucide-menu class="size-5" stroke-width="1.25" />
        </button>

        <a href="{{ route('home') }}" wire:navigate class="inline-flex h-11 shrink-0 items-center max-lg:absolute max-lg:left-1/2 max-lg:-translate-x-1/2" aria-label="{{ config('app.name') }}">
            <x-application-logo class="max-sm:text-lg" />
        </a>

        <div class="hidden items-center gap-7 lg:flex">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" wire:navigate
                   @class(['relative py-2 text-[13px] font-medium uppercase tracking-nav transition-colors duration-150',
                       'text-ink after:absolute after:inset-x-0 after:-bottom-px after:h-px after:bg-ink' => $link['active'],
                       'text-ink-2 hover:text-ink' => ! $link['active']])>{{ $link['label'] }}</a>
            @endforeach
        </div>

        <div class="ms-auto flex items-center lg:gap-2">
            <form action="{{ route('catalog') }}" method="GET" class="hidden lg:block" role="search">
                <label for="nav-search" class="sr-only">Cari parfum</label>
                <div class="relative">
                    <x-lucide-search class="pointer-events-none absolute left-0 top-1/2 size-4 -translate-y-1/2 text-ink-2" stroke-width="1.25" />
                    <input id="nav-search" name="q" type="search" value="{{ request('q') }}" placeholder="Cari nama atau brand"
                           class="h-10 w-56 border-0 border-b border-line bg-transparent pl-6 pr-0 text-[13px] text-ink placeholder:text-ink-faint focus:border-ink focus:ring-0"
                           autocomplete="off">
                </div>
            </form>

            <button type="button" x-on:click="search = ! search; $nextTick(() => $refs.mobileSearch?.focus())" class="{{ $icon }} lg:hidden" aria-label="Cari">
                <x-lucide-search class="size-5" stroke-width="1.25" />
            </button>

            @auth
                <a href="{{ route('wishlist') }}" wire:navigate class="{{ $icon }} max-sm:hidden" aria-label="Wishlist">
                    <x-lucide-heart class="size-5" stroke-width="1.25" />
                </a>
            @endauth

            <a href="{{ route('cart') }}" wire:navigate class="{{ $icon }} relative max-sm:-mr-3" aria-label="Keranjang">
                <x-lucide-shopping-bag class="size-5" stroke-width="1.25" />
                @if ($cartCount > 0)
                    <span class="absolute right-1.5 top-1.5 min-w-[17px] rounded-full bg-primary px-1 text-center text-[10px] font-medium leading-[17px] text-white tabular-nums" data-testid="cart-count">{{ $cartCount }}</span>
                @endif
            </a>

            @auth
                <div class="relative hidden sm:block" x-data="{ menu: false }" x-on:click.outside="menu = false" x-on:keydown.escape.window="menu = false">
                    <button type="button" x-on:click="menu = ! menu" class="inline-flex h-11 items-center gap-1.5 px-3 text-ink transition-opacity duration-150 hover:opacity-60 lg:-mr-3" aria-label="Akun" :aria-expanded="menu">
                        <x-lucide-user class="size-5" stroke-width="1.25" />
                        <x-lucide-chevron-down class="size-3.5" stroke-width="1.5" />
                    </button>
                    <div x-cloak x-show="menu" x-transition.opacity.duration.150ms
                         class="absolute right-0 mt-2 w-60 border border-line bg-paper-card py-2">
                        <p class="truncate px-4 pb-2 pt-1 text-label text-ink-2" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></p>
                        <div class="divider"></div>
                        @foreach ([
                            ['Pesanan saya', route('orders.index')],
                            ['Wishlist', route('wishlist')],
                            ['Alamat', route('addresses')],
                            ['Profil', route('profile')],
                        ] as [$label, $url])
                            <a href="{{ $url }}" wire:navigate class="flex h-10 items-center px-4 text-body text-ink-2 hover:bg-paper hover:text-ink">{{ $label }}</a>
                        @endforeach
                        @if (auth()->user()->isAdmin())
                            <a href="{{ url('/admin') }}" class="flex h-10 items-center px-4 text-body text-ink-2 hover:bg-paper hover:text-ink">Panel admin</a>
                        @endif
                        <div class="divider my-1"></div>
                        <button wire:click="logout" class="flex h-10 w-full items-center px-4 text-body text-ink-2 hover:bg-paper hover:text-ink">Keluar</button>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" wire:navigate class="ml-2 hidden h-10 items-center text-[13px] font-medium uppercase tracking-nav text-ink-2 hover:text-ink sm:inline-flex">Masuk</a>
                <a href="{{ route('register') }}" wire:navigate class="btn-primary btn-sm ml-4 hidden sm:inline-flex">Daftar</a>
            @endauth
        </div>
    </div>

    {{-- Search mobile/tablet --}}
    <div x-cloak x-show="search" x-transition.opacity.duration.150ms class="border-t border-line lg:hidden">
        <form action="{{ route('catalog') }}" method="GET" class="page py-3" role="search">
            <label for="nav-search-mobile" class="sr-only">Cari parfum</label>
            <input id="nav-search-mobile" x-ref="mobileSearch" name="q" type="search" value="{{ request('q') }}" placeholder="Cari nama atau brand" class="field">
        </form>
    </div>

    {{-- Menu mobile --}}
    {{-- Menu mobile: di-teleport ke <body> karena navbar memakai backdrop-filter (lihat x-ui.drawer). --}}
    @php
        $menuLink = 'flex h-12 items-center justify-between px-5 transition-colors duration-150 active:bg-paper';
    @endphp
    <x-ui.drawer name="menu" title="Menu" teleport class="lg:hidden">
        <div class="-mx-5">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" wire:navigate
                   @class([$menuLink, 'text-[13px] font-medium uppercase tracking-nav', 'text-ink' => $link['active'], 'text-ink-2' => ! $link['active']])>
                    {{ $link['label'] }}
                    <x-lucide-chevron-right class="size-4 text-ink-faint" stroke-width="1.25" />
                </a>
            @endforeach
            <a href="{{ route('catalog') }}" wire:navigate class="{{ $menuLink }} text-[13px] font-medium uppercase tracking-nav text-ink-2">
                Semua parfum
                <x-lucide-chevron-right class="size-4 text-ink-faint" stroke-width="1.25" />
            </a>
        </div>

        <form action="{{ route('catalog') }}" method="GET" class="mt-4" role="search">
            <label for="menu-search" class="sr-only">Cari parfum</label>
            <input id="menu-search" name="q" type="search" placeholder="Cari nama atau brand" class="field" autocomplete="off">
        </form>

        <div class="divider my-5"></div>
        <div class="-mx-5">
            @auth
                @foreach ([
                    ['Pesanan saya', route('orders.index')],
                    ['Wishlist', route('wishlist')],
                    ['Alamat', route('addresses')],
                    ['Profil', route('profile')],
                ] as [$label, $url])
                    <a href="{{ $url }}" wire:navigate class="{{ $menuLink }} text-body text-ink-2">{{ $label }}</a>
                @endforeach
                <button type="button" wire:click="logout" class="{{ $menuLink }} w-full text-body text-ink-2">Keluar</button>
            @else
                <a href="{{ route('login') }}" wire:navigate class="{{ $menuLink }} text-body text-ink-2">Masuk</a>
                <a href="{{ route('register') }}" wire:navigate class="{{ $menuLink }} text-body text-ink-2">Daftar</a>
            @endauth
        </div>
    </x-ui.drawer>
</nav>
