@props(['status'])
@php
    // Badge monokrom: makin jauh prosesnya, makin pekat. Dibatalkan tampil paling redup.
    $tone = match ($status) {
        \App\Enums\OrderStatus::PendingPayment => 'border border-ink text-ink',
        \App\Enums\OrderStatus::Paid, \App\Enums\OrderStatus::Processing => 'bg-ink/[0.06] text-ink',
        \App\Enums\OrderStatus::Shipped => 'bg-ink/[0.12] text-ink',
        \App\Enums\OrderStatus::Completed => 'bg-ink text-white',
        \App\Enums\OrderStatus::Cancelled => 'border border-line text-ink-2',
    };
@endphp
<x-ui.badge {{ $attributes->class($tone) }}>{{ $status->label() }}</x-ui.badge>
