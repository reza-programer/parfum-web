<div class="page max-w-3xl pt-10 lg:pt-14">
    @include('partials.account-nav')

    <div class="mt-10 flex items-center justify-between gap-4">
        <h1 class="heading-page">Alamat</h1>
        @unless ($showForm)
            <button type="button" wire:click="create" class="btn-secondary btn-sm">
                <x-lucide-plus class="size-4" stroke-width="1.5" /> Tambah alamat
            </button>
        @endunless
    </div>

    @if (session('status'))
        <p class="mt-4 border-l-2 border-ink bg-paper-card px-4 py-3 text-body text-ink" role="status">{{ session('status') }}</p>
    @endif

    @if ($showForm)
        <div class="mt-8 bg-paper-card p-6 sm:p-8">
            <h2 class="heading-section mb-6">{{ $editingId ? 'Ubah alamat' : 'Alamat baru' }}</h2>
            <livewire:account.address-form :address-id="$editingId" :key="'form-'.($editingId ?? 'new')" />
        </div>
    @endif

    <div class="mt-6 space-y-3">
        @forelse ($addresses as $address)
            <div class="bg-paper-card p-5 sm:p-6" wire:key="addr-{{ $address->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="flex items-center gap-2 text-body font-medium">
                            {{ $address->label }}
                            @if ($address->is_default)
                                <span class="inline-flex h-5 items-center bg-ink/[0.06] px-2 text-caption font-medium uppercase tracking-label text-ink">Utama</span>
                            @endif
                        </p>
                        <p class="mt-2 text-sm text-ink">{{ $address->recipient }} · <span class="tabular-nums">{{ $address->phone }}</span></p>
                        <p class="mt-1 max-w-md text-sm leading-6 text-ink-2">{{ $address->fullText() }}</p>
                        @if ($address->needsSubdistrict())
                            <button type="button" wire:click="edit({{ $address->id }})" class="mt-2 inline-flex items-center gap-1.5 text-label text-ink underline decoration-ink/30 underline-offset-4 hover:decoration-ink">
                                <x-lucide-circle-alert class="size-3.5" stroke-width="1.5" /> Lengkapi kelurahan
                            </button>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        @unless ($address->is_default)
                            <button type="button" wire:click="makeDefault({{ $address->id }})" class="inline-flex h-11 items-center px-3 text-label uppercase tracking-label text-ink-2 hover:text-ink">Jadikan utama</button>
                        @endunless
                        <button type="button" wire:click="edit({{ $address->id }})" class="inline-flex h-11 items-center px-3 text-label uppercase tracking-label text-ink-2 hover:text-ink">Ubah</button>
                        <button type="button" wire:click="delete({{ $address->id }})" wire:confirm="Hapus alamat {{ $address->label }}?" class="inline-flex h-11 items-center px-3 text-label uppercase tracking-label text-ink-2 hover:text-danger">Hapus</button>
                    </div>
                </div>
            </div>
        @empty
            @unless ($showForm)
                <div class="border-t border-line pt-10">
                    <p class="text-ink">Belum ada alamat</p>
                    <p class="mt-1 text-sm text-ink-2">Tambahkan alamat supaya ongkir bisa dihitung saat checkout.</p>
                </div>
            @endunless
        @endforelse
    </div>

    @if ($redirectTo && $addresses->isNotEmpty())
        <a href="{{ $redirectTo }}" wire:navigate class="btn-primary mt-8">Lanjut ke checkout</a>
    @endif
</div>
