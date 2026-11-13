@props(['tone' => 'neutral', 'size' => 'sm'])
{{-- Badge kecil. tone: neutral, primary, soft, success, warning, info, dark. size: sm (kartu), md (status), pill (label di atas foto). --}}
@php
    $tones = [
        'neutral' => 'bg-paper text-ink-2',
        'primary' => 'bg-primary text-white',
        'soft' => 'bg-paper-raised text-ink',
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'info' => 'bg-sky-50 text-sky-700',
        'dark' => 'bg-ink/80 text-white',
    ];
    $sizes = [
        'sm' => 'h-5 rounded px-1.5 text-caption',
        'md' => 'h-6 rounded px-2 text-label',
        'pill' => 'h-7 rounded-full px-3 text-label',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center font-semibold '.($sizes[$size] ?? $sizes['sm']).' '.($tones[$tone] ?? $tones['neutral'])]) }}>{{ $slot }}</span>
