{{-- Pagination ringkas untuk komponen Livewire (WithPagination). --}}
@if ($paginator->hasPages())
    @php($pageName = $paginator->getPageName())
    <nav role="navigation" aria-label="Halaman" class="flex items-center justify-between gap-4 border-t border-line pt-6">
        @if ($paginator->onFirstPage())
            <span class="btn-ghost -ml-3 text-ink-faint">Sebelumnya</span>
        @else
            <button type="button" wire:click="previousPage('{{ $pageName }}')" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="btn-ghost -ml-3">Sebelumnya</button>
        @endif

        <div class="hidden items-center gap-1 sm:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-label text-ink-2">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex size-10 items-center justify-center bg-ink text-label tabular-nums text-white">{{ $page }}</span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                                    class="inline-flex size-10 items-center justify-center text-label tabular-nums text-ink-2 hover:text-ink">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>
        <p class="text-label text-ink-2 sm:hidden">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</p>

        @if ($paginator->hasMorePages())
            <button type="button" wire:click="nextPage('{{ $pageName }}')" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="btn-ghost -mr-3">Berikutnya</button>
        @else
            <span class="btn-ghost -mr-3 text-ink-faint">Berikutnya</span>
        @endif
    </nav>
@endif
