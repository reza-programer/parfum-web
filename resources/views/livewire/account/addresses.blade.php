<div class="page max-w-3xl pt-4 sm:pt-6">
    @include('partials.account-nav')

    <div class="mt-5 flex items-center justify-between gap-4">
        <h1 class="heading-page">Alamat</h1>
        @unless ($showForm)
            <button type="button" wire:click="create" class="btn-secondary btn-sm">
                <x-lucide-plus class="size-4" stroke-width="1.5" /> Tambah alamat
            </button>
        @endunless
    </div>

    @if (session('status'))
        <p class="mt-4 rounded-md border border-success/30 bg-success-soft px-4 py-3 text-body text-success" role="status">{{ session('status') }}</p>
    @endif

    @if ($showForm)
        <div class="card mt-4 p-4 sm:p-6">
            <h2 class="heading-section mb-6">{{ $editingId ? 'Ubah alamat' : 'Alamat baru' }}</h2>
            <livewire:account.address-form :address-id="$editingId" :key="'form-'.($editingId ?? 'new')" />
        </div>
    @endif

    <div class="mt-4 space-y-3">
        @forelse ($addresses as $address)
            <div class="card p-4 sm:p-5" wire:key="addr-{{ $address->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="flex items-center gap-2 text-body font-bold">
                            {{ $address->label }}
                            @if ($address->is_default)
                                <x-ui.badge tone="soft">Utama</x-ui.badge>
                            @endif
                        </p>
                        <p class="mt-2 text-sm text-ink">{{ $address->recipient }} · <span class="tabular-nums">{{ $address->phone }}</span></p>
                        <p class="mt-1 max-w-md text-sm leading-6 text-ink-2">{{ $address->fullText() }}</p>
                        @if ($address->needsSubdistrict())
                            <button type="button" wire:click="edit({{ $address->id }})" class="mt-2 inline-flex items-center gap-1.5 rounded-md bg-warning-soft px-2.5 py-1 text-label font-medium text-warning hover:bg-orange-100">
                                <x-lucide-circle-alert class="size-3.5" stroke-width="1.5" /> Lengkapi kelurahan
                            </button>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        @unless ($address->is_default)
                            <button type="button" wire:click="makeDefault({{ $address->id }})" class="inline-flex h-10 items-center rounded-md px-3 text-body font-medium text-ink-2 hover:bg-paper hover:text-primary">Jadikan utama</button>
                        @endunless
                        <button type="button" wire:click="edit({{ $address->id }})" class="inline-flex h-10 items-center rounded-md px-3 text-body font-medium text-primary hover:bg-primary-soft">Ubah</button>
                        <button type="button" wire:click="delete({{ $address->id }})" wire:confirm="Hapus alamat {{ $address->label }}?" class="inline-flex h-10 items-center rounded-md px-3 text-body font-medium text-ink-2 hover:bg-paper hover:text-danger">Hapus</button>
                    </div>
                </div>
            </div>
        @empty
            @unless ($showForm)
                <div class="card px-6 py-10 text-center">
                    <p class="font-bold text-ink">Belum ada alamat</p>
                    <p class="mt-1 text-sm text-ink-2">Tambahkan alamat supaya ongkir bisa dihitung saat checkout.</p>
                </div>
            @endunless
        @endforelse
    </div>

    @if ($redirectTo && $addresses->isNotEmpty())
        <a href="{{ $redirectTo }}" wire:navigate class="btn-primary mt-8">Lanjut ke checkout</a>
    @endif
</div>
