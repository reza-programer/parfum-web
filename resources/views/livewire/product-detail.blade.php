@php
    $photos = array_values(array_filter($product->images ?? []));
    $notes = ['top' => 'Top notes', 'middle' => 'Middle notes', 'base' => 'Base notes'];
    $unavailable = $soldOut || ! $this->variant || $this->variant->stock === 0;
@endphp
<div>
    <div class="page pt-6 lg:pt-8">
        <nav class="flex flex-wrap items-center gap-2 text-caption uppercase tracking-label text-ink-2" aria-label="Breadcrumb">
            <a href="{{ route('catalog') }}" wire:navigate class="hover:text-ink">Katalog</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('categories.show', $product->category) }}" wire:navigate class="hover:text-ink">{{ $product->category->name }}</a>
            <span aria-hidden="true">/</span>
            <span class="text-ink">{{ $product->name }}</span>
        </nav>
    </div>

    <div class="page mt-6 grid gap-10 lg:mt-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:gap-16 xl:gap-24">
        {{-- Galeri --}}
        <div class="lg:sticky lg:top-[104px] lg:self-start">
            @if ($photos)
                <div x-data="{ active: 0, zoom: false, lightbox: false, total: {{ count($photos) }} }"
                     x-on:keydown.escape.window="lightbox = false"
                     x-on:keydown.arrow-right.window="if (lightbox) active = (active + 1) % total"
                     x-on:keydown.arrow-left.window="if (lightbox) active = (active - 1 + total) % total">
                    <div data-zoom class="relative cursor-zoom-in overflow-hidden bg-paper-raised"
                         x-on:mousemove="if (window.matchMedia('(hover: hover)').matches) { const r = $el.getBoundingClientRect(); $el.style.setProperty('--zx', ((event.clientX - r.left) / r.width * 100) + '%'); $el.style.setProperty('--zy', ((event.clientY - r.top) / r.height * 100) + '%'); zoom = true }"
                         x-on:mouseleave="zoom = false"
                         x-on:click="lightbox = true">
                        @foreach ($photos as $i => $path)
                            <x-shop.product-image :product="$product" :path="$path" :eager="$i === 0"
                                                  x-show="active === {{ $i }}"
                                                  class="aspect-square origin-[var(--zx)_var(--zy)] transition-transform duration-300 ease-out"
                                                  ::class="zoom && 'scale-[1.8]'" />
                        @endforeach
                        <span class="pointer-events-none absolute bottom-3 right-3 bg-paper-card px-2 py-1 text-caption uppercase tracking-label text-ink-2">Klik untuk memperbesar</span>
                    </div>

                    @if (count($photos) > 1)
                        <div class="mt-3 grid grid-cols-5 gap-3">
                            @foreach ($photos as $i => $path)
                                <button type="button" x-on:click="active = {{ $i }}" class="relative bg-paper-raised outline outline-1 -outline-offset-1 transition-[outline-color] duration-150"
                                        :class="active === {{ $i }} ? 'outline-ink' : 'outline-transparent hover:outline-line'" aria-label="Foto {{ $i + 1 }}">
                                    <x-shop.product-image :product="$product" :path="$path" class="aspect-square" />
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Lightbox --}}
                    <div x-cloak x-show="lightbox" x-transition.opacity.duration.200ms class="fixed inset-0 z-50 flex items-center justify-center bg-paper-card" role="dialog" aria-modal="true" aria-label="Foto {{ $product->name }}">
                        <button type="button" x-on:click="lightbox = false" class="absolute right-2 top-2 inline-flex size-12 items-center justify-center text-ink" aria-label="Tutup">
                            <x-lucide-x class="size-6" stroke-width="1.25" />
                        </button>
                        @foreach ($photos as $i => $path)
                            <img x-show="active === {{ $i }}" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" alt="{{ $product->name }}, foto {{ $i + 1 }}" loading="lazy" class="max-h-[88vh] max-w-[92vw] object-contain">
                        @endforeach
                        @if (count($photos) > 1)
                            <button type="button" x-on:click="active = (active - 1 + total) % total" class="absolute left-2 top-1/2 inline-flex size-12 -translate-y-1/2 items-center justify-center text-ink" aria-label="Foto sebelumnya">
                                <x-lucide-chevron-left class="size-6" stroke-width="1.25" />
                            </button>
                            <button type="button" x-on:click="active = (active + 1) % total" class="absolute right-2 top-1/2 inline-flex size-12 -translate-y-1/2 items-center justify-center text-ink" aria-label="Foto berikutnya">
                                <x-lucide-chevron-right class="size-6" stroke-width="1.25" />
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <x-shop.product-image :product="$product" eager class="aspect-square" />
            @endif
        </div>

        {{-- Info --}}
        <div>
            <a href="{{ route('brands.show', $product->brand) }}" wire:navigate class="eyebrow hover:text-ink">{{ $product->brand->name }}</a>
            <h1 class="heading-page mt-3">{{ $product->name }}</h1>

            <p class="mt-3 text-label uppercase tracking-label text-ink-2">
                {{ collect([$product->concentration?->label(), $product->scent_family->label(), $product->category->name])->filter()->implode(' · ') }}
            </p>

            @if ($rating)
                <a href="#ulasan" class="mt-4 inline-flex items-center gap-2 text-label text-ink-2 hover:text-ink">
                    <span class="flex gap-0.5" aria-hidden="true">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-lucide-star @class(['size-3.5', 'fill-ink text-ink' => $i <= round($rating), 'text-ink/25' => $i > round($rating)]) stroke-width="1.25" />
                        @endfor
                    </span>
                    <span class="tabular-nums"><span class="text-ink">{{ number_format($rating, 1, ',', '') }}</span> · {{ $reviewCount }} ulasan</span>
                </a>
            @endif

            @if ($this->variant)
                <p class="price mt-6 text-[26px] font-medium leading-none text-ink" data-testid="variant-price">{{ rupiah($this->variant->price) }}</p>
            @endif

            <div class="mt-6 max-w-prose text-body leading-7 text-ink-2">{!! nl2br(e($product->description)) !!}</div>

            @if (collect(array_keys($notes))->contains(fn ($layer) => $product->notesList($layer)))
                <dl class="mt-8 divide-y divide-line border-y border-line">
                    @foreach ($notes as $layer => $label)
                        @if ($product->notesList($layer))
                            <div class="grid grid-cols-[110px_1fr] gap-4 py-3.5">
                                <dt class="eyebrow pt-0.5 text-ink">{{ $label }}</dt>
                                <dd class="text-body text-ink-2">{{ implode(', ', $product->notesList($layer)) }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            @endif

            {{-- Ukuran --}}
            <div class="mt-8" role="group" aria-label="Ukuran">
                <p class="eyebrow text-ink">Ukuran</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($product->variants as $variant)
                        <button type="button" wire:click="selectVariant({{ $variant->id }})" wire:key="v-{{ $variant->id }}"
                            @class([
                                'inline-flex h-11 min-w-[76px] items-center justify-center border px-4 text-label tabular-nums transition-colors duration-150',
                                'border-ink bg-ink text-white' => $variant->id === $variantId,
                                'border-line bg-paper-card text-ink hover:border-ink' => $variant->id !== $variantId && $variant->stock > 0,
                                'border-line bg-paper-card text-ink-faint line-through' => $variant->id !== $variantId && $variant->stock === 0,
                            ])
                            aria-pressed="{{ $variant->id === $variantId ? 'true' : 'false' }}">
                            {{ $variant->label() }}
                        </button>
                    @endforeach
                </div>
                @if ($this->variant)
                    <p class="meta mt-3 tabular-nums" data-testid="variant-meta">
                        @if ($unavailable)
                            Stok habis
                        @else
                            Stok {{ $this->variant->stock }}
                        @endif
                        · Berat {{ number_format($this->variant->weight_gram, 0, ',', '.') }} g
                    </p>
                @endif
            </div>

            {{-- Beli --}}
            <div class="mt-8">
                @if ($unavailable)
                    <div class="flex gap-3">
                        <button type="button" disabled class="btn-primary h-12 flex-1">Habis</button>
                        <button type="button" wire:click="toggleWishlist" class="btn-secondary h-12 w-12 px-0" aria-label="{{ $this->inWishlist ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}">
                            <x-lucide-heart @class(['size-[18px]', 'fill-current' => $this->inWishlist]) stroke-width="1.25" />
                        </button>
                    </div>
                    <p class="meta mt-3">Simpan ke wishlist untuk membelinya nanti.</p>
                @else
                    {{-- Mobile: jumlah + wishlist sebaris, tombol keranjang selebar layar di bawahnya --}}
                    <div class="flex flex-wrap gap-3 sm:flex-nowrap">
                        <div class="inline-flex h-12 items-center border border-line bg-paper-card">
                            <button type="button" wire:click="decrement" class="inline-flex h-12 w-11 items-center justify-center text-ink-2 hover:text-ink" aria-label="Kurangi jumlah">
                                <x-lucide-minus class="size-4" stroke-width="1.25" />
                            </button>
                            <span class="w-8 text-center text-body tabular-nums" data-testid="qty">{{ $qty }}</span>
                            <button type="button" wire:click="increment" class="inline-flex h-12 w-11 items-center justify-center text-ink-2 hover:text-ink" aria-label="Tambah jumlah">
                                <x-lucide-plus class="size-4" stroke-width="1.25" />
                            </button>
                        </div>
                        <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="btn-primary order-last h-12 basis-full sm:order-none sm:basis-auto sm:flex-1">
                            <span wire:loading.remove wire:target="addToCart">Tambah ke keranjang</span>
                            <span wire:loading wire:target="addToCart">Menambahkan</span>
                        </button>
                        <button type="button" wire:click="toggleWishlist" class="btn-secondary ms-auto h-12 w-12 shrink-0 px-0 sm:ms-0" aria-label="{{ $this->inWishlist ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}">
                            <x-lucide-heart @class(['size-[18px]', 'fill-current' => $this->inWishlist]) stroke-width="1.25" />
                        </button>
                    </div>
                    <button type="button" wire:click="buyNow" wire:loading.attr="disabled" wire:target="buyNow" class="btn-secondary mt-3 h-12 w-full">
                        <span wire:loading.remove wire:target="buyNow">Beli sekarang</span>
                        <span wire:loading wire:target="buyNow">Memproses</span>
                    </button>
                    @error('qty')
                        <p class="mt-3 text-body text-danger">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <ul class="mt-8 space-y-2 border-t border-line pt-6 text-label text-ink-2">
                <li>Original, dikirim dari gudang kami</li>
                <li>Bayar lewat VA bank atau QRIS</li>
                <li>Ongkir dihitung otomatis saat checkout</li>
            </ul>
        </div>
    </div>

    {{-- Ulasan --}}
    <section id="ulasan" class="page mt-24 scroll-mt-24">
        <div class="flex flex-wrap items-end justify-between gap-4 border-b border-line pb-4">
            <h2 class="heading-section">Ulasan pembeli</h2>
            @if ($rating)
                <p class="text-label tabular-nums text-ink-2"><span class="text-ink">{{ number_format($rating, 1, ',', '') }}</span> dari 5 · {{ $reviewCount }} ulasan</p>
            @endif
        </div>
        @forelse ($reviews as $review)
            <article class="grid gap-2 border-b border-line py-6 sm:grid-cols-[200px_1fr] sm:gap-8" wire:key="r-{{ $review->id }}">
                <div>
                    <p class="text-body text-ink">{{ $review->user->name }}</p>
                    <p class="meta">{{ $review->created_at->translatedFormat('j F Y') }}</p>
                </div>
                <div>
                    <div class="flex gap-0.5" aria-label="{{ $review->rating }} dari 5 bintang">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-lucide-star @class(['size-3.5', 'fill-ink text-ink' => $i <= $review->rating, 'text-ink/25' => $i > $review->rating]) stroke-width="1.25" />
                        @endfor
                    </div>
                    @if ($review->comment)
                        <p class="mt-2 max-w-prose text-body leading-7 text-ink-2">{{ $review->comment }}</p>
                    @endif
                </div>
            </article>
        @empty
            <p class="py-8 text-body text-ink-2">Belum ada ulasan. Ulasan bisa ditulis pembeli setelah pesanannya selesai.</p>
        @endforelse
    </section>

    @if ($related->isNotEmpty())
        <section class="page mt-24">
            <h2 class="heading-section">Produk terkait</h2>
            <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 md:grid-cols-4">
                @foreach ($related as $item)
                    <x-shop.product-card :product="$item" wire:key="rel-{{ $item->id }}" />
                @endforeach
            </div>
        </section>
    @endif
</div>
