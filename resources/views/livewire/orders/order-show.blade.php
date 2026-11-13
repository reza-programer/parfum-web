@php
    use App\Enums\OrderStatus;
    $steps = [OrderStatus::PendingPayment, OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Completed];
    $current = array_search($order->status, $steps, true);
    $snap = $order->address_snapshot;
@endphp
<div class="page max-w-5xl pt-4 sm:pt-6">
    <a href="{{ route('orders.index') }}" wire:navigate class="link inline-flex h-10 items-center gap-1.5 text-body">
        <x-lucide-arrow-left class="size-4" stroke-width="2" /> Pesanan saya
    </a>

    <div class="card mt-2 p-4 sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="heading-page tabular-nums">{{ $order->code }}</h1>
            <p class="meta mt-0.5">Dibuat {{ $order->created_at->translatedFormat('j F Y, H:i') }} WIB</p>
        </div>
        <x-shop.order-status :status="$order->status" />
    </div>

    @if (session('order-placed'))
        <p class="mt-4 border border-success/30 bg-success-soft px-4 py-3 text-body text-success" role="status">Pesanan dibuat dan stok sudah kami kunci untukmu.</p>
    @endif
    @if (session('status'))
        <p class="mt-4 border border-success/30 bg-success-soft px-4 py-3 text-body text-success" role="status">{{ session('status') }}</p>
    @endif
    @error('action')
        <p class="mt-4 border border-danger/30 bg-red-50 px-4 py-3 text-body text-danger" role="alert">{{ $message }}</p>
    @enderror

    {{-- Progres --}}
    @if ($order->status !== OrderStatus::Cancelled)
        <ol class="mt-5 grid grid-cols-5" aria-label="Progres pesanan">
            @foreach ($steps as $i => $step)
                <li class="relative flex min-w-0 flex-col items-center text-center">
                    @unless ($loop->first)
                        <span @class(['absolute right-1/2 top-3.5 h-0.5 w-full -translate-y-1/2', 'bg-ink' => $i <= $current, 'bg-line' => $i > $current]) aria-hidden="true"></span>
                    @endunless
                    <span @class(['relative inline-flex size-7 items-center justify-center rounded-full text-label font-bold',
                        'bg-ink text-white' => $i <= $current,
                        'bg-paper text-ink-faint ring-1 ring-line' => $i > $current])>
                        @if ($i < $current)<x-lucide-check class="size-4" stroke-width="2.5" />@else{{ $i + 1 }}@endif
                    </span>
                    <p @class(['mt-1.5 w-full truncate px-0.5 text-caption sm:text-label', 'font-semibold text-ink' => $i === $current, 'text-ink-2' => $i < $current, 'text-ink-faint' => $i > $current])>{{ $step->label() }}</p>
                </li>
            @endforeach
        </ol>
    @endif
    </div>

    @if ($justPaid)
        <p class="mt-4 border border-success/30 bg-success-soft px-4 py-3 text-body text-success" role="status">Pembayaran diterima. Pesananmu segera kami kemas.</p>
    @endif

    {{-- Aksi sesuai status --}}
    @if ($order->status === OrderStatus::PendingPayment)
        @php
            $payment = $this->activePayment;
            $latest = $order->payment;
            $qrExpired = $latest && ! $latest->isVirtualAccount() && ! $latest->isPayable();
            $deadline = $order->expires_at->translatedFormat('j F Y, H:i');
        @endphp
        <div class="card mt-4 p-4 sm:p-6" @if ($payment) wire:poll.5s="checkPayment" @endif>
            @if (session('payment-error') && ! $payment)
                <p class="mb-5 border border-danger/30 bg-red-50 px-4 py-3 text-body text-danger" role="alert">{{ session('payment-error') }} Pilih metode lalu tekan Bayar lagi.</p>
            @endif
            @error('payment')
                <p class="mb-5 border border-danger/30 bg-red-50 px-4 py-3 text-body text-danger" role="alert">{{ $message }}</p>
            @enderror

            @if ($order->expires_at->isPast() && ! $payment)
                <p class="heading-section">Batas waktu bayar sudah lewat</p>
                <p class="mt-1 text-body text-ink-2">Pesanan ini akan dibatalkan otomatis dan stok dilepas.</p>
            @elseif ($payment && ! $choosingMethod)
                @if ($payment->isVirtualAccount())
                    <p class="heading-section">Transfer ke virtual account {{ $payment->bankLabel() }}</p>
                    <p class="mt-1 text-body text-ink-2">Bayar sebelum <span class="text-ink">{{ $payment->expires_at->translatedFormat('j F Y, H:i') }} WIB</span>. Transfer dengan nominal yang persis sama.</p>

                    <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <dt class="meta">Nomor virtual account</dt>
                            <dd class="mt-1 flex flex-wrap items-baseline gap-x-4 gap-y-1">
                                <span class="text-[28px] font-bold leading-none tabular-nums tracking-[0.06em]">{{ trim(chunk_split($payment->va_number, 4, ' ')) }}</span>
                                <x-shop.copy-button :value="$payment->va_number" />
                            </dd>
                        </div>
                        <div>
                            <dt class="meta">Nominal</dt>
                            <dd class="mt-1 flex flex-wrap items-baseline gap-x-4 gap-y-1">
                                <span class="price text-[28px] font-bold leading-none">{{ rupiah($payment->amount) }}</span>
                                <x-shop.copy-button :value="$payment->amount" />
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="heading-section">Scan kode QR</p>
                    <p class="mt-1 text-body text-ink-2">Buka m-banking atau e-wallet, pilih bayar dengan QRIS, lalu scan kode ini.</p>

                    <div class="mt-5 flex flex-wrap items-center gap-6">
                        <div data-qr class="border border-line bg-white p-3 [&_svg]:size-48 sm:[&_svg]:size-56">
                            {!! \App\Support\QrCode::svg($payment->qr_string) !!}
                        </div>
                        <dl class="space-y-3">
                            <div>
                                <dt class="meta">Total</dt>
                                <dd class="price text-[28px] font-bold leading-none">{{ rupiah($payment->amount) }}</dd>
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
                        <a href="{{ $payment->payment_url }}" target="_blank" rel="noopener noreferrer" class="link text-body">Simulasikan pembayaran (sandbox)</a>
                    @endif
                </div>
            @else
                @if ($qrExpired && ! $choosingMethod)
                    <p class="heading-section">Kode QR sudah tidak berlaku</p>
                    <p class="mt-1 text-body text-ink-2">Buat kode baru atau pilih metode lain. Bayar sebelum <span class="text-ink">{{ $deadline }} WIB</span>.</p>
                @else
                    <p class="heading-section">Pilih metode pembayaran</p>
                    <p class="mt-1 text-body text-ink-2">Bayar sebelum <span class="text-ink">{{ $deadline }} WIB</span>. Setelah itu pesanan dibatalkan otomatis dan stok dilepas.</p>
                @endif

                <div class="mt-5">
                    @if ($this->paymentMethods['error'])
                        <div class="flex flex-wrap items-center justify-between gap-3 border border-danger/30 bg-red-50 px-4 py-3">
                            <p class="text-body text-danger">{{ $this->paymentMethods['error'] }}</p>
                            <button type="button" wire:click="$refresh" class="btn-secondary btn-sm">Coba lagi</button>
                        </div>
                    @elseif (empty($this->paymentMethods['methods']))
                        <p class="text-body text-ink-2">Belum ada metode pembayaran untuk total ini. Hubungi kami lewat chat.</p>
                    @else
                        <x-shop.payment-methods :methods="$this->paymentMethods['methods']" :selected="$paymentMethod" />
                    @endif
                    @error('paymentMethod') <p class="mt-2 text-body text-danger">{{ $message }}</p> @enderror
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
        <div class="card mt-4 flex flex-wrap items-center justify-between gap-4 p-4 sm:p-6">
            <div>
                <p class="heading-section">Paket dalam perjalanan</p>
                <p class="mt-1 text-body text-ink-2">Sudah sampai? Tandai diterima supaya kami tahu pesananmu aman.</p>
            </div>
            <button type="button" wire:click="confirmReceived" wire:confirm="Paket sudah kamu terima dengan baik?" class="btn-primary">Pesanan Diterima</button>
        </div>
    @endif

    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div class="card p-4 sm:p-5">
            <h2 class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-nav text-ink"><x-lucide-truck class="size-[18px] text-ink" stroke-width="2" /> Pengiriman</h2>
            <dl class="mt-4 space-y-2.5 text-body">
                <div class="flex justify-between gap-4"><dt class="text-ink-2">Kurir</dt><dd>{{ strtoupper($order->courier) }} {{ $order->courier_service }}</dd></div>
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-2">No. resi</dt>
                    <dd class="tabular-nums">{{ $order->tracking_number ?? 'Belum ada' }}</dd>
                </div>
            </dl>
            <div class="mt-4 border-t border-line pt-4 text-body leading-6">
                <p class="text-ink">{{ $snap['recipient'] ?? '' }} · <span class="tabular-nums">{{ $snap['phone'] ?? '' }}</span></p>
                <p class="text-ink-2">{{ \App\Support\AddressText::fromSnapshot($snap ?? []) }}</p>
            </div>
        </div>

        <div class="card p-4 sm:p-5">
            <h2 class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-nav text-ink"><x-lucide-wallet class="size-[18px] text-ink" stroke-width="2" /> Pembayaran</h2>
            <dl class="mt-4 space-y-2.5 text-body">
                <div class="flex justify-between"><dt class="text-ink-2">Subtotal</dt><dd class="price">{{ rupiah($order->subtotal) }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-2">Ongkir</dt><dd class="price">{{ rupiah($order->shipping_cost) }}</dd></div>
                @if ($order->discount > 0)
                    <div class="flex justify-between"><dt class="text-ink-2">Diskon</dt><dd class="price text-success">−{{ rupiah($order->discount) }}</dd></div>
                @endif
                <div class="flex items-baseline justify-between border-t border-line pt-2.5 text-body"><dt class="font-semibold">Total</dt><dd class="price text-[17px] font-bold text-ink">{{ rupiah($order->total) }}</dd></div>
                @if ($order->payment?->paid_at)
                    <div class="flex justify-between pt-1"><dt class="text-ink-2">Dibayar</dt><dd>{{ $order->payment->methodLabel() }} · {{ $order->payment->paid_at->translatedFormat('j M Y, H:i') }}</dd></div>
                @endif
            </dl>
        </div>
    </div>

    <section class="card mt-4 p-4 sm:p-5">
        <h2 class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-nav text-ink"><x-lucide-package class="size-[18px] text-ink" stroke-width="2" /> Barang</h2>
        <ul class="mt-2 divide-y divide-line">
            @foreach ($order->items as $item)
                <li class="flex flex-wrap items-center gap-3 py-3" wire:key="oi-{{ $item->id }}">
                    @if ($item->variant)
                        <x-shop.product-image :product="$item->variant->product" class="aspect-square w-16 shrink-0 border border-line" />
                    @else
                        <div class="aspect-square w-16 shrink-0 bg-paper-raised"></div>
                    @endif
                    <div class="min-w-0 flex-1">
                        @if ($item->variant)
                            <a href="{{ route('products.show', $item->variant->product) }}" wire:navigate class="text-body font-medium text-ink hover:text-primary">{{ $item->product_name }}</a>
                        @else
                            <p class="text-body">{{ $item->product_name }}</p>
                        @endif
                        <p class="meta tabular-nums">{{ $item->size_ml }}ml · {{ $item->qty }} x {{ rupiah($item->price) }}</p>
                    </div>
                    <p class="price text-body font-semibold">{{ rupiah($item->lineTotal()) }}</p>
                    @if ($order->status === OrderStatus::Completed)
                        <div class="w-full sm:w-auto">
                            @if ($item->review)
                                <span class="inline-flex items-center gap-1 text-label font-medium text-success"><x-lucide-check class="size-3.5" stroke-width="2" /> Sudah diulas</span>
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
