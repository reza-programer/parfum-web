@php
    use App\Enums\OrderStatus;
    $steps = [OrderStatus::PendingPayment, OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Completed];
    $current = array_search($order->status, $steps, true);
    $snap = $order->address_snapshot;
@endphp
<div class="page max-w-4xl pt-10">
    <a href="{{ route('orders.index') }}" wire:navigate class="meta inline-flex items-center gap-1 hover:text-ink">
        <x-lucide-arrow-left class="size-3.5" stroke-width="1.5" /> Pesanan saya
    </a>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-serif text-4xl tabular-nums">{{ $order->code }}</h1>
            <p class="meta mt-1">Dibuat {{ $order->created_at->translatedFormat('j F Y, H:i') }} WIB</p>
        </div>
        <x-shop.order-status :status="$order->status" class="h-8 px-3 text-sm" />
    </div>

    @if (session('order-placed'))
        <p class="mt-6 rounded-lg bg-paper-card border border-line px-4 py-3 text-sm text-ink" role="status">Pesanan dibuat dan stok sudah kami kunci untukmu.</p>
    @endif
    @if (session('status'))
        <p class="mt-6 rounded-lg bg-paper-card border border-line px-4 py-3 text-sm text-ink" role="status">{{ session('status') }}</p>
    @endif
    @error('action')
        <p class="mt-6 rounded-lg bg-danger/5 px-4 py-3 text-sm text-danger" role="alert">{{ $message }}</p>
    @enderror

    {{-- Progres --}}
    @if ($order->status !== OrderStatus::Cancelled)
        <ol class="mt-8 grid grid-cols-5 gap-2" aria-label="Progres pesanan">
            @foreach ($steps as $i => $step)
                <li class="min-w-0">
                    <div @class(['h-1 rounded-full', 'bg-primary' => $i <= $current, 'bg-ink/10' => $i > $current])></div>
                    <p @class(['mt-2 truncate text-[13px]', 'text-ink' => $i === $current, 'text-ink-2' => $i < $current, 'text-ink-3' => $i > $current])>{{ $step->label() }}</p>
                </li>
            @endforeach
        </ol>
    @endif

    @if ($justPaid)
        <p class="mt-6 rounded-lg bg-paper-card border border-line px-4 py-3 text-sm text-ink" role="status">Pembayaran diterima. Pesananmu segera kami kemas.</p>
    @endif

    {{-- Aksi sesuai status --}}
    @if ($order->status === OrderStatus::PendingPayment)
        @php
            $payment = $this->activePayment;
            $latest = $order->payment;
            $qrExpired = $latest && ! $latest->isVirtualAccount() && ! $latest->isPayable();
            $deadline = $order->expires_at->translatedFormat('j F Y, H:i');
        @endphp
        <div class="card mt-8 p-6" @if ($payment) wire:poll.5s="checkPayment" @endif>
            @if (session('payment-error') && ! $payment)
                <p class="mb-5 rounded-lg bg-danger/5 px-4 py-3 text-sm text-danger" role="alert">{{ session('payment-error') }} Pilih metode lalu tekan Bayar lagi.</p>
            @endif
            @error('payment')
                <p class="mb-5 rounded-lg bg-danger/5 px-4 py-3 text-sm text-danger" role="alert">{{ $message }}</p>
            @enderror

            @if ($order->expires_at->isPast() && ! $payment)
                <p class="text-body font-medium">Batas waktu bayar sudah lewat</p>
                <p class="mt-1 text-sm text-ink-2">Pesanan ini akan dibatalkan otomatis dan stok dilepas.</p>
            @elseif ($payment && ! $choosingMethod)
                @if ($payment->isVirtualAccount())
                    <p class="text-body font-medium">Transfer ke virtual account {{ $payment->bankLabel() }}</p>
                    <p class="mt-1 text-sm text-ink-2">Bayar sebelum <span class="text-ink">{{ $payment->expires_at->translatedFormat('j F Y, H:i') }} WIB</span>. Transfer dengan nominal yang persis sama.</p>

                    <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <dt class="meta">Nomor virtual account</dt>
                            <dd class="mt-1 flex flex-wrap items-baseline gap-x-4 gap-y-1">
                                <span class="text-2xl tabular-nums tracking-wide">{{ trim(chunk_split($payment->va_number, 4, ' ')) }}</span>
                                <x-shop.copy-button :value="$payment->va_number" />
                            </dd>
                        </div>
                        <div>
                            <dt class="meta">Nominal</dt>
                            <dd class="mt-1 flex flex-wrap items-baseline gap-x-4 gap-y-1">
                                <span class="price text-2xl">{{ rupiah($payment->amount) }}</span>
                                <x-shop.copy-button :value="$payment->amount" />
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="text-body font-medium">Scan kode QR</p>
                    <p class="mt-1 text-sm text-ink-2">Buka m-banking atau e-wallet, pilih bayar dengan QRIS, lalu scan kode ini.</p>

                    <div class="mt-5 flex flex-wrap items-center gap-6">
                        <div data-qr class="rounded-xl border border-line bg-white p-3 [&_svg]:size-48 sm:[&_svg]:size-56">
                            {!! \App\Support\QrCode::svg($payment->qr_string) !!}
                        </div>
                        <dl class="space-y-3">
                            <div>
                                <dt class="meta">Total</dt>
                                <dd class="price text-2xl">{{ rupiah($payment->amount) }}</dd>
                            </div>
                            <div>
                                <dt class="meta">Berlaku</dt>
                                <dd class="text-body tabular-nums" wire:key="qr-left-{{ $payment->id }}" wire:ignore
                                    x-data="{ left: {{ max(0, (int) now()->diffInSeconds($payment->expires_at)) }} }"
                                    x-init="const start = Date.now(), total = left; const t = setInterval(() => { left = Math.max(0, total - Math.floor((Date.now() - start) / 1000)); if (left === 0) { clearInterval(t); $wire.$refresh() } }, 1000)"
                                    x-text="Math.floor(left / 60) + ':' + String(left % 60).padStart(2, '0')">
                                    {{ $payment->expires_at->translatedFormat('H:i') }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <p class="meta mt-5">Status pembayaran diperbarui otomatis di halaman ini.</p>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="button" wire:click="changeMethod" class="btn-secondary btn-sm">Ganti metode</button>
                    <button type="button" wire:click="cancel" wire:confirm="Batalkan pesanan {{ $order->code }}?" class="btn-secondary btn-sm">Batalkan pesanan</button>
                    @if ($this->isSandbox && $payment->payment_url)
                        <a href="{{ $payment->payment_url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-ink-2 underline decoration-ink/20 underline-offset-4 hover:text-ink">Simulasikan pembayaran (sandbox)</a>
                    @endif
                </div>
            @else
                @if ($qrExpired && ! $choosingMethod)
                    <p class="text-body font-medium">Kode QR sudah tidak berlaku</p>
                    <p class="mt-1 text-sm text-ink-2">Buat kode baru atau pilih metode lain. Bayar sebelum <span class="text-ink">{{ $deadline }} WIB</span>.</p>
                @else
                    <p class="text-body font-medium">Pilih metode pembayaran</p>
                    <p class="mt-1 text-sm text-ink-2">Bayar sebelum <span class="text-ink">{{ $deadline }} WIB</span>. Setelah itu pesanan dibatalkan otomatis dan stok dilepas.</p>
                @endif

                <div class="mt-5">
                    @if ($this->paymentMethods['error'])
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-paper-raised px-4 py-3">
                            <p class="text-sm text-danger">{{ $this->paymentMethods['error'] }}</p>
                            <button type="button" wire:click="$refresh" class="btn-secondary btn-sm">Coba lagi</button>
                        </div>
                    @elseif (empty($this->paymentMethods['methods']))
                        <p class="text-sm text-ink-2">Belum ada metode pembayaran untuk total ini. Hubungi kami lewat chat.</p>
                    @else
                        <x-shop.payment-methods :methods="$this->paymentMethods['methods']" :selected="$paymentMethod" />
                    @endif
                    @error('paymentMethod') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <button type="button" wire:click="pay" wire:loading.attr="disabled" wire:target="pay" class="btn-primary">
                        <span wire:loading.remove wire:target="pay">{{ $qrExpired && $paymentMethod === 'qris' && ! $choosingMethod ? 'Buat QR baru' : 'Bayar '.rupiah($order->total) }}</span>
                        <span wire:loading wire:target="pay">Memproses</span>
                    </button>
                    @if ($choosingMethod && $payment)
                        <button type="button" wire:click="cancelChange" class="btn-secondary">Kembali</button>
                    @else
                        <button type="button" wire:click="cancel" wire:confirm="Batalkan pesanan {{ $order->code }}?" class="btn-secondary">Batalkan pesanan</button>
                    @endif
                </div>
            @endif
        </div>
    @elseif ($order->status === OrderStatus::Shipped)
        <div class="card mt-8 flex flex-wrap items-center justify-between gap-4 p-6">
            <div>
                <p class="text-body font-medium">Paket dalam perjalanan</p>
                <p class="mt-1 text-sm text-ink-2">Sudah sampai? Tandai diterima supaya kami tahu pesananmu aman.</p>
            </div>
            <button type="button" wire:click="confirmReceived" wire:confirm="Paket sudah kamu terima dengan baik?" class="btn-primary">Pesanan Diterima</button>
        </div>
    @endif

    <div class="mt-8 grid gap-6 md:grid-cols-2">
        <div class="card p-6">
            <h2 class="text-body font-medium">Pengiriman</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-ink-2">Kurir</dt><dd>{{ strtoupper($order->courier) }} {{ $order->courier_service }}</dd></div>
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-2">No. resi</dt>
                    <dd class="tabular-nums">{{ $order->tracking_number ?? 'Belum ada' }}</dd>
                </div>
            </dl>
            <div class="mt-4 border-t border-line pt-4 text-sm leading-6">
                <p class="text-ink">{{ $snap['recipient'] ?? '' }} · <span class="tabular-nums">{{ $snap['phone'] ?? '' }}</span></p>
                <p class="text-ink-2">{{ $snap['detail'] ?? '' }}, {{ region_name($snap['district_name'] ?? '') }}, {{ region_name($snap['city_name'] ?? '') }}, {{ region_name($snap['province_name'] ?? '') }} {{ $snap['postal_code'] ?? '' }}</p>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-body font-medium">Pembayaran</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-2">Subtotal</dt><dd class="price">{{ rupiah($order->subtotal) }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-2">Ongkir</dt><dd class="price">{{ rupiah($order->shipping_cost) }}</dd></div>
                @if ($order->discount > 0)
                    <div class="flex justify-between"><dt class="text-ink-2">Diskon</dt><dd class="price text-ink">−{{ rupiah($order->discount) }}</dd></div>
                @endif
                <div class="flex justify-between border-t border-line pt-2 text-body"><dt>Total</dt><dd class="price font-medium">{{ rupiah($order->total) }}</dd></div>
                @if ($order->payment?->paid_at)
                    <div class="flex justify-between pt-1"><dt class="text-ink-2">Dibayar</dt><dd>{{ $order->payment->methodLabel() }} · {{ $order->payment->paid_at->translatedFormat('j M Y, H:i') }}</dd></div>
                @endif
            </dl>
        </div>
    </div>

    <section class="mt-8">
        <h2 class="text-lg font-medium">Barang</h2>
        <ul class="mt-4 divide-y divide-line border-y border-line">
            @foreach ($order->items as $item)
                <li class="flex flex-wrap items-center gap-4 py-4" wire:key="oi-{{ $item->id }}">
                    @if ($item->variant)
                        <x-shop.product-image :product="$item->variant->product" class="aspect-square w-14 shrink-0 rounded-lg" />
                    @else
                        <div class="aspect-square w-14 shrink-0 rounded-lg bg-paper-raised"></div>
                    @endif
                    <div class="min-w-0 flex-1">
                        @if ($item->variant)
                            <a href="{{ route('products.show', $item->variant->product) }}" wire:navigate class="text-body hover:text-primary">{{ $item->product_name }}</a>
                        @else
                            <p class="text-body">{{ $item->product_name }}</p>
                        @endif
                        <p class="meta tabular-nums">{{ $item->size_ml }}ml · {{ $item->qty }} x {{ rupiah($item->price) }}</p>
                    </div>
                    <p class="price text-body">{{ rupiah($item->lineTotal()) }}</p>
                    @if ($order->status === OrderStatus::Completed)
                        <div class="w-full sm:w-auto">
                            @if ($item->review)
                                <span class="meta inline-flex items-center gap-1"><x-lucide-check class="size-3.5" stroke-width="1.5" /> Sudah diulas</span>
                            @elseif ($item->variant)
                                <livewire:reviews.review-form :order-item-id="$item->id" :key="'rf-'.$item->id" />
                            @endif
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>
</div>
