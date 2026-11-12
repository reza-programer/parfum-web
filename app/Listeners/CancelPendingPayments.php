<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pesanan batal → VA yang masih menunggu dibatalkan di Komerce supaya tidak bisa dibayar lagi.
 * Gagal di sini tidak membatalkan pembatalan pesanan; uang yang tetap masuk ditandai perlu refund.
 */
class CancelPendingPayments
{
    public function __construct(private readonly PaymentService $payments) {}

    public function cancel(OrderCancelled $event): void
    {
        try {
            $this->payments->cancelPending($event->order);
        } catch (Throwable $e) {
            Log::warning('Komerce: gagal membatalkan VA pesanan yang batal', ['order' => $event->order->code, 'error' => $e->getMessage()]);
        }
    }
}
