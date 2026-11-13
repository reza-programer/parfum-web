@props(['name', 'title' => null, 'side' => 'left', 'mobileBottom' => false, 'teleport' => false])
{{--
    Drawer. Dibuka lewat window event: $dispatch('open-drawer', 'nama').
    side: left atau right. mobileBottom: di bawah 640px muncul dari bawah (filter katalog, sesuai PRD).
    teleport: pindahkan panel ke <body>. Wajib kalau drawer berada di dalam elemen yang memakai
    backdrop-filter/transform/sticky (misalnya navbar), karena position: fixed akan terkurung di sana.
    Class ditulis utuh supaya terbaca Tailwind.
--}}
@php
    $variants = [
        'left' => [
            'panel' => 'inset-y-0 left-0 h-dvh w-[min(22rem,88vw)] border-r border-line',
            'panelBottom' => 'max-sm:inset-x-0 max-sm:bottom-0 max-sm:max-h-[85dvh] max-sm:rounded-t-xl sm:inset-y-0 sm:left-0 sm:h-dvh sm:w-[22rem]',
            'from' => '-translate-x-full',
            'fromBottom' => 'max-sm:translate-y-full sm:-translate-x-full',
        ],
        'right' => [
            'panel' => 'inset-y-0 right-0 h-dvh w-[min(24rem,92vw)] border-l border-line',
            'panelBottom' => 'max-sm:inset-x-0 max-sm:bottom-0 max-sm:max-h-[85dvh] max-sm:rounded-t-xl sm:inset-y-0 sm:right-0 sm:h-dvh sm:w-[24rem]',
            'from' => 'translate-x-full',
            'fromBottom' => 'max-sm:translate-y-full sm:translate-x-full',
        ],
    ][$side === 'right' ? 'right' : 'left'];
    $panel = $mobileBottom ? $variants['panelBottom'] : $variants['panel'];
    $from = $mobileBottom ? $variants['fromBottom'] : $variants['from'];
    $visibility = $attributes->get('class');
@endphp
<div x-data="{ open: false }"
     x-on:open-drawer.window="if ($event.detail === @js($name)) open = true"
     x-on:close-drawer.window="open = false"
     x-on:livewire:navigating.window="open = false"
     x-on:keydown.escape.window="open = false"
     {{ $attributes }}>
    @if ($teleport)<template x-teleport="body">@endif
    <div class="{{ $visibility }}">
        <div x-cloak x-show="open"
             x-transition:enter="transition-opacity duration-300 ease-out" x-transition:enter-start="opacity-0"
             x-transition:leave="transition-opacity duration-200 ease-in" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-ink/40" x-on:click="open = false" aria-hidden="true"></div>

        <section x-cloak x-show="open"
                 x-trap.inert.noscroll="open"
                 x-transition:enter="transition-transform duration-300 ease-out"
                 x-transition:enter-start="{{ $from }}"
                 x-transition:leave="transition-transform duration-200 ease-in"
                 x-transition:leave-end="{{ $from }}"
                 {{-- Pilih link di dalam drawer: tutup dulu supaya pindah halaman terasa langsung. --}}
                 x-on:click="if ($event.target.closest('a[href]')) open = false"
                 class="fixed z-[60] flex flex-col bg-paper-card {{ $panel }}"
                 role="dialog" aria-modal="true" @if ($title) aria-label="{{ $title }}" @endif>
            <header class="flex h-14 shrink-0 items-center justify-between border-b border-line px-5">
                <p class="text-[13px] font-bold uppercase tracking-nav text-ink">{{ $title }}</p>
                <button type="button" x-on:click="open = false" class="-mr-3 inline-flex size-11 items-center justify-center text-ink-2 transition-colors duration-150 hover:text-ink active:text-ink" aria-label="Tutup">
                    <x-lucide-x class="size-5" stroke-width="1.75" />
                </button>
            </header>
            <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5">
                {{ $slot }}
            </div>
            @isset($footer)
                <footer class="shrink-0 border-t border-line px-5 pb-[max(1rem,env(safe-area-inset-bottom))] pt-4">{{ $footer }}</footer>
            @endisset
        </section>
    </div>
    @if ($teleport)</template>@endif
</div>
