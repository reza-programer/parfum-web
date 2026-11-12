@php
    $available = $product->is_active && $variants->contains(fn ($v) => $v->stock > 0);
@endphp
{{-- Lapisan di atas foto kartu. Kartu (x-shop.product-card) memberi class "group" untuk efek hover. --}}
<div x-data="{ open: false }" x-on:click.outside="open = false" class="pointer-events-none absolute inset-0 flex flex-col justify-between">
    <div class="flex justify-end p-1">
        <button type="button" wire:click="toggleWishlist" wire:loading.attr="disabled" wire:target="toggleWishlist"
                class="pointer-events-auto inline-flex size-11 items-center justify-center transition-transform duration-200 ease-out active:scale-90"
                aria-label="{{ $wished ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}" aria-pressed="{{ $wished ? 'true' : 'false' }}">
            <span class="inline-flex size-8 items-center justify-center rounded-full bg-paper-card/90 shadow-card">
                <x-lucide-heart @class(['size-4 transition-[fill,color,transform] duration-200', 'fill-primary text-primary scale-110' => $wished, 'text-ink-2' => ! $wished]) stroke-width="2" />
            </span>
        </button>
    </div>

    @if ($available)
        <div class="relative">
            {{-- Layar sentuh: tombol "+" membuka pilihan ukuran --}}
            <button type="button" x-show="! open" x-on:click="open = true"
                    class="pointer-events-auto absolute bottom-0 right-0 inline-flex size-11 items-center justify-center lg:hidden"
                    aria-label="Tambah cepat {{ $product->name }}">
                <span class="inline-flex size-8 items-center justify-center rounded-full bg-primary text-white shadow-card">
                    <x-lucide-plus class="size-4" stroke-width="2.25" />
                </span>
            </button>

            {{-- Pilihan ukuran: muncul saat hover di desktop, atau setelah "+" di layar sentuh --}}
            <div :class="open ? 'max-lg:pointer-events-auto max-lg:translate-y-0 max-lg:opacity-100' : ''"
                 class="m-1.5 rounded-md bg-paper-card/95 px-2.5 pb-2.5 pt-2 shadow-float backdrop-blur transition duration-200 ease-out max-lg:pointer-events-none max-lg:translate-y-2 max-lg:opacity-0 lg:pointer-events-none lg:translate-y-2 lg:opacity-0 lg:group-hover:pointer-events-auto lg:group-hover:translate-y-0 lg:group-hover:opacity-100 lg:group-focus-within:pointer-events-auto lg:group-focus-within:translate-y-0 lg:group-focus-within:opacity-100">
                <p class="text-caption font-semibold text-ink">Tambah cepat</p>
                <div class="mt-1.5 flex flex-wrap gap-1">
                    @foreach ($variants as $variant)
                        <button type="button" wire:click="add({{ $variant->id }})" wire:loading.attr="disabled" wire:target="add"
                                data-variant="{{ $variant->id }}" @disabled($variant->stock < 1)
                                class="inline-flex h-8 items-center rounded border border-line bg-paper-card px-2 text-caption font-medium tabular-nums text-ink transition-colors duration-150 hover:border-primary hover:text-primary disabled:cursor-not-allowed disabled:text-ink-faint disabled:line-through disabled:hover:border-line disabled:hover:text-ink-faint">
                            {{ $variant->label() }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
