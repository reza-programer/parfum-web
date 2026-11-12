{{-- Badge sederhana: teks 11px kapital, tanpa sudut, tanpa ikon. --}}
<span {{ $attributes->merge(['class' => 'inline-flex h-6 shrink-0 items-center px-2 text-caption font-medium uppercase tracking-label']) }}>{{ $slot }}</span>
