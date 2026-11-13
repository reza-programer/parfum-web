@php
    $groups = $brands->groupBy(fn ($brand) => mb_strtoupper(mb_substr($brand->name, 0, 1)))->sortKeys();
@endphp
<x-app-layout>
    <x-slot name="title">Brand</x-slot>
    <div class="page pt-4 sm:pt-6">
        <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-label text-ink-2">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-primary">Beranda</a>
            <x-lucide-chevron-right class="size-3.5" stroke-width="2" />
            <span class="text-ink">Brand</span>
        </nav>
        <div class="mt-3 flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
            <h1 class="heading-page">Brand</h1>
            <p class="meta tabular-nums">{{ $brands->count() }} brand</p>
        </div>

        @if ($groups->isEmpty())
            <p class="card mt-3 px-6 py-10 text-center text-body text-ink-2">Belum ada brand.</p>
        @else
            {{-- Indeks huruf --}}
            <nav class="card mt-3 flex flex-wrap gap-1 p-2" aria-label="Indeks brand">
                @foreach ($groups->keys() as $letter)
                    <a href="#huruf-{{ $letter }}" class="inline-flex size-9 items-center justify-center text-body font-semibold text-ink transition-colors duration-150 hover:bg-paper-raised">{{ $letter }}</a>
                @endforeach
            </nav>

            @foreach ($groups as $letter => $items)
                <section id="huruf-{{ $letter }}" class="mt-5 scroll-mt-[calc(var(--header-h)+1rem)]">
                    <h2 class="mb-3 text-[20px] font-bold uppercase text-ink">{{ $letter }}</h2>
                    <ul class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($items as $brand)
                            <li>
                                <a href="{{ route('brands.show', $brand) }}" wire:navigate class="card group flex items-center gap-3 p-3 transition-shadow duration-200">
                                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-paper-raised font-serif text-base font-semibold uppercase text-ink">{{ mb_substr($brand->name, 0, 1) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-body font-semibold text-ink group-hover:text-primary">{{ $brand->name }}</span>
                                        <span class="block text-label tabular-nums text-ink-2">{{ $brand->products_count }} parfum</span>
                                    </span>
                                    <x-lucide-chevron-right class="size-4 shrink-0 text-ink-2 group-hover:text-primary" stroke-width="2" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        @endif
    </div>
</x-app-layout>
