{{-- Navigasi akun gaya NB: tab kapital bergaris bawah, bisa digeser di HP. --}}
<nav class="no-scrollbar flex gap-7 overflow-x-auto border-b border-line" aria-label="Akun">
    @foreach ([
        ['orders.index', 'Pesanan', 'pesanan*'],
        ['wishlist', 'Wishlist', 'wishlist'],
        ['addresses', 'Alamat', 'akun/alamat'],
        ['profile', 'Profil', 'profile'],
    ] as [$route, $label, $pattern])
        <a href="{{ route($route) }}" wire:navigate
           @class([
               '-mb-px inline-flex h-12 shrink-0 items-center border-b-2 text-label font-bold uppercase tracking-nav transition-colors duration-150',
               'border-ink text-ink' => request()->is($pattern),
               'border-transparent text-ink-2 hover:text-ink' => ! request()->is($pattern),
           ])
           @if (request()->is($pattern)) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
</nav>
