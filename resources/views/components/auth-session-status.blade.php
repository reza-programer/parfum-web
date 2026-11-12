@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'border-l-2 border-ink bg-paper px-4 py-3 text-body text-ink']) }} role="status">
        {{ $status }}
    </div>
@endif
