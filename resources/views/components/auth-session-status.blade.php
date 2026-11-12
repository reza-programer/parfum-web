@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-md border border-success/30 bg-success-soft px-4 py-3 text-body text-success']) }} role="status">
        {{ $status }}
    </div>
@endif
