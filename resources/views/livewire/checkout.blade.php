@php
    $radio = 'size-4 shrink-0 border-ink-faint text-primary focus:ring-0 focus:ring-offset-0';
    $canPay = $this->selectedShipping && ! $this->summary->hasProblems;
    $option = 'cursor-pointer rounded-md border transition-colors duration-150 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-primary/40';
@endphp
<div class="page pb-28 pt-4 sm:pt-6 lg:pb-0">
    <h1 class="heading-page">Checkout</h1>

    @error('order')
        <p class="mt-4 rounded-md border border-danger/30 bg-red-50 px-4 py-3 text-body text-danger" role="alert">{{ $message }}</p>
    @enderror

    <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
        <div class="space-y-4">
            {{-- 1. Alamat --}}
            <section class="card p-4 sm:p-5">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="flex items-center gap-2 text-[15px] font-bold text-ink"><x-lucide-map-pin class="size-[18px] text-primary" stroke-width="2" /> Alamat pengiriman</h2>
                    <a href="{{ route('addresses', ['kembali' => 'checkout']) }}" wire:navigate class="link shrink-0 text-body">Kelola alamat</a>
                </div>

                @if ($this->addresses->isEmpty())
                    <div class="mt-4 rounded-md bg-paper p-4">
                        <p class="text-body font-medium text-ink">Belum ada alamat</p>
                        <p class="mt-1 text-body text-ink-2">Tambahkan alamat dulu supaya ongkir bisa dihitung.</p>
                        <a href="{{ route('addresses', ['kembali' => 'checkout']) }}" wire:navigate class="btn-primary mt-3">Tambah alamat</a>
                    </div>
                @else
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach ($this->addresses as $address)
                            <label wire:key="ca-{{ $address->id }}"
                                   @class([$option, 'relative flex gap-3 p-3.5',
                                       'border-primary bg-primary-soft' => $address->id === $addressId,
                                       'border-line bg-paper-card hover:border-ink-faint' => $address->id !== $addressId])>
                                <input type="radio" wire:model.live="addressId" value="{{ $address->id }}" class="{{ $radio }} mt-0.5">
                                <span class="min-w-0">
                                    <span class="flex items-center gap-2 text-body font-semibold text-ink">
                                        {{ $address->label }}
                                        @if ($address->is_default)
                                            <x-ui.badge tone="soft">Utama</x-ui.badge>
                                        @endif
                                    </span>
                                    <span class="mt-1 block text-body text-ink">{{ $address->recipient }} · <span class="tabular-nums">{{ $address->phone }}</span></span>
                                    <span class="mt-1 block text-label leading-5 text-ink-2">{{ $address->fullText() }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
                @error('addressId') <p class="mt-3 text-label text-danger">{{ $message }}</p> @enderror
            </section>

            {{-- 2. Kurir --}}
            <section class="card p-4 sm:p-5">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="flex items-center gap-2 text-[15px] font-bold text-ink"><x-lucide-truck class="size-[18px] text-primary" stroke-width="2" /> Kurir</h2>
                    <p class="meta shrink-0 tabular-nums">Berat {{ number_format($this->summary->totalWeight, 0, ',', '.') }} g</p>
                </div>

                <div class="mt-4" wire:loading.remove wire:target="addressId">
                    @if (! $this->address)
                        <p class="text-body text-ink-2">Pilih alamat untuk melihat ongkir.</p>
                    @elseif ($this->shippingOptions['error'])
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-md border border-danger/30 bg-red-50 px-4 py-3">
                            <p class="text-body text-danger">{{ $this->shippingOptions['error'] }}</p>
                            <button type="button" wire:click="$refresh" class="btn-outline btn-sm">Coba lagi</button>
                        </div>
                    @elseif (empty($this->shippingOptions['options']))
                        <p class="text-body text-ink-2">Belum ada kurir yang melayani alamat ini.</p>
                    @else
                        <div class="space-y-2" role="radiogroup" aria-label="Kurir">
                            @foreach ($this->shippingOptions['options'] as $shipping)
                                <label wire:key="ship-{{ $shipping->key() }}"
                                       @class([$option, 'flex min-h-14 items-center gap-3 px-3.5 py-3',
                                           'border-primary bg-primary-soft' => $shipping->key() === $shippingKey,
                                           'border-line bg-paper-card hover:border-ink-faint' => $shipping->key() !== $shippingKey])>
                                    <input type="radio" wire:model.live="shippingKey" value="{{ $shipping->key() }}" class="{{ $radio }}">
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-body font-medium text-ink">{{ $shipping->courierLabel() }} <span class="font-normal text-ink-2">{{ $shipping->service }}</span></span>
                                        <span class="meta block">{{ $shipping->description }}@if ($shipping->etd) · {{ $shipping->etd }}@endif</span>
                                    </span>
                                    <span class="price shrink-0 text-body font-semibold text-ink">{{ rupiah($shipping->cost) }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                {{-- Skeleton saat alamat diganti dan ongkir dihitung ulang --}}
                <div wire:loading.block wire:target="addressId" class="mt-4 space-y-2" aria-hidden="true">
                    @foreach (range(1, 4) as $i)
                        <div class="flex h-14 items-center gap-4 rounded-md border border-line px-3.5">
                            <x-ui.skeleton class="size-4 rounded-full" />
                            <x-ui.skeleton class="h-3 flex-1 rounded" />
                            <x-ui.skeleton class="h-3 w-16 rounded" />
                        </div>
                    @endforeach
                </div>
                @error('shippingKey') <p class="mt-3 text-label text-danger">{{ $message }}</p> @enderror
            </section>

            {{-- 3. Pembayaran --}}
            <section class="card p-4 sm:p-5">
                <h2 class="flex items-center gap-2 text-[15px] font-bold text-ink"><x-lucide-wallet class="size-[18px] text-primary" stroke-width="2" /> Pembayaran</h2>

                <div class="mt-4">
                    @if ($this->paymentMethods['error'])
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-md border border-danger/30 bg-red-50 px-4 py-3">
                            <p class="text-body text-danger">{{ $this->paymentMethods['error'] }}</p>
                            <button type="button" wire:click="$refresh" class="btn-outline btn-sm">Coba lagi</button>
                        </div>
                    @elseif (empty($this->paymentMethods['methods']))
                        <p class="text-body text-ink-2">Belum ada metode pembayaran untuk total ini.</p>
                    @else
                        <x-shop.payment-methods :methods="$this->paymentMethods['methods']" :selected="$paymentMethod" />
                    @endif
                </div>
                @error('paymentMethod') <p class="mt-3 text-label text-danger">{{ $message }}</p> @enderror
            </section>

            {{-- 4. Barang --}}
            <section class="card p-4 sm:p-5">
                <h2 class="flex items-center gap-2 text-[15px] font-bold text-ink"><x-lucide-package class="size-[18px] text-primary" stroke-width="2" /> Barang</h2>
                <ul class="mt-2 divide-y divide-line">
                    @foreach ($this->summary->items as $item)
                        <li class="flex items-center gap-3 py-3" wire:key="co-{{ $item->id }}">
                            <x-shop.product-image :product="$item->variant->product" class="aspect-square w-14 shrink-0 rounded-md border border-line" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-body font-medium text-ink">{{ $item->variant->product->name }}</p>
                                <p class="meta tabular-nums">{{ $item->variant->label() }} · {{ $item->qty }} x {{ rupiah($item->variant->price) }}</p>
                            </div>
                            <p class="price shrink-0 text-body font-semibold text-ink">{{ rupiah($item->variant->price * $item->qty) }}</p>
                        </li>
                    @endforeach
                </ul>
                @if ($this->summary->hasProblems)
                    <p class="mt-2 rounded-md bg-red-50 px-3 py-2 text-label text-danger">Ada barang yang stoknya berubah. <a href="{{ route('cart') }}" wire:navigate class="font-semibold underline">Cek keranjang</a></p>
                @endif
            </section>
        </div>

        {{-- Ringkasan --}}
        <aside class="card p-4 sm:p-5 lg:sticky lg:top-[calc(var(--header-h)+1rem)]">
            <h2 class="text-[15px] font-bold text-ink">Ringkasan belanja</h2>

            <div class="mt-4">
                @if ($voucherCode && $this->voucher)
                    <div class="flex items-center justify-between rounded-md border border-dashed border-primary bg-primary-soft px-3 py-2.5">
                        <span class="inline-flex items-center gap-2 text-body font-semibold uppercase text-primary"><x-lucide-ticket-percent class="size-4" stroke-width="2" /> {{ $voucherCode }}</span>
                        <button type="button" wire:click="removeVoucher" class="link text-label">Hapus</button>
                    </div>
                @else
                    <form wire:submit="applyVoucher" class="flex gap-2">
                        <label for="voucher" class="sr-only">Kode voucher</label>
                        <input id="voucher" type="text" wire:model="voucherInput" placeholder="Kode voucher" class="field uppercase placeholder:normal-case" autocomplete="off">
                        <button type="submit" class="btn-secondary shrink-0 px-4">Pakai</button>
                    </form>
                @endif
                @error('voucherInput') <p class="mt-2 text-label text-danger">{{ $message }}</p> @enderror
            </div>

            <dl class="mt-4 space-y-2.5 border-t border-line pt-4 text-body">
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-2">Subtotal</dt>
                    <dd class="price text-ink">{{ rupiah($this->summary->subtotal) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-2">Ongkir</dt>
                    <dd class="price text-ink">{{ $this->selectedShipping ? rupiah($this->selectedShipping->cost) : '–' }}</dd>
                </div>
                @if ($this->discount > 0)
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-2">Diskon</dt>
                        <dd class="price text-success">−{{ rupiah($this->discount) }}</dd>
                    </div>
                @endif
                <div class="flex items-baseline justify-between gap-4 border-t border-line pt-3">
                    <dt class="font-semibold text-ink">Total</dt>
                    <dd class="price text-[18px] font-bold text-primary" data-testid="total">{{ rupiah($this->total) }}</dd>
                </div>
            </dl>

            <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder"
                    class="btn-primary mt-4 hidden w-full lg:inline-flex" @disabled(! $canPay)>
                <span wire:loading.remove wire:target="placeOrder">Bayar Sekarang</span>
                <span wire:loading wire:target="placeOrder">Memproses</span>
            </button>
            <p class="meta mt-3">Stok dikunci setelah pesanan dibuat. Selesaikan pembayaran dalam 24 jam.</p>
        </aside>
    </div>

    {{-- HP/tablet: total dan tombol bayar menempel di bawah, mudah dijangkau satu tangan --}}
    <div data-bottom-bar class="fixed inset-x-0 bottom-0 z-20 border-t border-line bg-paper-card px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 lg:hidden">
        <div class="mx-auto flex max-w-page items-center gap-4">
            <div class="min-w-0">
                <p class="meta">Total</p>
                <p class="price text-[17px] font-bold text-primary">{{ rupiah($this->total) }}</p>
            </div>
            <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder"
                    class="btn-primary ms-auto flex-1" @disabled(! $canPay)>
                <span wire:loading.remove wire:target="placeOrder">Bayar Sekarang</span>
                <span wire:loading wire:target="placeOrder">Memproses</span>
            </button>
        </div>
    </div>
</div>
