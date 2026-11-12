<div class="page pt-10 lg:pt-14">
    <p class="eyebrow">Belanja</p>
    <div class="mt-3 flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
        <h1 class="heading-page">Keranjang</h1>
        @unless ($this->summary->isEmpty())
            <p class="meta tabular-nums">{{ $this->summary->itemCount }} barang</p>
        @endunless
    </div>

    @if ($this->summary->isEmpty())
        <div class="mt-10 max-w-xl border-t border-line pt-10">
            <p class="heading-section">Keranjangmu masih kosong</p>
            <p class="mt-2 text-body text-ink-2">Pilih parfum dari katalog, lalu tambahkan ukuran yang kamu mau.</p>
            <a href="{{ route('catalog') }}" wire:navigate class="btn-primary mt-6">Lihat katalog</a>
        </div>
    @else
        <div class="mt-10 grid gap-12 pb-28 lg:grid-cols-[1fr_360px] lg:pb-0">
            <ul class="divide-y divide-line border-y border-line">
                @foreach ($this->summary->items as $item)
                    @php($variant = $item->variant)
                    <li class="flex gap-4 py-6 sm:gap-6" wire:key="ci-{{ $item->id }}">
                        <a href="{{ route('products.show', $variant->product) }}" wire:navigate class="shrink-0">
                            <x-shop.product-image :product="$variant->product" class="aspect-square w-24 sm:w-28" />
                        </a>
                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="eyebrow">{{ $variant->product->brand->name }}</p>
                                    <a href="{{ route('products.show', $variant->product) }}" wire:navigate class="mt-1 block text-product font-medium text-ink hover:underline">{{ $variant->product->name }}</a>
                                    <p class="mt-0.5 text-label text-ink-2">{{ $variant->label() }} · <span class="tabular-nums">{{ rupiah($variant->price) }}</span></p>
                                </div>
                                <p class="price shrink-0 text-body font-medium text-ink">{{ rupiah($variant->price * $item->qty) }}</p>
                            </div>

                            <div class="mt-auto flex items-center justify-between gap-3 pt-4">
                                <div class="inline-flex h-11 items-center border border-line bg-paper-card">
                                    <button type="button" wire:click="setQty({{ $item->id }}, {{ $item->qty - 1 }})" class="inline-flex h-11 w-10 items-center justify-center text-ink-2 hover:text-ink" aria-label="Kurangi">
                                        <x-lucide-minus class="size-4" stroke-width="1.25" />
                                    </button>
                                    <span class="w-8 text-center text-body tabular-nums">{{ $item->qty }}</span>
                                    <button type="button" wire:click="setQty({{ $item->id }}, {{ $item->qty + 1 }})" class="inline-flex h-11 w-10 items-center justify-center text-ink-2 hover:text-ink disabled:text-ink-faint" aria-label="Tambah" @disabled($item->qty >= $variant->stock)>
                                        <x-lucide-plus class="size-4" stroke-width="1.25" />
                                    </button>
                                </div>
                                <button type="button" wire:click="remove({{ $item->id }})" class="inline-flex h-11 items-center text-label uppercase tracking-label text-ink-2 underline decoration-ink/20 underline-offset-4 hover:text-ink">Hapus</button>
                            </div>

                            @if ($item->problem)
                                <p class="mt-2 text-label text-danger">{{ $item->problem }}</p>
                            @endif
                            @error('qty.'.$item->id)
                                <p class="mt-2 text-label text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </li>
                @endforeach
            </ul>

            <aside class="lg:sticky lg:top-[104px] lg:self-start">
                <div class="bg-paper-card p-6 sm:p-8">
                    <h2 class="eyebrow text-ink">Ringkasan</h2>
                    <dl class="mt-5 space-y-3 text-body">
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-2">Subtotal ({{ $this->summary->itemCount }} barang)</dt>
                            <dd class="price">{{ rupiah($this->summary->subtotal) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-2">Total berat</dt>
                            <dd class="tabular-nums">{{ number_format($this->summary->totalWeight, 0, ',', '.') }} g</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-2">Ongkir</dt>
                            <dd class="text-ink-2">dihitung saat checkout</dd>
                        </div>
                    </dl>
                    <div class="mt-5 flex justify-between gap-4 border-t border-line pt-5 text-product">
                        <span>Total sementara</span>
                        <span class="price font-medium">{{ rupiah($this->summary->subtotal) }}</span>
                    </div>
                    @if ($canCheckout)
                        <a href="{{ route('checkout') }}" wire:navigate class="btn-primary mt-6 hidden h-12 w-full lg:inline-flex">Checkout</a>
                    @else
                        <button type="button" disabled class="btn-primary mt-6 hidden h-12 w-full lg:inline-flex">Checkout</button>
                        <p class="meta mt-2">Perbaiki item yang ditandai merah dulu.</p>
                    @endif
                </div>
            </aside>
        </div>

        {{-- Mobile/tablet: total dan tombol checkout menempel di bawah layar --}}
        <div data-bottom-bar class="fixed inset-x-0 bottom-0 z-20 border-t border-line bg-paper-card px-4 pb-[max(1rem,env(safe-area-inset-bottom))] pt-3 lg:hidden">
            <div class="flex items-center gap-4">
                <div class="min-w-0">
                    <p class="meta">Total sementara</p>
                    <p class="price text-product font-medium">{{ rupiah($this->summary->subtotal) }}</p>
                </div>
                @if ($canCheckout)
                    <a href="{{ route('checkout') }}" wire:navigate class="btn-primary ms-auto h-12 flex-1">Checkout</a>
                @else
                    <button type="button" disabled class="btn-primary ms-auto h-12 flex-1">Checkout</button>
                @endif
            </div>
        </div>
    @endif
</div>
