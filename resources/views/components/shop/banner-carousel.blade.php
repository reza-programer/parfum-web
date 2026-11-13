@props(['banners'])
{{--
    Carousel banner beranda. Gambar dari public/images/banners (render produk, lihat database/seeders/images/source/render-banners.mjs);
    teks dan tombol ditulis di HTML supaya tajam dan terbaca mesin pencari.
    Geser dengan swipe (scroll-snap), panah di desktop, titik penanda, dan geser otomatis tiap 5 detik
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
                    this.timer = setInterval(() => { if (! this.paused && ! document.hidden) this.go(this.active + 1) }, 5000);
                },
                stop() { clearInterval(this.timer) },
             }"
             x-init="start()"
             x-on:mouseenter="paused = true" x-on:mouseleave="paused = false"
             x-on:focusin="paused = true" x-on:focusout="paused = false"
             x-on:livewire:navigating.window="stop()"
             class="relative overflow-hidden rounded-lg bg-paper-raised"
             aria-roledescription="carousel" aria-label="Banner toko">
        <div x-ref="track" x-on:scroll.debounce.80ms="sync()"
             x-on:touchstart.passive="paused = true" x-on:touchend.passive="paused = false"
             class="no-scrollbar flex snap-x snap-mandatory overflow-x-auto overscroll-x-contain">
            @foreach ($banners as $i => $banner)
                @php $light = $banner['tone'] === 'light'; @endphp
                <div class="relative w-full shrink-0 snap-start" role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} dari {{ $count }}" data-banner="{{ $banner['key'] }}">
                    <picture>
                        <source media="(min-width: 640px)" srcset="{{ asset('images/banners/'.$banner['key'].'.webp') }}" width="2400" height="800">
                        <img src="{{ asset('images/banners/'.$banner['key'].'-m.webp') }}" alt="" width="1200" height="900"
                             class="aspect-[4/3] w-full object-cover sm:aspect-[3/1]"
                             @if ($i > 0) loading="lazy" @else fetchpriority="high" @endif decoding="async">
                    </picture>
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-[50%] pl-4 pr-2 sm:w-[46%] sm:pl-16 lg:pl-20">
                            <p @class(['text-caption font-semibold uppercase tracking-nav sm:text-label', 'text-white/80' => $light, 'text-primary' => ! $light])>{{ $banner['eyebrow'] }}</p>
                            <h2 @class(['mt-1.5 text-banner font-bold sm:mt-2', 'text-white' => $light, 'text-ink' => ! $light])>{{ $banner['title'] }}</h2>
                            <p @class(['mt-3 hidden max-w-md text-body md:block lg:text-[15px] lg:leading-6', 'text-white/85' => $light, 'text-ink-2' => ! $light])>{{ $banner['text'] }}</p>
                            <a href="{{ $banner['url'] }}" wire:navigate
                               @class(['mt-3 inline-flex h-9 items-center gap-1.5 rounded-md px-3.5 text-label font-semibold transition-colors duration-150 sm:mt-5 sm:h-10 sm:px-5 sm:text-body',
                                   'bg-paper-card text-ink hover:bg-primary-soft' => $light,
                                   'bg-primary text-white hover:bg-primary-dark' => ! $light])>
                                {{ $banner['cta'] }}
                                <x-lucide-arrow-right class="size-4" stroke-width="2" />
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($count > 1)
            <button type="button" x-on:click="go(active - 1)" class="absolute left-3 top-1/2 hidden size-10 -translate-y-1/2 items-center justify-center rounded-full bg-paper-card/90 text-ink shadow-float transition-colors duration-150 hover:bg-paper-card sm:inline-flex" aria-label="Banner sebelumnya">
                <x-lucide-chevron-left class="size-5" stroke-width="2" />
            </button>
            <button type="button" x-on:click="go(active + 1)" class="absolute right-3 top-1/2 hidden size-10 -translate-y-1/2 items-center justify-center rounded-full bg-paper-card/90 text-ink shadow-float transition-colors duration-150 hover:bg-paper-card sm:inline-flex" aria-label="Banner berikutnya">
                <x-lucide-chevron-right class="size-5" stroke-width="2" />
            </button>
            <div class="absolute inset-x-0 bottom-2 flex justify-center gap-0.5 sm:bottom-3">
                @foreach ($banners as $i => $banner)
                    <button type="button" x-on:click="go({{ $i }})" class="inline-flex h-6 items-center px-1" aria-label="Tampilkan banner {{ $i + 1 }}" :aria-current="active === {{ $i }} ? 'true' : 'false'">
                        <span class="block h-1.5 rounded-full transition-all duration-300"
                              :class="active === {{ $i }} ? 'w-5 bg-paper-card' : 'w-1.5 bg-paper-card/60'"></span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>
@endif
