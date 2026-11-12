@php
    $footLink = 'transition-colors duration-150 hover:text-primary';
@endphp
<footer class="mt-16 border-t border-line bg-paper-card">
    <div class="page grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1fr] lg:py-12">
        <div class="space-y-3">
            <x-application-logo class="text-xl" />
            <p class="max-w-xs text-body text-ink-2">Parfum original dan decant, dikirim dari satu gudang ke seluruh Indonesia.</p>
        </div>
        <div>
            <p class="text-body font-bold text-ink">Belanja</p>
            <ul class="mt-3 space-y-2 text-body text-ink-2">
                <li><a href="{{ route('categories.show', 'pria') }}" wire:navigate class="{{ $footLink }}">Parfum pria</a></li>
                <li><a href="{{ route('categories.show', 'wanita') }}" wire:navigate class="{{ $footLink }}">Parfum wanita</a></li>
                <li><a href="{{ route('categories.show', 'unisex') }}" wire:navigate class="{{ $footLink }}">Unisex</a></li>
                <li><a href="{{ route('catalog', ['ukuran' => ['decant']]) }}" wire:navigate class="{{ $footLink }}">Decant</a></li>
                <li><a href="{{ route('brands.index') }}" wire:navigate class="{{ $footLink }}">Semua brand</a></li>
            </ul>
        </div>
        <div>
            <p class="text-body font-bold text-ink">Bantuan</p>
            <ul class="mt-3 space-y-2 text-body text-ink-2">
                <li>Pembayaran: VA bank dan QRIS</li>
                <li>Pengiriman: JNE, J&amp;T, SiCepat, dan lainnya</li>
                <li>Tanya kami lewat chat di pojok kanan bawah</li>
            </ul>
        </div>
        <div>
            <p class="text-body font-bold text-ink">Akun</p>
            <ul class="mt-3 space-y-2 text-body text-ink-2">
                @auth
                    <li><a href="{{ route('orders.index') }}" wire:navigate class="{{ $footLink }}">Pesanan saya</a></li>
                    <li><a href="{{ route('wishlist') }}" wire:navigate class="{{ $footLink }}">Wishlist</a></li>
                    <li><a href="{{ route('profile') }}" wire:navigate class="{{ $footLink }}">Profil &amp; alamat</a></li>
                @else
                    <li><a href="{{ route('login') }}" wire:navigate class="{{ $footLink }}">Masuk</a></li>
                    <li><a href="{{ route('register') }}" wire:navigate class="{{ $footLink }}">Daftar</a></li>
                @endauth
            </ul>
        </div>
    </div>
    <div class="border-t border-line">
        <div class="page flex flex-wrap items-center justify-between gap-2 py-5">
            <p class="text-label text-ink-2">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
            <p class="text-label text-ink-2">Original · Decant · Seluruh Indonesia</p>
        </div>
    </div>
</footer>
