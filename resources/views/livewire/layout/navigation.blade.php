<?php

use App\Enums\ScentFamily;
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
    $onCatalog = request()->routeIs('catalog');
    $sizes = (array) request()->query('ukuran', []);
    $aromas = (array) request()->query('aroma', []);
    $links = [
        ['label' => 'Pria', 'url' => route('categories.show', 'pria'), 'active' => request()->is('kategori/pria')],
        ['label' => 'Wanita', 'url' => route('categories.show', 'wanita'), 'active' => request()->is('kategori/wanita')],
        ['label' => 'Unisex', 'url' => route('categories.show', 'unisex'), 'active' => request()->is('kategori/unisex')],
        ['label' => 'Decant', 'url' => route('catalog', ['ukuran' => ['decant']]), 'active' => $onCatalog && in_array('decant', $sizes, true)],
        ['label' => 'Brand', 'url' => route('brands.index'), 'active' => request()->is('brand*')],
    ];
    $scentLinks = collect(ScentFamily::cases())->map(fn (ScentFamily $s) => [
        'label' => $s->label(),
        'url' => route('catalog', ['aroma' => [$s->value]]),
        'active' => $onCatalog && in_array($s->value, $aromas, true),
    ]);
    $accountLinks = [
        ['Pesanan saya', route('orders.index'), 'package'],
        ['Wishlist', route('wishlist'), 'heart'],
        ['Alamat', route('addresses'), 'map-pin'],
        ['Profil', route('profile'), 'user'],
    ];
    $icon = 'relative inline-flex size-11 items-center justify-center rounded-md text-ink transition-colors duration-150 hover:bg-paper hover:text-primary';
@endphp

