@props(['banners'])
{{--
    Banner beranda selebar layar (gaya docs/plans/redesign-nb.md). Gambar dari public/images/hero
    (render editorial, lihat database/seeders/images/source/render-banners.mjs). Teks dan link ditulis di HTML:
    di desktop menimpa sisi kiri gambar, di HP berada di bawah gambar.
    Geser dengan swipe (scroll-snap), panah di desktop, titik penanda, dan geser otomatis tiap 6 detik
    (berhenti saat disentuh/di-hover/difokus, dan mati kalau pengguna memilih kurangi gerakan).
--}}
@php $count = count($banners); @endphp
@if ($count)
    <section x-data="{
                active: 0,
                count: {{ $count }},
                timer: null,
                paused: false,
                go(i) {
                    this.active = (i + this.count) % this.count;
                    this.$refs.track.scrollTo({ left: this.active * this.$refs.track.clientWidth, behavior: 'smooth' });
                },
                sync() {
                    const w = this.$refs.track.clientWidth;
                    if (w) this.active = Math.round(this.$refs.track.scrollLeft / w);
                },
                start() {
                    if (this.count < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                    this.stop();
                    this.timer = setInterval(() => { if (! this.paused && ! document.hidden) this.go(this.active + 1) }, 6000);
                },
                stop() { clearInterval(this.timer) },
             }"
             x-init="start()"
             x-on:mouseenter="paused = true" x-on:mouseleave="paused = false"
             x-on:focusin="paused = true" x-on:focusout="paused = false"
             x-on:livewire:navigating.window="stop()"
             aria-roledescription="carousel" aria-label="Banner toko">
        <div class="relative">
            <div x-ref="track" x-on:scroll.debounce.80ms="sync()"
                 x-on:touchstart.passive="paused = true" x-on:touchend.passive="paused = false"
                 class="no-scrollbar flex snap-x snap-mandatory overflow-x-auto overscroll-x-contain">
                @foreach ($banners as $i => $banner)
                    <div class="w-full shrink-0 snap-start" role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} dari {{ $count }}" data-banner="{{ $banner['key'] }}">
                        <div class="relative bg-paper-raised">
                            <picture>
                                <source media="(min-width: 768px)" srcset="{{ asset('images/hero/'.$banner['key'].'.webp') }}" width="2400" height="1000">
                                <img src="{{ asset('images/hero/'.$banner['key'].'-m.webp') }}" alt="" width="1080" height="1350"
                                     class="aspect-[4/5] w-full object-cover md:aspect-[12/5]"
                                     @if ($i > 0) loading="lazy" @else fetchpriority="high" @endif decoding="async">
                            </picture>
                            {{-- Desktop: teks di sisi kiri gambar --}}
                            <div class="absolute inset-0 hidden items-center md:flex">
                                <div class="page">
                                    <div class="max-w-md lg:max-w-lg">
                                        <p class="text-label font-bold uppercase tracking-nav text-ink">{{ $banner['eyebrow'] }}</p>
                                        <h2 class="mt-3 text-banner font-semibold tracking-tight text-ink">{{ $banner['title'] }}</h2>
                                        <p class="mt-4 max-w-sm text-[15px] leading-6 text-ink">{{ $banner['text'] }}</p>
                                        <a href="{{ $banner['url'] }}" wire:navigate class="mt-7 inline-block border-b-2 border-ink pb-0.5 text-[15px] font-bold text-ink transition-colors duration-150 hover:border-primary hover:text-primary">{{ $banner['cta'] }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- HP: teks di bawah gambar --}}
                        <div class="page pt-6 md:hidden">
                            <p class="text-caption font-bold uppercase tracking-nav text-ink-2">{{ $banner['eyebrow'] }}</p>
                            <h2 class="mt-2 text-[28px] font-semibold leading-tight tracking-tight text-ink">{{ $banner['title'] }}</h2>
                            <p class="mt-2 text-body text-ink">{{ $banner['text'] }}</p>
                            <a href="{{ $banner['url'] }}" wire:navigate class="mt-4 inline-block border-b-2 border-ink pb-0.5 text-body font-bold text-ink">{{ $banner['cta'] }}</a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($count > 1)
                <button type="button" x-on:click="go(active - 1)" class="absolute left-4 top-1/2 hidden size-11 -translate-y-1/2 items-center justify-center bg-paper-card/80 text-ink transition-colors duration-150 hover:bg-paper-card md:inline-flex" aria-label="Banner sebelumnya">
                    <x-lucide-chevron-left class="size-5" stroke-width="1.75" />
                </button>
                <button type="button" x-on:click="go(active + 1)" class="absolute right-4 top-1/2 hidden size-11 -translate-y-1/2 items-center justify-center bg-paper-card/80 text-ink transition-colors duration-150 hover:bg-paper-card md:inline-flex" aria-label="Banner berikutnya">
                    <x-lucide-chevron-right class="size-5" stroke-width="1.75" />
                </button>
            @endif
        </div>

        @if ($count > 1)
            <div class="flex justify-center gap-1 pt-4">
                @foreach ($banners as $i => $banner)
                    <button type="button" x-on:click="go({{ $i }})" class="inline-flex size-6 items-center justify-center" aria-label="Tampilkan banner {{ $i + 1 }}" :aria-current="active === {{ $i }} ? 'true' : 'false'">
                        <span class="block size-2 rounded-full transition-colors duration-300" :class="active === {{ $i }} ? 'bg-primary' : 'bg-ink/20'"></span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>
@endif
