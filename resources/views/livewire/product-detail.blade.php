@php
    $photos = array_values(array_filter($product->images ?? []));
    $notes = ['top' => 'Top notes', 'middle' => 'Middle notes', 'base' => 'Base notes'];
    $unavailable = $soldOut || ! $this->variant || $this->variant->stock === 0;
    $wishLabel = $this->inWishlist ? 'Hapus dari wishlist' : 'Simpan ke wishlist';
    $stars = fn (float $value) => collect(range(1, 5))->map(fn ($i) => [$i, $i <= round($value)]);
    $tag = match (true) {
        $soldOut => 'Habis',
        \App\Support\BestsellerIds::has($product->id) => 'Terlaris',
        $product->isNew() => 'Baru',
        default => null,
    };
    $crumb = 'hover:text-ink hover:underline hover:underline-offset-4';
    $blockHead = 'text-label font-bold uppercase tracking-nav text-ink';
@endphp
{{-- Detail produk gaya newbalance.co.id (docs/plans/redesign-nb.md): galeri besar | info. Di HP: bilah beli menempel di bawah. --}}
<div class="pb-24 lg:pb-0">
    <div class="page pt-5 lg:pt-6">
        <nav class="flex items-center gap-2 overflow-hidden text-caption font-bold uppercase tracking-nav text-ink-2" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" wire:navigate class="{{ $crumb }} shrink-0">Beranda</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('categories.show', $product->category) }}" wire:navigate class="{{ $crumb }} shrink-0">{{ $product->category->name }}</a>
            <span aria-hidden="true">/</span>
            <span class="truncate text-ink">{{ $product->name }}</span>
        </nav>
    </div>

    <div class="page mt-5 grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-start lg:gap-14">
        {{-- Galeri --}}
        <div class="lg:sticky lg:top-[calc(var(--header-h)+1.5rem)]">
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
                        @if ($tag)
                            <span class="tag pointer-events-none absolute left-0 top-4">{{ $tag }}</span>
                        @endif
                        <span class="pointer-events-none absolute bottom-3 right-3 inline-flex items-center gap-1.5 bg-paper-card px-2.5 py-1 text-caption font-bold uppercase tracking-label text-ink">
                            <x-lucide-zoom-in class="size-3.5" stroke-width="2" /> Perbesar
                        </span>
                    </div>

                    @if (count($photos) > 1)
                        <div class="mt-3 grid grid-cols-5 gap-3">
                            @foreach ($photos as $i => $path)
                                <button type="button" x-on:click="active = {{ $i }}" class="relative border-2 transition-colors duration-150"
                                        :class="active === {{ $i }} ? 'border-ink' : 'border-transparent hover:border-line'" aria-label="Foto {{ $i + 1 }}">
                                    <x-shop.product-image :product="$product" :path="$path" class="aspect-square" />
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Lightbox --}}
                    <div x-cloak x-show="lightbox" x-transition.opacity.duration.200ms class="fixed inset-0 z-50 flex items-center justify-center bg-paper-card" role="dialog" aria-modal="true" aria-label="Foto {{ $product->name }}">
                        <button type="button" x-on:click="lightbox = false" class="absolute right-2 top-2 inline-flex size-12 items-center justify-center text-ink" aria-label="Tutup">
                            <x-lucide-x class="size-6" stroke-width="1.5" />
                        </button>
                        @foreach ($photos as $i => $path)
                            <img x-show="active === {{ $i }}" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" alt="{{ $product->name }}, foto {{ $i + 1 }}" loading="lazy" class="max-h-[88vh] max-w-[92vw] object-contain">
                        @endforeach
                        @if (count($photos) > 1)
                            <button type="button" x-on:click="active = (active - 1 + total) % total" class="absolute left-2 top-1/2 inline-flex size-12 -translate-y-1/2 items-center justify-center text-ink" aria-label="Foto sebelumnya">
                                <x-lucide-chevron-left class="size-7" stroke-width="1.5" />
                            </button>
                            <button type="button" x-on:click="active = (active + 1) % total" class="absolute right-2 top-1/2 inline-flex size-12 -translate-y-1/2 items-center justify-center text-ink" aria-label="Foto berikutnya">
                                <x-lucide-chevron-right class="size-7" stroke-width="1.5" />
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <x-shop.product-image :product="$product" eager class="aspect-square" />
            @endif
        </div>

        {{-- Info dan beli --}}
        <div class="min-w-0">
            <a href="{{ route('brands.show', $product->brand) }}" wire:navigate class="text-label font-bold uppercase tracking-nav text-primary hover:underline hover:underline-offset-4">{{ $product->brand->name }}</a>
            <h1 class="mt-2 text-[26px] font-semibold leading-tight text-ink lg:text-[32px]">{{ $product->name }}</h1>
            <p class="mt-2 text-label uppercase tracking-label text-ink-2">
                {{ collect([$product->concentration?->label(), $product->scent_family->label(), $product->category->name])->filter()->implode(' · ') }}
            </p>

            @if ($rating || $sold > 0)
                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-body text-ink-2">
                    @if ($rating)
                        <a href="#ulasan" class="inline-flex items-center gap-1.5 hover:text-ink" data-rating="{{ $rating }}">
                            <span class="flex gap-0.5" aria-hidden="true">
                                @foreach ($stars($rating) as [$i, $on])
                                    <x-lucide-star @class(['size-4', 'fill-ink text-ink' => $on, 'text-ink/25' => ! $on]) stroke-width="1.5" />
                                @endforeach
                            </span>
                            <span class="font-semibold tabular-nums text-ink">{{ number_format($rating, 1, ',', '') }}</span>
                            <span class="underline underline-offset-4">{{ $reviewCount }} ulasan</span>
                        </a>
                    @endif
                    @if ($sold > 0)
                        <span class="tabular-nums">{{ $sold }} terjual</span>
                    @endif
                </div>
            @endif

            @if ($this->variant)
                <p class="price mt-5 text-[26px] font-bold leading-none text-ink" data-testid="variant-price">{{ rupiah($this->variant->price) }}</p>
            @endif

            {{-- Ukuran --}}
            <div class="mt-7" role="group" aria-label="Ukuran">
                <p class="{{ $blockHead }}">Ukuran <span class="ml-1 font-normal normal-case tracking-normal text-ink-2">{{ $this->variant?->label() }}</span></p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($product->variants as $variant)
                        <button type="button" wire:click="selectVariant({{ $variant->id }})" wire:key="v-{{ $variant->id }}"
                            @class([
                                'inline-flex h-12 min-w-[88px] items-center justify-center border px-4 text-body tabular-nums transition-colors duration-150',
                                'border-ink font-bold text-ink ring-1 ring-inset ring-ink' => $variant->id === $variantId,
                                'border-line text-ink hover:border-ink' => $variant->id !== $variantId && $variant->stock > 0,
                                'border-line bg-paper-raised text-ink-faint line-through' => $variant->id !== $variantId && $variant->stock === 0,
                            ])
                            aria-pressed="{{ $variant->id === $variantId ? 'true' : 'false' }}">
                            {{ $variant->label() }}
                        </button>
                    @endforeach
                </div>
                @if ($this->variant)
                    <p class="meta mt-3 tabular-nums" data-testid="variant-meta">
                        @if ($unavailable)
                            <span class="font-bold text-primary">Stok habis</span>
                        @else
                            Stok {{ $this->variant->stock }}
                        @endif
                        · Berat {{ number_format($this->variant->weight_gram, 0, ',', '.') }} g
                        @if (! $unavailable && $product->isLowStock())
                            · <span class="font-bold text-primary">Stok terbatas</span>
                        @endif
                    </p>
                @endif
            </div>

            {{-- Beli --}}
            @if ($unavailable)
                <div class="mt-7">
                    <button type="button" disabled class="btn-primary w-full">Habis</button>
                    <p class="meta mt-3">Simpan ke wishlist untuk membelinya nanti.</p>
                </div>
            @else
                <div class="mt-7">
                    <p class="{{ $blockHead }}">Kuantitas</p>
                    <div class="mt-3 inline-flex h-12 items-center border border-line">
                        <button type="button" wire:click="decrement" @disabled($qty <= 1) class="inline-flex h-12 w-12 items-center justify-center text-ink disabled:text-ink-faint" aria-label="Kurangi jumlah">
                            <x-lucide-minus class="size-4" stroke-width="2" />
                        </button>
                        <span class="w-10 text-center text-body font-semibold tabular-nums" data-testid="qty">{{ $qty }}</span>
                        <button type="button" wire:click="increment" @disabled($qty >= $this->variant->stock) class="inline-flex h-12 w-12 items-center justify-center text-ink disabled:text-ink-faint" aria-label="Tambah jumlah">
                            <x-lucide-plus class="size-4" stroke-width="2" />
                        </button>
                    </div>
                </div>
                <div class="mt-7 hidden space-y-3 lg:block">
                    <button type="button" wire:click="buyNow" wire:loading.attr="disabled" wire:target="buyNow" class="btn-primary h-14 w-full">
                        <span wire:loading.remove wire:target="buyNow">Beli sekarang</span>
                        <span wire:loading wire:target="buyNow">Memproses</span>
                    </button>
                    <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="btn-secondary h-14 w-full">
                        <span wire:loading.remove wire:target="addToCart">Tambah ke keranjang</span>
                        <span wire:loading wire:target="addToCart">Menambahkan</span>
                    </button>
                </div>
            @endif
            @error('qty')
                <p class="mt-3 text-body font-medium text-primary">{{ $message }}</p>
            @enderror

            <button type="button" wire:click="toggleWishlist" class="mt-4 inline-flex h-11 items-center gap-2 text-body text-ink underline underline-offset-4 hover:text-primary" aria-label="{{ $wishLabel }}">
                <x-lucide-heart @class(['size-[18px]', 'fill-primary text-primary' => $this->inWishlist]) stroke-width="1.5" />
                {{ $this->inWishlist ? 'Ada di wishlist' : 'Simpan ke wishlist' }}
            </button>

            <ul class="mt-6 space-y-2.5 border-t border-line pt-6 text-body text-ink">
                <li class="flex items-center gap-3"><x-lucide-shield-check class="size-[18px] shrink-0" stroke-width="1.5" /> Original, dikirim dari gudang kami</li>
                <li class="flex items-center gap-3"><x-lucide-wallet class="size-[18px] shrink-0" stroke-width="1.5" /> Bayar lewat VA bank atau QRIS</li>
                <li class="flex items-center gap-3"><x-lucide-truck class="size-[18px] shrink-0" stroke-width="1.5" /> Ongkir dihitung otomatis saat checkout</li>
            </ul>
        </div>
    </div>

    {{-- Detail produk --}}
    <section class="page mt-16 lg:mt-24">
        <h2 class="heading-section border-b border-ink pb-4">Detail produk</h2>
        <div class="mt-8 grid gap-10 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="{{ $blockHead }}">Gambaran umum</p>
                <div class="mt-3 max-w-prose text-body leading-7 text-ink">{!! nl2br(e($product->description)) !!}</div>
            </div>
            <dl class="divide-y divide-line border-y border-line text-body">
                <div class="grid grid-cols-[140px_1fr] gap-4 py-3">
                    <dt class="font-bold text-ink">Brand</dt>
                    <dd><a href="{{ route('brands.show', $product->brand) }}" wire:navigate class="link">{{ $product->brand->name }}</a></dd>
                </div>
                @if ($product->concentration)
                    <div class="grid grid-cols-[140px_1fr] gap-4 py-3">
                        <dt class="font-bold text-ink">Konsentrasi</dt>
                        <dd class="text-ink">{{ $product->concentration->label() }}</dd>
                    </div>
                @endif
                <div class="grid grid-cols-[140px_1fr] gap-4 py-3">
                    <dt class="font-bold text-ink">Keluarga aroma</dt>
                    <dd><a href="{{ route('catalog', ['aroma' => [$product->scent_family->value]]) }}" wire:navigate class="link">{{ $product->scent_family->label() }}</a></dd>
                </div>
                @foreach ($notes as $layer => $label)
                    @if ($product->notesList($layer))
                        <div class="grid grid-cols-[140px_1fr] gap-4 py-3">
                            <dt class="font-bold text-ink">{{ $label }}</dt>
                            <dd class="text-ink">{{ implode(', ', $product->notesList($layer)) }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Ulasan --}}
    <section id="ulasan" class="page mt-16 scroll-mt-[calc(var(--header-h)+1.5rem)] lg:mt-24">
        <div class="flex flex-wrap items-end justify-between gap-4 border-b border-ink pb-4">
            <h2 class="heading-section">Ulasan pembeli</h2>
            @if ($rating)
                <p class="text-body tabular-nums text-ink"><span class="font-bold">{{ number_format($rating, 1, ',', '') }}</span> dari 5 · {{ $reviewCount }} ulasan</p>
            @endif
        </div>
        @forelse ($reviews as $review)
            <article class="grid gap-2 border-b border-line py-6 sm:grid-cols-[200px_1fr] sm:gap-8" wire:key="r-{{ $review->id }}">
                <div>
                    <p class="text-body font-bold text-ink">{{ $review->user->name }}</p>
                    <p class="meta">{{ $review->created_at->translatedFormat('j F Y') }}</p>
                </div>
                <div>
                    <div class="flex gap-0.5" aria-label="{{ $review->rating }} dari 5 bintang">
                        @foreach ($stars($review->rating) as [$i, $on])
                            <x-lucide-star @class(['size-4', 'fill-ink text-ink' => $on, 'text-ink/25' => ! $on]) stroke-width="1.5" />
                        @endforeach
                    </div>
                    @if ($review->comment)
                        <p class="mt-2 max-w-prose text-body leading-7 text-ink">{{ $review->comment }}</p>
                    @endif
                </div>
            </article>
        @empty
            <p class="py-8 text-body text-ink-2">Belum ada ulasan. Ulasan bisa ditulis pembeli setelah pesanannya selesai.</p>
        @endforelse
    </section>

    @if ($related->isNotEmpty())
        <section class="page mt-16 lg:mt-24">
            <h2 class="heading-section">Produk terkait</h2>
            <div class="mt-6 grid grid-cols-2 gap-x-3 gap-y-8 sm:gap-x-5 md:grid-cols-4">
                @foreach ($related as $item)
                    <x-shop.product-card :product="$item" wire:key="rel-{{ $item->id }}" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Bilah beli HP: menempel di bawah layar --}}
    <div data-bottom-bar class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-paper-card px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 lg:hidden">
        <div class="mx-auto flex max-w-page items-center gap-2">
            <button type="button" wire:click="toggleWishlist" class="btn-outline size-12 shrink-0 px-0" aria-label="{{ $wishLabel }}">
                <x-lucide-heart @class(['size-5', 'fill-primary text-primary' => $this->inWishlist]) stroke-width="1.5" />
            </button>
            @if ($unavailable)
                <button type="button" disabled class="btn-primary flex-1">Habis</button>
            @else
                <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="btn-secondary flex-1 px-3">Keranjang</button>
                <button type="button" wire:click="buyNow" wire:loading.attr="disabled" wire:target="buyNow" class="btn-primary flex-1 px-3">Beli sekarang</button>
            @endif
        </div>
    </div>
</div>
