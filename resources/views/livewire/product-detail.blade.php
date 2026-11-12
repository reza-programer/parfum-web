@php
    $photos = array_values(array_filter($product->images ?? []));
    $notes = ['top' => 'Top notes', 'middle' => 'Middle notes', 'base' => 'Base notes'];
    $unavailable = $soldOut || ! $this->variant || $this->variant->stock === 0;
    $wishLabel = $this->inWishlist ? 'Hapus dari wishlist' : 'Simpan ke wishlist';
    $stars = fn (float $value) => collect(range(1, 5))->map(fn ($i) => [$i, $i <= round($value)]);
@endphp
{{-- Detail produk gaya marketplace: galeri | info | kotak beli menempel (desktop). Di HP: bilah beli menempel di bawah. --}}
<div class="pb-24 lg:pb-0">
    <div class="page pt-3 sm:pt-5">
        <nav class="flex items-center gap-1.5 overflow-hidden text-label text-ink-2" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" wire:navigate class="shrink-0 hover:text-primary">Beranda</a>
            <x-lucide-chevron-right class="size-3.5 shrink-0" stroke-width="2" />
            <a href="{{ route('categories.show', $product->category) }}" wire:navigate class="shrink-0 hover:text-primary">{{ $product->category->name }}</a>
            <x-lucide-chevron-right class="size-3.5 shrink-0" stroke-width="2" />
            <span class="truncate text-ink">{{ $product->name }}</span>
        </nav>
    </div>

    <div class="page mt-3 grid gap-4 lg:grid-cols-[minmax(0,380px)_minmax(0,1fr)_280px] lg:items-start lg:gap-5 xl:grid-cols-[minmax(0,420px)_minmax(0,1fr)_296px]">
        {{-- Galeri --}}
        <div class="lg:sticky lg:top-[calc(var(--header-h)+1rem)]">
            @if ($photos)
                <div x-data="{ active: 0, zoom: false, lightbox: false, total: {{ count($photos) }} }"
                     x-on:keydown.escape.window="lightbox = false"
                     x-on:keydown.arrow-right.window="if (lightbox) active = (active + 1) % total"
                     x-on:keydown.arrow-left.window="if (lightbox) active = (active - 1 + total) % total">
                    <div data-zoom class="relative cursor-zoom-in overflow-hidden rounded-lg border border-line bg-paper-raised"
                         x-on:mousemove="if (window.matchMedia('(hover: hover)').matches) { const r = $el.getBoundingClientRect(); $el.style.setProperty('--zx', ((event.clientX - r.left) / r.width * 100) + '%'); $el.style.setProperty('--zy', ((event.clientY - r.top) / r.height * 100) + '%'); zoom = true }"
                         x-on:mouseleave="zoom = false"
                         x-on:click="lightbox = true">
                        @foreach ($photos as $i => $path)
                            <x-shop.product-image :product="$product" :path="$path" :eager="$i === 0"
                                                  x-show="active === {{ $i }}"
                                                  class="aspect-square origin-[var(--zx)_var(--zy)] transition-transform duration-300 ease-out"
                                                  ::class="zoom && 'scale-[1.8]'" />
                        @endforeach
                        <span class="pointer-events-none absolute bottom-2.5 right-2.5 inline-flex items-center gap-1 rounded-full bg-paper-card/90 px-2.5 py-1 text-caption font-medium text-ink-2">
                            <x-lucide-zoom-in class="size-3.5" stroke-width="2" /> Klik untuk memperbesar
                        </span>
                    </div>

                    @if (count($photos) > 1)
                        <div class="mt-2 grid grid-cols-5 gap-2">
                            @foreach ($photos as $i => $path)
                                <button type="button" x-on:click="active = {{ $i }}" class="relative overflow-hidden rounded-md border-2 transition-colors duration-150"
                                        :class="active === {{ $i }} ? 'border-primary' : 'border-transparent hover:border-line'" aria-label="Foto {{ $i + 1 }}">
                                    <x-shop.product-image :product="$product" :path="$path" class="aspect-square" />
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Lightbox --}}
                    <div x-cloak x-show="lightbox" x-transition.opacity.duration.200ms class="fixed inset-0 z-50 flex items-center justify-center bg-paper-card" role="dialog" aria-modal="true" aria-label="Foto {{ $product->name }}">
                        <button type="button" x-on:click="lightbox = false" class="absolute right-2 top-2 inline-flex size-12 items-center justify-center rounded-full text-ink hover:bg-paper" aria-label="Tutup">
                            <x-lucide-x class="size-6" stroke-width="1.75" />
                        </button>
                        @foreach ($photos as $i => $path)
                            <img x-show="active === {{ $i }}" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" alt="{{ $product->name }}, foto {{ $i + 1 }}" loading="lazy" class="max-h-[88vh] max-w-[92vw] object-contain">
                        @endforeach
                        @if (count($photos) > 1)
                            <button type="button" x-on:click="active = (active - 1 + total) % total" class="absolute left-2 top-1/2 inline-flex size-12 -translate-y-1/2 items-center justify-center rounded-full bg-paper-card text-ink shadow-float" aria-label="Foto sebelumnya">
                                <x-lucide-chevron-left class="size-6" stroke-width="1.75" />
                            </button>
                            <button type="button" x-on:click="active = (active + 1) % total" class="absolute right-2 top-1/2 inline-flex size-12 -translate-y-1/2 items-center justify-center rounded-full bg-paper-card text-ink shadow-float" aria-label="Foto berikutnya">
                                <x-lucide-chevron-right class="size-6" stroke-width="1.75" />
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <x-shop.product-image :product="$product" eager class="aspect-square rounded-lg border border-line" />
            @endif
        </div>

        {{-- Info --}}
        <div class="card min-w-0 p-4 sm:p-5">
            <h1 class="text-[20px] font-bold leading-7 text-ink sm:text-[22px] sm:leading-8">{{ $product->name }}</h1>

            <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-body text-ink-2">
                @if ($sold > 0)
                    <span>Terjual <span class="text-ink">{{ $sold }}</span></span>
                @endif
                @if ($rating)
                    @if ($sold > 0)<span aria-hidden="true">·</span>@endif
                    <a href="#ulasan" class="inline-flex items-center gap-1 hover:text-primary">
                        <x-lucide-star class="size-4 fill-amber-400 text-amber-400" stroke-width="1.5" aria-hidden="true" />
                        <span class="font-semibold tabular-nums text-ink">{{ number_format($rating, 1, ',', '') }}</span>
                        <span>({{ $reviewCount }} ulasan)</span>
                    </a>
                @endif
                @if (! $sold && ! $rating)
                    <span>Belum ada ulasan</span>
                @endif
            </div>

            @if ($this->variant)
                <p class="price mt-4 text-[28px] font-bold leading-none text-primary" data-testid="variant-price">{{ rupiah($this->variant->price) }}</p>
            @endif

            {{-- Ukuran --}}
            <div class="mt-5 border-t border-line pt-4" role="group" aria-label="Ukuran">
                <p class="text-body font-semibold text-ink">Pilih ukuran: <span class="font-normal text-ink-2">{{ $this->variant?->label() }}</span></p>
                <div class="mt-2.5 flex flex-wrap gap-2">
                    @foreach ($product->variants as $variant)
                        <button type="button" wire:click="selectVariant({{ $variant->id }})" wire:key="v-{{ $variant->id }}"
                            @class([
                                'inline-flex h-10 min-w-[76px] items-center justify-center rounded-md border px-3.5 text-body font-medium tabular-nums transition-colors duration-150',
                                'border-primary bg-primary-soft text-primary' => $variant->id === $variantId,
                                'border-line bg-paper-card text-ink hover:border-primary hover:text-primary' => $variant->id !== $variantId && $variant->stock > 0,
                                'border-line bg-paper text-ink-faint line-through' => $variant->id !== $variantId && $variant->stock === 0,
                            ])
                            aria-pressed="{{ $variant->id === $variantId ? 'true' : 'false' }}">
                            {{ $variant->label() }}
                        </button>
                    @endforeach
                </div>
                @if ($this->variant)
                    <p class="meta mt-2.5 tabular-nums" data-testid="variant-meta">
                        @if ($unavailable)
                            <span class="font-medium text-danger">Stok habis</span>
                        @else
                            <span @class(['font-medium text-warning' => $this->variant->stock <= \App\Models\Product::LOW_STOCK])>Stok {{ $this->variant->stock }}</span>
                        @endif
                        · Berat {{ number_format($this->variant->weight_gram, 0, ',', '.') }} g
                    </p>
                @endif
            </div>

            {{-- Detail --}}
            <div class="mt-5 border-t border-line pt-4">
                <h2 class="text-body font-bold text-ink">Detail produk</h2>
                <dl class="mt-3 grid grid-cols-[120px_1fr] gap-x-4 gap-y-2 text-body">
                    <dt class="text-ink-2">Brand</dt>
                    <dd><a href="{{ route('brands.show', $product->brand) }}" wire:navigate class="link">{{ $product->brand->name }}</a></dd>
                    @if ($product->concentration)
                        <dt class="text-ink-2">Konsentrasi</dt>
                        <dd class="text-ink">{{ $product->concentration->label() }}</dd>
                    @endif
                    <dt class="text-ink-2">Keluarga aroma</dt>
                    <dd><a href="{{ route('catalog', ['aroma' => [$product->scent_family->value]]) }}" wire:navigate class="link">{{ $product->scent_family->label() }}</a></dd>
                    <dt class="text-ink-2">Kategori</dt>
                    <dd class="text-ink">{{ $product->category->name }}</dd>
                    @foreach ($notes as $layer => $label)
                        @if ($product->notesList($layer))
                            <dt class="text-ink-2">{{ $label }}</dt>
                            <dd class="text-ink">{{ implode(', ', $product->notesList($layer)) }}</dd>
                        @endif
                    @endforeach
                </dl>
                <div class="mt-4 max-w-prose text-body leading-6 text-ink">{!! nl2br(e($product->description)) !!}</div>
            </div>

            {{-- Jumlah di HP (kotak beli desktop punya sendiri) --}}
            @unless ($unavailable)
                <div class="mt-5 flex items-center justify-between border-t border-line pt-4 lg:hidden">
                    <p class="text-body font-semibold text-ink">Jumlah</p>
                    @include('livewire.partials.qty-stepper')
                </div>
            @endunless
            @error('qty')
                <p class="mt-3 text-body text-danger lg:hidden">{{ $message }}</p>
            @enderror
        </div>

        {{-- Kotak beli (desktop) --}}
        <aside class="card hidden p-4 lg:sticky lg:top-[calc(var(--header-h)+1rem)] lg:block" aria-label="Atur pembelian">
            <p class="text-body font-bold text-ink">Atur jumlah</p>
            @if ($this->variant)
                <p class="mt-1 text-label text-ink-2">{{ $this->variant->label() }}</p>
            @endif

            @if ($unavailable)
                <p class="mt-4 rounded-md bg-paper px-3 py-2.5 text-body text-ink-2">Stok habis. Simpan ke wishlist untuk membelinya nanti.</p>
                <button type="button" disabled class="btn-primary mt-4 w-full">Habis</button>
            @else
                <div class="mt-3 flex items-center gap-3">
                    @include('livewire.partials.qty-stepper')
                    <span class="text-label text-ink-2 tabular-nums">Stok {{ $this->variant->stock }}</span>
                </div>
                <div class="mt-4 flex items-baseline justify-between gap-3">
                    <span class="text-body text-ink-2">Subtotal</span>
                    <span class="price text-[18px] font-bold text-ink">{{ rupiah($this->variant->price * $qty) }}</span>
                </div>
                <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="btn-primary mt-4 w-full">
                    <x-lucide-plus class="size-4" stroke-width="2.25" wire:loading.remove wire:target="addToCart" />
                    <span wire:loading.remove wire:target="addToCart">Keranjang</span>
                    <span wire:loading wire:target="addToCart">Menambahkan</span>
                </button>
                <button type="button" wire:click="buyNow" wire:loading.attr="disabled" wire:target="buyNow" class="btn-secondary mt-2 w-full">
                    <span wire:loading.remove wire:target="buyNow">Beli sekarang</span>
                    <span wire:loading wire:target="buyNow">Memproses</span>
                </button>
                @error('qty')
                    <p class="mt-3 text-body text-danger">{{ $message }}</p>
                @enderror
            @endif

            <button type="button" wire:click="toggleWishlist" class="mt-3 inline-flex h-10 w-full items-center justify-center gap-2 rounded-md text-body font-medium text-ink-2 transition-colors duration-150 hover:bg-paper hover:text-primary" aria-label="{{ $wishLabel }}">
                <x-lucide-heart @class(['size-4', 'fill-primary text-primary' => $this->inWishlist]) stroke-width="2" />
                {{ $this->inWishlist ? 'Ada di wishlist' : 'Wishlist' }}
            </button>

            <ul class="mt-3 space-y-2 border-t border-line pt-3 text-label text-ink-2">
                <li class="flex items-center gap-2"><x-lucide-shield-check class="size-4 shrink-0 text-success" stroke-width="2" /> Original, dikirim dari gudang kami</li>
                <li class="flex items-center gap-2"><x-lucide-wallet class="size-4 shrink-0 text-ink-2" stroke-width="2" /> Bayar lewat VA bank atau QRIS</li>
                <li class="flex items-center gap-2"><x-lucide-truck class="size-4 shrink-0 text-ink-2" stroke-width="2" /> Ongkir dihitung otomatis saat checkout</li>
            </ul>
        </aside>
    </div>

    {{-- Info brand --}}
    <div class="page mt-4">
        <a href="{{ route('brands.show', $product->brand) }}" wire:navigate class="card group flex items-center gap-3 p-4 transition-shadow duration-200 hover:shadow-hover">
            <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-full bg-primary-soft font-serif text-lg font-semibold uppercase text-primary">{{ mb_substr($product->brand->name, 0, 1) }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-body font-bold text-ink group-hover:text-primary">{{ $product->brand->name }}</span>
                <span class="block text-label text-ink-2">{{ $brandProductCount }} parfum di toko ini</span>
            </span>
            <span class="btn-outline btn-sm shrink-0">Lihat brand</span>
        </a>
    </div>

    {{-- Ulasan --}}
    <section id="ulasan" class="page mt-4 scroll-mt-[calc(var(--header-h)+1rem)]">
        <div class="card p-4 sm:p-5">
            <h2 class="heading-section">Ulasan pembeli</h2>
            <div class="mt-4 grid gap-6 md:grid-cols-[200px_1fr]">
                <div>
                    @if ($rating)
                        <p class="flex items-baseline gap-1"><span class="text-[36px] font-bold leading-none tabular-nums text-ink">{{ number_format($rating, 1, ',', '') }}</span><span class="text-body text-ink-2">/5</span></p>
                        <div class="mt-2 flex gap-0.5" aria-hidden="true">
                            @foreach ($stars($rating) as [$i, $on])
                                <x-lucide-star @class(['size-4', 'fill-amber-400 text-amber-400' => $on, 'text-ink-faint' => ! $on]) stroke-width="1.5" />
                            @endforeach
                        </div>
                        <p class="meta mt-1.5">{{ $reviewCount }} ulasan</p>
                    @else
                        <p class="text-body text-ink-2">Belum ada rating.</p>
                    @endif
                </div>
                <div class="divide-y divide-line">
                    @forelse ($reviews as $review)
                        <article class="py-4 first:pt-0" wire:key="r-{{ $review->id }}">
                            <div class="flex gap-0.5" aria-label="{{ $review->rating }} dari 5 bintang">
                                @foreach ($stars($review->rating) as [$i, $on])
                                    <x-lucide-star @class(['size-3.5', 'fill-amber-400 text-amber-400' => $on, 'text-ink-faint' => ! $on]) stroke-width="1.5" />
                                @endforeach
                            </div>
                            <p class="mt-1.5 text-body font-semibold text-ink">{{ $review->user->name }} <span class="font-normal text-ink-2">· {{ $review->created_at->translatedFormat('j F Y') }}</span></p>
                            @if ($review->comment)
                                <p class="mt-1 max-w-prose text-body leading-6 text-ink">{{ $review->comment }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="text-body text-ink-2">Belum ada ulasan. Ulasan bisa ditulis pembeli setelah pesanannya selesai.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="page mt-6">
            <h2 class="heading-section mb-3">Produk terkait</h2>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3 md:grid-cols-4 lg:grid-cols-5">
                @foreach ($related as $item)
                    <x-shop.product-card :product="$item" wire:key="rel-{{ $item->id }}" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Bilah beli HP: menempel di bawah layar --}}
    <div data-bottom-bar class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-paper-card px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 lg:hidden">
        <div class="mx-auto flex max-w-page items-center gap-2">
            <button type="button" wire:click="toggleWishlist" class="btn-outline size-11 shrink-0 px-0" aria-label="{{ $wishLabel }}">
                <x-lucide-heart @class(['size-5', 'fill-primary text-primary' => $this->inWishlist]) stroke-width="2" />
            </button>
            @if ($unavailable)
                <button type="button" disabled class="btn-primary flex-1">Habis</button>
            @else
                <button type="button" wire:click="buyNow" wire:loading.attr="disabled" wire:target="buyNow" class="btn-secondary flex-1 px-3">Beli sekarang</button>
                <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="btn-primary flex-1 px-3">
                    <x-lucide-plus class="size-4" stroke-width="2.25" /> Keranjang
                </button>
            @endif
        </div>
    </div>
</div>
