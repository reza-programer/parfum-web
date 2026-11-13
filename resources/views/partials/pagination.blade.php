{{-- Pagination untuk komponen Livewire (WithPagination): tombol bulat bernomor, panah di kiri dan kanan. --}}
@if ($paginator->hasPages())
    @php
        $pageName = $paginator->getPageName();
        $box = 'inline-flex size-10 items-center justify-center text-body font-medium tabular-nums transition-colors duration-150';
    @endphp
    <nav role="navigation" aria-label="Halaman" class="flex items-center justify-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="{{ $box }} text-ink-faint" aria-hidden="true"><x-lucide-chevron-left class="size-5" stroke-width="2" /></span>
        @else
            <button type="button" wire:click="previousPage('{{ $pageName }}')" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="{{ $box }} text-ink hover:bg-paper-raised" aria-label="Halaman sebelumnya">
                <x-lucide-chevron-left class="size-5" stroke-width="2" />
            </button>
        @endif

        <div class="hidden items-center gap-1 sm:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-label text-ink-2">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="{{ $box }} bg-ink text-white">{{ $page }}</span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                                    class="{{ $box }} text-ink hover:bg-paper-raised">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>
        <p class="px-3 text-body text-ink-2 sm:hidden">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</p>

        @if ($paginator->hasMorePages())
            <button type="button" wire:click="nextPage('{{ $pageName }}')" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="{{ $box }} text-ink hover:bg-paper-raised" aria-label="Halaman berikutnya">
                <x-lucide-chevron-right class="size-5" stroke-width="2" />
            </button>
        @else
            <span class="{{ $box }} text-ink-faint" aria-hidden="true"><x-lucide-chevron-right class="size-5" stroke-width="2" /></span>
        @endif
    </nav>
@endif
