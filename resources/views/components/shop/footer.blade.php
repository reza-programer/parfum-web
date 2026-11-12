<footer class="mt-28 border-t border-line bg-paper-card">
    <div class="page grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
        <div class="space-y-4">
            <x-application-logo class="text-xl" />
            <p class="max-w-xs text-body text-ink-2">Parfum original dan decant, dikirim dari satu gudang ke seluruh Indonesia.</p>
        </div>
        <div>
            <p class="eyebrow text-ink">Belanja</p>
            <ul class="mt-4 space-y-2.5 text-body text-ink-2">
                <li><a href="{{ route('categories.show', 'pria') }}" wire:navigate class="hover:text-ink">Parfum pria</a></li>
                <li><a href="{{ route('categories.show', 'wanita') }}" wire:navigate class="hover:text-ink">Parfum wanita</a></li>
                <li><a href="{{ route('categories.show', 'unisex') }}" wire:navigate class="hover:text-ink">Unisex</a></li>
                <li><a href="{{ route('brands.index') }}" wire:navigate class="hover:text-ink">Semua brand</a></li>
            </ul>
        </div>
        <div>
            <p class="eyebrow text-ink">Bantuan</p>
            <ul class="mt-4 space-y-2.5 text-body text-ink-2">
                <li>Pembayaran: VA bank dan QRIS</li>
                <li>Pengiriman: JNE, J&amp;T, SiCepat, dan lainnya</li>
                <li>Tanya kami lewat chat di pojok kanan bawah</li>
            </ul>
        </div>
        <div>
            <p class="eyebrow text-ink">Akun</p>
            <ul class="mt-4 space-y-2.5 text-body text-ink-2">
                @auth
                    <li><a href="{{ route('orders.index') }}" wire:navigate class="hover:text-ink">Pesanan saya</a></li>
                    <li><a href="{{ route('profile') }}" wire:navigate class="hover:text-ink">Profil &amp; alamat</a></li>
                @else
                    <li><a href="{{ route('login') }}" wire:navigate class="hover:text-ink">Masuk</a></li>
                    <li><a href="{{ route('register') }}" wire:navigate class="hover:text-ink">Daftar</a></li>
                @endauth
            </ul>
        </div>
    </div>
    <div class="page flex flex-wrap items-center justify-between gap-2 border-t border-line py-6">
        <p class="text-caption uppercase tracking-nav text-ink-2">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        <p class="text-caption uppercase tracking-nav text-ink-2">Original · Decant · Seluruh Indonesia</p>
    </div>
</footer>
