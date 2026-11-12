<nav class="flex gap-7 overflow-x-auto border-b border-line [scrollbar-width:none]" aria-label="Akun">
    @foreach ([
        ['orders.index', 'Pesanan', 'pesanan*'],
        ['wishlist', 'Wishlist', 'wishlist'],
        ['addresses', 'Alamat', 'akun/alamat'],
        ['profile', 'Profil', 'profile'],
    ] as [$route, $label, $pattern])
        <a href="{{ route($route) }}" wire:navigate
           @class([
               '-mb-px inline-flex h-11 items-center whitespace-nowrap border-b text-[13px] font-medium uppercase tracking-nav transition-colors duration-150',
               'border-ink text-ink' => request()->is($pattern),
               'border-transparent text-ink-2 hover:text-ink' => ! request()->is($pattern),
           ])>{{ $label }}</a>
    @endforeach
</nav>
