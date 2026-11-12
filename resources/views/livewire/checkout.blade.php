@php
    $radio = 'size-4 shrink-0 border-ink/40 text-ink focus:ring-0 focus:ring-offset-0';
    $canPay = $this->selectedShipping && ! $this->summary->hasProblems;
@endphp
<div class="page pb-28 pt-10 lg:pb-0 lg:pt-14">
    <p class="eyebrow">Belanja</p>
    <h1 class="heading-page mt-3">Checkout</h1>

    @error('order')
        <p class="mt-6 border-l-2 border-danger bg-danger/5 px-4 py-3 text-body text-danger" role="alert">{{ $message }}</p>
    @enderror

    <div class="mt-10 grid gap-12 lg:grid-cols-[1fr_380px] lg:gap-16">
        <div class="space-y-14">
            {{-- 1. Alamat --}}
            <section>
                <div class="flex items-baseline justify-between gap-4 border-b border-line pb-4">
                    <h2 class="flex items-baseline gap-3"><span class="text-caption tabular-nums text-ink-2">01</span><span class="heading-section">Alamat pengiriman</span></h2>
                    <a href="{{ route('addresses', ['kembali' => 'checkout']) }}" wire:navigate class="link shrink-0 text-label">Kelola alamat</a>
                </div>

                @if ($this->addresses->isEmpty())
                    <div class="mt-6">
                        <p class="text-body text-ink">Belum ada alamat</p>
                        <p class="mt-1 text-body text-ink-2">Tambahkan alamat dulu supaya ongkir bisa dihitung.</p>
                        <a href="{{ route('addresses', ['kembali' => 'checkout']) }}" wire:navigate class="btn-primary mt-4">Tambah alamat</a>
                    </div>
                @else
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach ($this->addresses as $address)
                            <label wire:key="ca-{{ $address->id }}"
                                   @class(['relative flex cursor-pointer gap-3 border bg-paper-card p-4 transition-colors duration-150 has-[:focus-visible]:ring-1 has-[:focus-visible]:ring-ink',
                                       'border-ink' => $address->id === $addressId,
                                       'border-line hover:border-ink/40' => $address->id !== $addressId])>
                                <input type="radio" wire:model.live="addressId" value="{{ $address->id }}" class="{{ $radio }} mt-0.5">
                                <span class="min-w-0">
                                    <span class="flex items-center gap-2 text-body font-medium text-ink">
                                        {{ $address->label }}
                                        @if ($address->is_default)
                                            <x-ui.badge class="h-5 bg-ink/[0.06] text-ink">Utama</x-ui.badge>
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
            <section>
                <div class="flex items-baseline justify-between gap-4 border-b border-line pb-4">
                    <h2 class="flex items-baseline gap-3"><span class="text-caption tabular-nums text-ink-2">02</span><span class="heading-section">Kurir</span></h2>
                    <p class="meta shrink-0 tabular-nums">Berat {{ number_format($this->summary->totalWeight, 0, ',', '.') }} g</p>
                </div>

                <div wire:loading.remove wire:target="addressId">
                    @if (! $this->address)
                        <p class="pt-6 text-body text-ink-2">Pilih alamat untuk melihat ongkir.</p>
                    @elseif ($this->shippingOptions['error'])
                        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-l-2 border-danger bg-danger/5 px-4 py-3">
                            <p class="text-body text-danger">{{ $this->shippingOptions['error'] }}</p>
                            <button type="button" wire:click="$refresh" class="btn-secondary btn-sm">Coba lagi</button>
                        </div>
                    @elseif (empty($this->shippingOptions['options']))
                        <p class="pt-6 text-body text-ink-2">Belum ada kurir yang melayani alamat ini.</p>
                    @else
                        <div class="divide-y divide-line border-b border-line" role="radiogroup" aria-label="Kurir">
                            @foreach ($this->shippingOptions['options'] as $option)
                                <label wire:key="ship-{{ $option->key() }}"
                                       @class(['flex min-h-14 cursor-pointer items-center gap-4 px-4 py-3 transition-colors duration-150 has-[:focus-visible]:ring-1 has-[:focus-visible]:ring-inset has-[:focus-visible]:ring-ink',
                                           'bg-paper-card' => $option->key() === $shippingKey,
                                           'hover:bg-paper-card/60' => $option->key() !== $shippingKey])>
                                    <input type="radio" wire:model.live="shippingKey" value="{{ $option->key() }}" class="{{ $radio }}">
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-body text-ink">{{ $option->courierLabel() }} <span class="text-ink-2">{{ $option->service }}</span></span>
                                        <span class="meta block">{{ $option->description }}@if ($option->etd) · {{ $option->etd }}@endif</span>
                                    </span>
                                    <span class="price shrink-0 text-body text-ink">{{ rupiah($option->cost) }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                {{-- Skeleton saat alamat diganti dan ongkir dihitung ulang --}}
                <div wire:loading.block wire:target="addressId" class="divide-y divide-line border-b border-line" aria-hidden="true">
                    @foreach (range(1, 4) as $i)
                        <div class="flex h-14 items-center gap-4 px-4">
                            <x-ui.skeleton class="size-4" />
                            <x-ui.skeleton class="h-3 flex-1" />
                            <x-ui.skeleton class="h-3 w-16" />
                        </div>
                    @endforeach
                </div>
                @error('shippingKey') <p class="mt-3 text-label text-danger">{{ $message }}</p> @enderror
            </section>

            {{-- 3. Pembayaran --}}
            <section>
                <div class="border-b border-line pb-4">
                    <h2 class="flex items-baseline gap-3"><span class="text-caption tabular-nums text-ink-2">03</span><span class="heading-section">Pembayaran</span></h2>
                </div>

                <div class="mt-6">
                    @if ($this->paymentMethods['error'])
                        <div class="flex flex-wrap items-center justify-between gap-3 border-l-2 border-danger bg-danger/5 px-4 py-3">
                            <p class="text-body text-danger">{{ $this->paymentMethods['error'] }}</p>
                            <button type="button" wire:click="$refresh" class="btn-secondary btn-sm">Coba lagi</button>
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
            <section>
                <div class="border-b border-line pb-4">
                    <h2 class="flex items-baseline gap-3"><span class="text-caption tabular-nums text-ink-2">04</span><span class="heading-section">Barang</span></h2>
                </div>
                <ul class="divide-y divide-line border-b border-line">
                    @foreach ($this->summary->items as $item)
                        <li class="flex items-center gap-4 py-4" wire:key="co-{{ $item->id }}">
                            <x-shop.product-image :product="$item->variant->product" class="aspect-square w-16 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-body text-ink">{{ $item->variant->product->name }}</p>
                                <p class="meta tabular-nums">{{ $item->variant->label() }} · {{ $item->qty }} x {{ rupiah($item->variant->price) }}</p>
                            </div>
                            <p class="price shrink-0 text-body text-ink">{{ rupiah($item->variant->price * $item->qty) }}</p>
                        </li>
                    @endforeach
                </ul>
                @if ($this->summary->hasProblems)
                    <p class="mt-3 text-label text-danger">Ada barang yang stoknya berubah. <a href="{{ route('cart') }}" wire:navigate class="underline">Cek keranjang</a></p>
                @endif
            </section>
        </div>

        {{-- Ringkasan --}}
        <aside class="lg:sticky lg:top-[104px] lg:self-start">
            <div class="bg-paper-card p-6 sm:p-8">
                <h2 class="eyebrow text-ink">Ringkasan</h2>

                <div class="mt-5">
                    @if ($voucherCode && $this->voucher)
                        <div class="flex items-center justify-between border border-ink px-3 py-2.5">
                            <span class="text-label font-medium uppercase tracking-label text-ink">{{ $voucherCode }}</span>
                            <button type="button" wire:click="removeVoucher" class="text-label text-ink-2 underline underline-offset-4 hover:text-ink">Hapus</button>
                        </div>
                    @else
                        <form wire:submit="applyVoucher" class="flex">
                            <label for="voucher" class="sr-only">Kode voucher</label>
                            <input id="voucher" type="text" wire:model="voucherInput" placeholder="Kode voucher" class="field uppercase placeholder:normal-case" autocomplete="off">
                            <button type="submit" class="btn-secondary -ml-px shrink-0 px-4">Pakai</button>
                        </form>
                    @endif
                    @error('voucherInput') <p class="mt-2 text-label text-danger">{{ $message }}</p> @enderror
                </div>

                <dl class="mt-6 space-y-3 border-t border-line pt-5 text-body">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-2">Subtotal</dt>
                        <dd class="price">{{ rupiah($this->summary->subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-2">Ongkir</dt>
                        <dd class="price">{{ $this->selectedShipping ? rupiah($this->selectedShipping->cost) : '–' }}</dd>
                    </div>
                    @if ($this->discount > 0)
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-2">Diskon</dt>
                            <dd class="price text-ink">−{{ rupiah($this->discount) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4 border-t border-line pt-4 text-product">
                        <dt class="text-ink">Total</dt>
                        <dd class="price font-medium text-ink" data-testid="total">{{ rupiah($this->total) }}</dd>
                    </div>
                </dl>

                <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder"
                        class="btn-primary mt-6 hidden h-12 w-full lg:inline-flex" @disabled(! $canPay)>
                    <span wire:loading.remove wire:target="placeOrder">Bayar Sekarang</span>
                    <span wire:loading wire:target="placeOrder">Memproses</span>
                </button>
                <p class="meta mt-4">Stok dikunci setelah pesanan dibuat. Selesaikan pembayaran dalam 24 jam.</p>
            </div>
        </aside>
    </div>

    {{-- Mobile/tablet: total dan tombol bayar menempel di bawah, mudah dijangkau satu tangan --}}
    <div data-bottom-bar class="fixed inset-x-0 bottom-0 z-20 border-t border-line bg-paper-card px-4 pb-[max(1rem,env(safe-area-inset-bottom))] pt-3 lg:hidden">
        <div class="flex items-center gap-4">
            <div class="min-w-0">
                <p class="meta">Total</p>
                <p class="price text-product font-medium">{{ rupiah($this->total) }}</p>
            </div>
            <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder"
                    class="btn-primary ms-auto h-12 flex-1" @disabled(! $canPay)>
                <span wire:loading.remove wire:target="placeOrder">Bayar Sekarang</span>
                <span wire:loading wire:target="placeOrder">Memproses</span>
            </button>
        </div>
    </div>
</div>
