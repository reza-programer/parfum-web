<div class="page pt-4 sm:pt-6">
    <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
        <h1 class="heading-page">Keranjang</h1>
        @unless ($this->summary->isEmpty())
            <p class="meta tabular-nums">{{ $this->summary->itemCount }} barang</p>
        @endunless
    </div>

    @if ($this->summary->isEmpty())
        <div class="card mt-4 flex flex-col items-center px-6 py-14 text-center">
            <span class="inline-flex size-16 items-center justify-center rounded-full bg-primary-soft text-primary">
                <x-lucide-shopping-cart class="size-7" stroke-width="1.75" />
            </span>
            <p class="mt-4 text-[16px] font-bold text-ink">Keranjangmu masih kosong</p>
            <p class="mt-1 max-w-sm text-body text-ink-2">Pilih parfum dari katalog, lalu tambahkan ukuran yang kamu mau.</p>
            <a href="{{ route('catalog') }}" wire:navigate class="btn-primary mt-5">Lihat katalog</a>
        </div>
    @else
        <div class="mt-4 grid gap-4 pb-28 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start lg:pb-0">
            <ul class="card divide-y divide-line">
                @foreach ($this->summary->items as $item)
                    @php($variant = $item->variant)
                    <li class="flex gap-3 p-4 sm:gap-4" wire:key="ci-{{ $item->id }}">
                        <a href="{{ route('products.show', $variant->product) }}" wire:navigate class="shrink-0">
                            <x-shop.product-image :product="$variant->product" class="aspect-square w-20 rounded-md border border-line sm:w-24" />
                        </a>
                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('products.show', $variant->product) }}" wire:navigate class="line-clamp-2 text-body font-medium text-ink hover:text-primary">{{ $variant->product->name }}</a>
                                    <p class="mt-0.5 text-label text-ink-2">{{ $variant->product->brand->name }} · {{ $variant->label() }}</p>
                                    <p class="price mt-1 text-body font-bold text-ink">{{ rupiah($variant->price) }}</p>
                                </div>
                            </div>

                            <div class="mt-auto flex items-center justify-end gap-2 pt-3">
                                <button type="button" wire:click="remove({{ $item->id }})" class="inline-flex size-10 items-center justify-center rounded-md text-ink-2 transition-colors duration-150 hover:bg-paper hover:text-danger" aria-label="Hapus {{ $variant->product->name }}">
                                    <x-lucide-trash-2 class="size-[18px]" stroke-width="1.75" />
                                </button>
                                <div class="inline-flex h-10 items-center rounded-md border border-line bg-paper-card">
                                    <button type="button" wire:click="setQty({{ $item->id }}, {{ $item->qty - 1 }})" class="inline-flex size-10 items-center justify-center text-primary disabled:text-ink-faint" aria-label="Kurangi" @disabled($item->qty <= 1)>
                                        <x-lucide-minus class="size-4" stroke-width="2.25" />
                                    </button>
                                    <span class="w-8 text-center text-body font-medium tabular-nums">{{ $item->qty }}</span>
                                    <button type="button" wire:click="setQty({{ $item->id }}, {{ $item->qty + 1 }})" class="inline-flex size-10 items-center justify-center text-primary disabled:text-ink-faint" aria-label="Tambah" @disabled($item->qty >= $variant->stock)>
                                        <x-lucide-plus class="size-4" stroke-width="2.25" />
                                    </button>
                                </div>
                            </div>

                            @if ($item->problem)
                                <p class="mt-2 rounded-md bg-red-50 px-2.5 py-1.5 text-label text-danger">{{ $item->problem }}</p>
                            @endif
                            @error('qty.'.$item->id)
                                <p class="mt-2 rounded-md bg-red-50 px-2.5 py-1.5 text-label text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </li>
                @endforeach
            </ul>

            <aside class="card p-4 sm:p-5 lg:sticky lg:top-[calc(var(--header-h)+1rem)]">
                <h2 class="text-[15px] font-bold text-ink">Ringkasan belanja</h2>
                <dl class="mt-4 space-y-2.5 text-body">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-2">Subtotal ({{ $this->summary->itemCount }} barang)</dt>
                        <dd class="price text-ink">{{ rupiah($this->summary->subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-2">Total berat</dt>
                        <dd class="tabular-nums text-ink">{{ number_format($this->summary->totalWeight, 0, ',', '.') }} g</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-2">Ongkir</dt>
                        <dd class="text-ink-2">dihitung saat checkout</dd>
                    </div>
                </dl>
                <div class="mt-4 flex items-baseline justify-between gap-4 border-t border-line pt-4">
                    <span class="text-body font-semibold text-ink">Total sementara</span>
                    <span class="price text-[18px] font-bold text-primary">{{ rupiah($this->summary->subtotal) }}</span>
                </div>
                @if ($canCheckout)
                    <a href="{{ route('checkout') }}" wire:navigate class="btn-primary mt-4 hidden w-full lg:inline-flex">Checkout</a>
                @else
                    <button type="button" disabled class="btn-primary mt-4 hidden w-full lg:inline-flex">Checkout</button>
                    <p class="meta mt-2">Perbaiki item yang ditandai merah dulu.</p>
                @endif
            </aside>
        </div>

        {{-- HP/tablet: total dan tombol checkout menempel di bawah layar --}}
        <div data-bottom-bar class="fixed inset-x-0 bottom-0 z-20 border-t border-line bg-paper-card px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 lg:hidden">
            <div class="mx-auto flex max-w-page items-center gap-4">
                <div class="min-w-0">
                    <p class="meta">Total sementara</p>
                    <p class="price text-[17px] font-bold text-primary">{{ rupiah($this->summary->subtotal) }}</p>
                </div>
                @if ($canCheckout)
                    <a href="{{ route('checkout') }}" wire:navigate class="btn-primary ms-auto flex-1">Checkout ({{ $this->summary->itemCount }})</a>
                @else
                    <button type="button" disabled class="btn-primary ms-auto flex-1">Checkout</button>
                @endif
            </div>
        </div>
    @endif
</div>
