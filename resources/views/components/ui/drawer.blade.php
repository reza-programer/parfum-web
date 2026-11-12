@props(['name', 'title' => null, 'side' => 'left', 'mobileBottom' => false])
{{--
    Drawer. Dibuka lewat window event: $dispatch('open-drawer', 'nama').
    side: left atau right. mobileBottom: di bawah 640px muncul dari bawah (filter katalog, sesuai PRD).
    Class ditulis utuh supaya terbaca Tailwind.
--}}
@php
    $variants = [
        'left' => [
            'panel' => 'inset-y-0 left-0 w-[min(22rem,88vw)]',
            'panelBottom' => 'max-sm:inset-x-0 max-sm:bottom-0 max-sm:max-h-[85vh] sm:inset-y-0 sm:left-0 sm:w-[22rem]',
            'from' => '-translate-x-full',
            'fromBottom' => 'max-sm:translate-y-full sm:-translate-x-full',
        ],
        'right' => [
            'panel' => 'inset-y-0 right-0 w-[min(24rem,92vw)]',
            'panelBottom' => 'max-sm:inset-x-0 max-sm:bottom-0 max-sm:max-h-[85vh] sm:inset-y-0 sm:right-0 sm:w-[24rem]',
            'from' => 'translate-x-full',
            'fromBottom' => 'max-sm:translate-y-full sm:translate-x-full',
        ],
    ][$side === 'right' ? 'right' : 'left'];
    $panel = $mobileBottom ? $variants['panelBottom'] : $variants['panel'];
    $from = $mobileBottom ? $variants['fromBottom'] : $variants['from'];
@endphp
<div x-data="{ open: false }"
     x-on:open-drawer.window="if ($event.detail === @js($name)) open = true"
     x-on:close-drawer.window="open = false"
     x-on:keydown.escape.window="open = false"
     x-effect="document.body.classList.toggle('overflow-hidden', open)"
     {{ $attributes }}>
    <div x-cloak x-show="open" x-transition.opacity.duration.200ms class="fixed inset-0 z-40 bg-ink/30" x-on:click="open = false"></div>

    <section x-cloak x-show="open"
             x-transition:enter="transition duration-300 ease-out"
             x-transition:enter-start="{{ $from }}"
             x-transition:leave="transition duration-200 ease-in"
             x-transition:leave-end="{{ $from }}"
             class="fixed z-50 flex flex-col bg-paper-card {{ $panel }}"
             role="dialog" aria-modal="true" @if ($title) aria-label="{{ $title }}" @endif>
        <header class="flex h-16 shrink-0 items-center justify-between border-b border-line px-5">
            <p class="eyebrow text-ink">{{ $title }}</p>
            <button type="button" x-on:click="open = false" class="-mr-2 inline-flex size-11 items-center justify-center text-ink-2 hover:text-ink" aria-label="Tutup">
                <x-lucide-x class="size-5" stroke-width="1.25" />
            </button>
        </header>
        <div class="flex-1 overflow-y-auto px-5 py-5">
            {{ $slot }}
        </div>
        @isset($footer)
            <footer class="shrink-0 border-t border-line px-5 py-4">{{ $footer }}</footer>
        @endisset
    </section>
</div>
