@props(['status'])
@php
    // Warna status: kuning menunggu bayar, biru sedang diproses/dikirim, hijau selesai, abu-abu batal.
    $tone = match ($status) {
        \App\Enums\OrderStatus::PendingPayment => 'warning',
        \App\Enums\OrderStatus::Paid, \App\Enums\OrderStatus::Processing, \App\Enums\OrderStatus::Shipped => 'info',
        \App\Enums\OrderStatus::Completed => 'success',
        \App\Enums\OrderStatus::Cancelled => 'neutral',
    };
@endphp
<x-ui.badge :tone="$tone" size="md" {{ $attributes }}>{{ $status->label() }}</x-ui.badge>
