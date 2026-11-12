{{-- Navigasi akun: tab berbentuk pil di dalam kartu, bisa digeser di HP. --}}
<nav class="card no-scrollbar flex gap-1 overflow-x-auto p-1.5" aria-label="Akun">
    @foreach ([
        ['orders.index', 'Pesanan', 'pesanan*', 'package'],
        ['wishlist', 'Wishlist', 'wishlist', 'heart'],
        ['addresses', 'Alamat', 'akun/alamat', 'map-pin'],
        ['profile', 'Profil', 'profile', 'user'],
    ] as [$route, $label, $pattern, $glyph])
        <a href="{{ route($route) }}" wire:navigate
           @class([
               'inline-flex h-10 shrink-0 items-center gap-2 rounded-md px-3.5 text-body font-medium transition-colors duration-150',
               'bg-primary-soft text-primary' => request()->is($pattern),
               'text-ink-2 hover:bg-paper hover:text-ink' => ! request()->is($pattern),
           ])
           @if (request()->is($pattern)) aria-current="page" @endif>
            <x-dynamic-component :component="'lucide-'.$glyph" class="size-4" stroke-width="2" />
            {{ $label }}
        </a>
    @endforeach
</nav>
