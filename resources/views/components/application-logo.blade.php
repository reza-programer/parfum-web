@props(['inverse' => false])
{{-- Wordmark toko: serif kapital berjarak, tanpa ikon. inverse: putih untuk latar gelap (footer). --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center font-serif text-[22px] font-semibold uppercase leading-none tracking-[0.16em] '.($inverse ? 'text-white' : 'text-ink')]) }}>
    {{ config('app.name') }}
</span>
