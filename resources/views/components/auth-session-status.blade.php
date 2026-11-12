@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg bg-paper-card border border-line px-4 py-3 text-sm text-ink']) }} role="status">
        {{ $status }}
    </div>
@endif
