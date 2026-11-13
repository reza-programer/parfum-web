@php
    $summary = function ($order) {
        $first = $order->items->first();

        return $first ? $first->product_name.' '.$first->size_ml.'ml' : '–';
    };
@endphp
<div class="page max-w-4xl pt-4 sm:pt-6">
    @include('partials.account-nav')

    <h1 class="heading-page mt-5">Pesanan saya</h1>

    {{-- Filter status --}}
    <div class="no-scrollbar mt-3 flex gap-1.5 overflow-x-auto" role="tablist" aria-label="Status pesanan">
        @foreach (\App\Livewire\Orders\OrderIndex::TABS as $value => $label)
            <button type="button" wire:click="$set('status', '{{ $value }}')" role="tab" aria-selected="{{ $status === $value ? 'true' : 'false' }}"
                @class(['chip shrink-0', 'chip-active' => $status === $value])>{{ $label }}</button>
        @endforeach
    </div>

    <div class="mt-3 transition-opacity duration-150" wire:loading.class="opacity-50">
        @if ($orders->isEmpty())
            <div class="card flex flex-col items-center px-6 py-14 text-center">
                <span class="inline-flex size-16 items-center justify-center rounded-full bg-paper-raised text-ink">
                    <x-lucide-package class="size-7" stroke-width="1.75" />
                </span>
                <p class="mt-4 text-[16px] font-bold text-ink">Belum ada pesanan</p>
                <p class="mt-1 text-body text-ink-2">Pesanan yang kamu buat akan muncul di sini.</p>
                <a href="{{ route('catalog') }}" wire:navigate class="btn-primary mt-5">Lihat katalog</a>
            </div>
        @else
            <ul class="space-y-3">
                @foreach ($orders as $order)
                    @php
                        $first = $order->items->first();
                        $more = $order->items->count() - 1;
                    @endphp
                    <li wire:key="o-{{ $order->id }}" class="card relative transition-shadow duration-200">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 border-b border-line px-4 py-2.5 text-label text-ink-2">
                            <x-lucide-shopping-bag class="size-4 text-ink" stroke-width="2" />
                            <span class="font-semibold text-ink">Belanja</span>
                            <span class="tabular-nums">{{ $order->created_at->translatedFormat('j M Y, H:i') }}</span>
                            <x-shop.order-status :status="$order->status" />
                            <span class="tabular-nums max-sm:basis-full sm:ms-auto">{{ $order->code }}</span>
                        </div>
                        <div class="flex items-center gap-3 p-4">
                            @if ($first?->variant?->product)
                                <x-shop.product-image :product="$first->variant->product" class="aspect-square w-14 shrink-0 border border-line" />
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-body font-semibold text-ink">{{ $summary($order) }}</p>
                                <p class="meta">{{ $first ? $first->qty.' barang x '.rupiah($first->price) : '' }}</p>
                                @if ($more > 0)
                                    <p class="meta mt-0.5">+{{ $more }} produk lainnya</p>
                                @endif
                            </div>
                            <div class="shrink-0 text-right max-sm:hidden">
                                <p class="meta">Total belanja</p>
                                <p class="price text-body font-bold text-ink">{{ rupiah($order->total) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-4 pb-3 sm:justify-end">
                            <p class="price text-body font-bold text-ink sm:hidden">{{ rupiah($order->total) }}</p>
                            <a href="{{ route('orders.show', $order) }}" wire:navigate class="btn-secondary btn-sm after:absolute after:inset-0">Lihat detail</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="mt-6">{{ $orders->links('partials.pagination') }}</div>
</div>
