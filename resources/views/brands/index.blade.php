@php
    $groups = $brands->groupBy(fn ($brand) => mb_strtoupper(mb_substr($brand->name, 0, 1)))->sortKeys();
@endphp
<x-app-layout>
    <x-slot name="title">Brand</x-slot>
    <div class="page pt-10 lg:pt-14">
        <p class="eyebrow">Katalog</p>
        <div class="mt-3 flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
            <h1 class="heading-page">Brand</h1>
            <p class="meta tabular-nums">{{ $brands->count() }} brand</p>
        </div>

        @if ($groups->isEmpty())
            <p class="mt-10 border-t border-line pt-10 text-body text-ink-2">Belum ada brand.</p>
        @else
            {{-- Indeks huruf --}}
            <nav class="mt-8 flex flex-wrap gap-x-4 gap-y-2 border-y border-line py-4" aria-label="Indeks brand">
                @foreach ($groups->keys() as $letter)
                    <a href="#huruf-{{ $letter }}" class="inline-flex size-8 items-center justify-center text-label font-medium text-ink hover:underline">{{ $letter }}</a>
                @endforeach
            </nav>

            <div class="mt-4">
                @foreach ($groups as $letter => $items)
                    <section id="huruf-{{ $letter }}" class="grid scroll-mt-24 gap-4 border-b border-line py-8 sm:grid-cols-[120px_1fr]">
                        <h2 class="font-serif text-[40px] leading-none text-ink">{{ $letter }}</h2>
                        <ul class="grid gap-x-10 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($items as $brand)
                                <li>
                                    <a href="{{ route('brands.show', $brand) }}" wire:navigate class="group flex h-12 items-center justify-between gap-4 border-b border-line text-body text-ink">
                                        <span class="group-hover:underline group-hover:underline-offset-4">{{ $brand->name }}</span>
                                        <span class="text-label tabular-nums text-ink-2">{{ $brand->products_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
