@php
    $available = $product->is_active && $variants->contains(fn ($v) => $v->stock > 0);
@endphp
{{-- Lapisan di atas foto kartu. Kartu (x-shop.product-card) memberi class "group" untuk efek hover. --}}
<div x-data="{ open: false }" x-on:click.outside="open = false" class="pointer-events-none absolute inset-0 flex flex-col justify-between">
    <div class="flex justify-end">
        <button type="button" wire:click="toggleWishlist" wire:loading.attr="disabled" wire:target="toggleWishlist"
                class="pointer-events-auto inline-flex size-11 items-center justify-center text-ink transition-transform duration-200 ease-out active:scale-90"
                aria-label="{{ $wished ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}" aria-pressed="{{ $wished ? 'true' : 'false' }}">
            <x-lucide-heart @class(['size-[18px] transition-[fill,transform] duration-200', 'fill-ink scale-110' => $wished]) stroke-width="1.25" />
        </button>
    </div>

    @if ($available)
        <div class="relative">
            {{-- Mobile/tablet: tombol "+" membuka pilihan ukuran --}}
            <button type="button" x-show="! open" x-on:click="open = true"
                    class="pointer-events-auto absolute bottom-2 right-2 inline-flex size-11 items-center justify-center bg-paper-card text-ink lg:hidden"
                    aria-label="Tambah cepat {{ $product->name }}">
                <x-lucide-plus class="size-5" stroke-width="1.25" />
            </button>

            {{-- Pilihan ukuran: muncul saat hover di desktop, atau setelah "+" di layar sentuh --}}
            <div :class="open ? 'max-lg:pointer-events-auto max-lg:translate-y-0 max-lg:opacity-100' : ''"
                 class="bg-paper-card/95 px-3 pb-3 pt-2.5 backdrop-blur transition duration-200 ease-out max-lg:pointer-events-none max-lg:translate-y-2 max-lg:opacity-0 lg:pointer-events-none lg:translate-y-2 lg:opacity-0 lg:group-hover:pointer-events-auto lg:group-hover:translate-y-0 lg:group-hover:opacity-100 lg:group-focus-within:pointer-events-auto lg:group-focus-within:translate-y-0 lg:group-focus-within:opacity-100">
                <p class="eyebrow">Tambah cepat</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($variants as $variant)
                        <button type="button" wire:click="add({{ $variant->id }})" wire:loading.attr="disabled" wire:target="add"
                                data-variant="{{ $variant->id }}" @disabled($variant->stock < 1)
                                class="inline-flex h-9 items-center border border-line bg-paper-card px-2.5 text-label tabular-nums text-ink transition-colors duration-150 hover:border-ink disabled:cursor-not-allowed disabled:text-ink-faint disabled:line-through disabled:hover:border-line">
                            {{ $variant->label() }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
