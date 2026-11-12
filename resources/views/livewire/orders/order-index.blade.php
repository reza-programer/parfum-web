@php
    $th = 'h-11 px-4 text-left text-label font-medium text-ink-2';
    $summary = function ($order) {
        $first = $order->items->first();
        $more = $order->items->count() - 1;

        return $first ? $first->product_name.' '.$first->size_ml.'ml'.($more > 0 ? " +{$more} lainnya" : '') : '–';
    };
@endphp
<div class="page pt-10 lg:pt-14">
    @include('partials.account-nav')

    <h1 class="heading-page mt-10">Pesanan saya</h1>

    {{-- Filter status --}}
    <div class="mt-6 flex gap-6 overflow-x-auto border-b border-line [scrollbar-width:none]" role="tablist" aria-label="Status pesanan">
        @foreach (\App\Livewire\Orders\OrderIndex::TABS as $value => $label)
            <button type="button" wire:click="$set('status', '{{ $value }}')" role="tab" aria-selected="{{ $status === $value ? 'true' : 'false' }}"
                @class(['-mb-px inline-flex h-11 shrink-0 items-center border-b text-body transition-colors duration-150',
                    'border-ink text-ink' => $status === $value,
                    'border-transparent text-ink-2 hover:text-ink' => $status !== $value])>{{ $label }}</button>
        @endforeach
    </div>

    <div class="mt-6 transition-opacity duration-150" wire:loading.class="opacity-50">
        @if ($orders->isEmpty())
            <div class="py-12">
                <p class="heading-section">Belum ada pesanan</p>
                <p class="mt-2 text-body text-ink-2">Pesanan yang kamu buat akan muncul di sini.</p>
                <a href="{{ route('catalog') }}" wire:navigate class="btn-primary mt-6">Lihat katalog</a>
            </div>
        @else
            {{-- Desktop/tablet: tabel --}}
            <div class="hidden border border-line bg-paper-card md:block">
                <table class="w-full table-fixed">
                    <thead class="border-b border-line bg-[#fafafa]">
                        <tr>
                            <th scope="col" class="{{ $th }} w-[22%]">No. pesanan</th>
                            <th scope="col" class="{{ $th }} w-[18%]">Tanggal</th>
                            <th scope="col" class="{{ $th }}">Barang</th>
                            <th scope="col" class="{{ $th }} w-[15%]">Status</th>
                            <th scope="col" class="{{ $th }} w-[15%] text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($orders as $order)
                            <tr wire:key="o-{{ $order->id }}" class="relative h-14 text-[13px] transition-colors duration-150 hover:bg-paper">
                                <td class="px-4">
                                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="font-medium tabular-nums text-ink after:absolute after:inset-0">{{ $order->code }}</a>
                                </td>
                                <td class="px-4 tabular-nums text-ink-2">{{ $order->created_at->translatedFormat('j M Y, H:i') }}</td>
                                <td class="truncate px-4 text-ink-2">{{ $summary($order) }}</td>
                                <td class="px-4"><x-shop.order-status :status="$order->status" /></td>
                                <td class="price px-4 text-right font-medium text-ink">{{ rupiah($order->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile: daftar --}}
            <ul class="divide-y divide-line border-y border-line md:hidden">
                @foreach ($orders as $order)
                    <li wire:key="om-{{ $order->id }}">
                        <a href="{{ route('orders.show', $order) }}" wire:navigate class="block py-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-body font-medium tabular-nums text-ink">{{ $order->code }}</p>
                                <x-shop.order-status :status="$order->status" />
                            </div>
                            <p class="meta mt-1">{{ $order->created_at->translatedFormat('j M Y, H:i') }}</p>
                            <div class="mt-3 flex items-end justify-between gap-3">
                                <p class="min-w-0 truncate text-label text-ink-2">{{ $summary($order) }}</p>
                                <p class="price shrink-0 text-body font-medium text-ink">{{ rupiah($order->total) }}</p>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="mt-8">{{ $orders->links('partials.pagination') }}</div>
</div>
