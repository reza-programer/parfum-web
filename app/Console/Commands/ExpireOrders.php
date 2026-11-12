<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class ExpireOrders extends Command
{
    protected $signature = 'orders:expire';

    protected $description = 'Batalkan order Menunggu Bayar yang lewat 24 jam dan kembalikan stoknya';

    public function handle(PaymentService $payments, OrderService $orders): int
    {
        // Cek dulu ke Komerce: pesanan yang dibayar menjelang batas waktu tidak ikut dibatalkan.
        $payments->syncOverdue();

        $count = $orders->expireOverdue();

        $this->info("{$count} pesanan kedaluwarsa dibatalkan.");

        return self::SUCCESS;
    }
}