<nav class="sticky top-0 z-30 border-b border-line bg-paper-card">
    <div class="page flex h-14 items-center gap-1 md:h-16 md:gap-4 lg:gap-6">
        <button type="button" x-on:click="$dispatch('open-drawer', 'menu')" class="{{ $icon }} -ml-2 lg:hidden" aria-label="Menu">
            <x-lucide-menu class="size-[22px]" stroke-width="1.75" />
        </button>

        <a href="{{ route('home') }}" wire:navigate class="inline-flex h-11 shrink-0 items-center" aria-label="{{ config('app.name') }}">
            <x-application-logo class="max-sm:text-lg" />
        </a>

        {{-- Kolom cari besar (tablet dan desktop) --}}
        <form action="{{ route('catalog') }}" method="GET" role="search" class="hidden min-w-0 flex-1 md:block">
            <label for="nav-search" class="sr-only">Cari parfum</label>
            <div class="flex h-10 overflow-hidden rounded-md border border-line bg-paper-card transition-colors duration-150 focus-within:border-primary">
                <input id="nav-search" name="q" type="search" value="{{ request('q') }}" placeholder="Cari nama parfum atau brand"
                       class="h-full min-w-0 flex-1 border-0 bg-transparent px-3.5 text-body text-ink placeholder:text-ink-faint focus:ring-0"
                       autocomplete="off">
                <button type="submit" class="inline-flex w-14 shrink-0 items-center justify-center bg-primary text-white transition-colors duration-150 hover:bg-primary-dark" aria-label="Cari">
                    <x-lucide-search class="size-[18px]" stroke-width="2" />
                </button>
            </div>
        </form>

        <div class="ms-auto flex items-center gap-0.5 md:ms-0">
            @auth
                <a href="{{ route('wishlist') }}" wire:navigate class="{{ $icon }}" aria-label="Wishlist">
                    <x-lucide-heart class="size-[22px]" stroke-width="1.75" />
                </a>
            @endauth

            <a href="{{ route('cart') }}" wire:navigate class="{{ $icon }} max-md:-mr-2" aria-label="Keranjang">
                <x-lucide-shopping-cart class="size-[22px]" stroke-width="1.75" />
                @if ($cartCount > 0)
                    <span class="absolute right-0.5 top-0.5 min-w-[18px] rounded-full border-2 border-paper-card bg-primary px-1 text-center text-[10px] font-bold leading-[14px] text-white tabular-nums" data-testid="cart-count">{{ $cartCount }}</span>
                @endif
            </a>

            @auth
                <div class="relative ml-2 hidden border-l border-line pl-3 md:block" x-data="{ menu: false }" x-on:click.outside="menu = false" x-on:keydown.escape.window="menu = false">
                    <button type="button" x-on:click="menu = ! menu" class="inline-flex h-11 items-center gap-2 rounded-md px-2 text-ink transition-colors duration-150 hover:bg-paper" aria-label="Akun" :aria-expanded="menu">
                        <span class="inline-flex size-7 items-center justify-center rounded-full bg-primary-soft text-label font-bold uppercase text-primary">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                        <span class="hidden max-w-[8rem] truncate text-body font-medium lg:block" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name">{{ auth()->user()->name }}</span>
                        <x-lucide-chevron-down class="size-4 text-ink-2" stroke-width="1.75" />
                    </button>
                    <div x-cloak x-show="menu" x-transition.opacity.duration.150ms
                         class="absolute right-0 mt-2 w-56 overflow-hidden rounded-lg border border-line bg-paper-card py-1.5 shadow-float">
                        @foreach ($accountLinks as [$label, $url, $glyph])
                            <a href="{{ $url }}" wire:navigate class="flex h-10 items-center gap-3 px-4 text-body text-ink hover:bg-paper">
                                <x-dynamic-component :component="'lucide-'.$glyph" class="size-4 text-ink-2" stroke-width="1.75" />
                                {{ $label }}
                            </a>
                        @endforeach
                        @if (auth()->user()->isAdmin())
                            <a href="{{ url('/admin') }}" class="flex h-10 items-center gap-3 px-4 text-body text-ink hover:bg-paper">
                                <x-lucide-layout-dashboard class="size-4 text-ink-2" stroke-width="1.75" />
                                Panel admin
                            </a>
                        @endif
                        <div class="divider my-1.5"></div>
                        <button wire:click="logout" class="flex h-10 w-full items-center gap-3 px-4 text-body text-ink hover:bg-paper">
                            <x-lucide-log-out class="size-4 text-ink-2" stroke-width="1.75" />
                            Keluar
                        </button>
                    </div>
                </div>
            @else
                <div class="ml-2 hidden items-center gap-2 border-l border-line pl-4 md:flex">
                    <a href="{{ route('login') }}" wire:navigate class="btn-secondary btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" wire:navigate class="btn-primary btn-sm">Daftar</a>
                </div>
            @endauth
        </div>
    </div>

    {{-- Kolom cari HP: selalu terlihat di baris kedua --}}
    <div class="page flex h-[52px] items-start md:hidden">
        <form action="{{ route('catalog') }}" method="GET" role="search" class="w-full">
            <label for="nav-search-mobile" class="sr-only">Cari parfum</label>
            <div class="relative">
                <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-2" stroke-width="2" />
                <input id="nav-search-mobile" name="q" type="search" value="{{ request('q') }}" placeholder="Cari nama parfum atau brand"
                       class="h-10 w-full rounded-md border-line bg-paper pl-9 pr-3 text-body text-ink placeholder:text-ink-2/70 focus:border-primary focus:bg-paper-card focus:ring-0"
                       autocomplete="off">
            </div>
        </form>
    </div>

    {{-- Baris kategori (desktop) --}}
    <div class="hidden lg:block">
        <div class="page flex h-10 items-center gap-6 overflow-x-auto text-[13px] no-scrollbar">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" wire:navigate
                   @class(['shrink-0 font-medium transition-colors duration-150',
                       'text-primary' => $link['active'],
                       'text-ink hover:text-primary' => ! $link['active']])>{{ $link['label'] }}</a>
            @endforeach
            <span class="h-4 w-px shrink-0 bg-line" aria-hidden="true"></span>
            @foreach ($scentLinks as $link)
                <a href="{{ $link['url'] }}" wire:navigate
                   @class(['shrink-0 transition-colors duration-150',
                       'font-medium text-primary' => $link['active'],
                       'text-ink-2 hover:text-primary' => ! $link['active']])>{{ $link['label'] }}</a>
            @endforeach
            <a href="{{ route('catalog') }}" wire:navigate class="ms-auto shrink-0 text-ink-2 hover:text-primary">Semua parfum</a>
        </div>
    </div>

    {{-- Menu mobile: di-teleport ke <body> supaya position: fixed tidak terkurung navbar sticky (lihat x-ui.drawer). --}}
    @php
        $menuLink = 'flex h-12 items-center justify-between px-5 text-body transition-colors duration-150 active:bg-paper';
    @endphp
    <x-ui.drawer name="menu" title="Menu" teleport class="lg:hidden">
        @auth
            <div class="-mx-5 -mt-5 mb-4 flex items-center gap-3 bg-paper px-5 py-4">
                <span class="inline-flex size-10 items-center justify-center rounded-full bg-primary-soft text-body font-bold uppercase text-primary">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                <span class="min-w-0">
                    <span class="block truncate text-body font-semibold text-ink">{{ auth()->user()->name }}</span>
                    <span class="block truncate text-label text-ink-2">{{ auth()->user()->email }}</span>
                </span>
            </div>
        @else
            <div class="mb-5 grid grid-cols-2 gap-2">
                <a href="{{ route('login') }}" wire:navigate class="btn-secondary">Masuk</a>
                <a href="{{ route('register') }}" wire:navigate class="btn-primary">Daftar</a>
            </div>
        @endauth

        <p class="eyebrow mb-1">Kategori</p>
        <div class="-mx-5">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" wire:navigate
                   @class([$menuLink, 'font-medium', 'text-primary' => $link['active'], 'text-ink' => ! $link['active']])>
                    {{ $link['label'] }}
                    <x-lucide-chevron-right class="size-4 text-ink-faint" stroke-width="1.75" />
                </a>
            @endforeach
            <a href="{{ route('catalog') }}" wire:navigate class="{{ $menuLink }} font-medium text-ink">
                Semua parfum
                <x-lucide-chevron-right class="size-4 text-ink-faint" stroke-width="1.75" />
            </a>
        </div>

        <p class="eyebrow mb-3 mt-5">Keluarga aroma</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($scentLinks as $link)
                <a href="{{ $link['url'] }}" wire:navigate @class(['chip', 'chip-active' => $link['active']])>{{ $link['label'] }}</a>
            @endforeach
        </div>

        @auth
            <div class="divider my-5"></div>
            <div class="-mx-5">
                @foreach ($accountLinks as [$label, $url, $glyph])
                    <a href="{{ $url }}" wire:navigate class="{{ $menuLink }} justify-start gap-3 text-ink">
                        <x-dynamic-component :component="'lucide-'.$glyph" class="size-[18px] text-ink-2" stroke-width="1.75" />
                        {{ $label }}
                    </a>
                @endforeach
                @if (auth()->user()->isAdmin())
                    <a href="{{ url('/admin') }}" class="{{ $menuLink }} justify-start gap-3 text-ink">
                        <x-lucide-layout-dashboard class="size-[18px] text-ink-2" stroke-width="1.75" />
                        Panel admin
                    </a>
                @endif
                <button type="button" wire:click="logout" class="{{ $menuLink }} w-full justify-start gap-3 text-ink">
                    <x-lucide-log-out class="size-[18px] text-ink-2" stroke-width="1.75" />
                    Keluar
                </button>
            </div>
        @endauth
    </x-ui.drawer>
</nav>
