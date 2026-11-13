@php
    $head = 'text-label font-bold uppercase tracking-nav text-white';
    $footLink = 'text-white/70 transition-colors duration-150 hover:text-white';
@endphp
{{-- Footer gelap: kolom berjudul kapital, link putih redup. --}}
<footer class="mt-20 bg-night text-white">
    <div class="page grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr] lg:py-14">
        <div class="space-y-3">
            <x-application-logo inverse class="text-xl" />
            <p class="max-w-xs text-body text-white/70">Parfum original dan decant, dikirim dari satu gudang ke seluruh Indonesia.</p>
        </div>
        <div>
            <p class="{{ $head }}">Belanja</p>
            <ul class="mt-4 space-y-2.5 text-body">
                <li><a href="{{ route('categories.show', 'pria') }}" wire:navigate class="{{ $footLink }}">Parfum pria</a></li>
                <li><a href="{{ route('categories.show', 'wanita') }}" wire:navigate class="{{ $footLink }}">Parfum wanita</a></li>
                <li><a href="{{ route('categories.show', 'unisex') }}" wire:navigate class="{{ $footLink }}">Unisex</a></li>
                <li><a href="{{ route('catalog', ['ukuran' => ['decant']]) }}" wire:navigate class="{{ $footLink }}">Decant</a></li>
                <li><a href="{{ route('brands.index') }}" wire:navigate class="{{ $footLink }}">Semua brand</a></li>
            </ul>
        </div>
        <div>
            <p class="{{ $head }}">Bantuan</p>
            <ul class="mt-4 space-y-2.5 text-body text-white/70">
                <li>Pembayaran: VA bank dan QRIS</li>
                <li>Pengiriman: JNE, J&amp;T, SiCepat, dan lainnya</li>
                <li><button type="button" x-data x-on:click="$dispatch('open-chat')" class="{{ $footLink }} text-left">Tanya kami lewat chat</button></li>
            </ul>
        </div>
        <div>
            <p class="{{ $head }}">Akun</p>
            <ul class="mt-4 space-y-2.5 text-body">
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
    <div class="border-t border-white/10">
        <div class="page flex flex-wrap items-center justify-between gap-2 py-5 text-caption uppercase tracking-nav text-white/60">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
            <p>Original · Decant · Seluruh Indonesia</p>
        </div>
    </div>
</footer>
