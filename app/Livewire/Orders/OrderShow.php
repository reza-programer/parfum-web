<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Exceptions\PaymentException;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class OrderShow extends Component
{
    public Order $order;

    /** Pilihan metode saat membuat pembayaran baru ("qris" atau "va:BCA"). */
    public ?string $paymentMethod = null;

    public bool $choosingMethod = false;

    public bool $justPaid = false;

    public function mount(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 404);

        $this->order = $order->loadMissing('payment');

        // QR yang kedaluwarsa: langsung siap dibuat ulang dengan metode yang sama.
        $latest = $this->order->payment;
        if ($latest && ! $latest->isVirtualAccount() && ! $latest->isPayable()) {
            $this->paymentMethod = 'qris';
        }
    }

    /**
     * Percobaan bayar terbaru yang masih bisa dibayar, kalau pesanan masih menunggu bayar.
     */
    #[Computed]
    public function activePayment(): ?Payment
    {
        $payment = $this->order->payment;

        return $this->order->status === OrderStatus::PendingPayment && $payment?->isPayable() ? $payment : null;
    }

    /**
     * @return array{methods: list<PaymentMethod>, error: ?string}
     */
    #[Computed]
    public function paymentMethods(): array
    {
        try {
            return ['methods' => app(PaymentService::class)->methods($this->order->total), 'error' => null];
        } catch (PaymentException $e) {
            return ['methods' => [], 'error' => $e->getMessage()];
        }
    }

    #[Computed]
    public function isSandbox(): bool
    {
        return app(PaymentService::class)->isSandbox();
    }

    public function pay(PaymentService $payments): void
    {
        $this->resetErrorBag();

        if (! $this->paymentMethod) {
            $this->addError('paymentMethod', 'Pilih metode pembayaran.');

            return;
        }

        try {
            $payments->create($this->order, $this->paymentMethod);
            $this->choosingMethod = false;
        } catch (PaymentException $e) {
            $this->addError('payment', $e->getMessage());
        }

        $this->reload();
    }

    public function changeMethod(): void
    {
        $this->choosingMethod = true;
        $this->paymentMethod = null;
    }

    public function cancelChange(): void
    {
        $this->choosingMethod = false;
        $this->resetErrorBag();
    }

    /**
     * Dipanggil wire:poll selama ada pembayaran yang ditunggu. Cek ke Komerce dibatasi
     * PaymentService (sekali per 3 detik per pembayaran).
     */
    public function checkPayment(PaymentService $payments): void
    {
        if ($this->order->status !== OrderStatus::PendingPayment) {
            return;
        }

        try {
            $payments->refreshPending($this->order);
        } catch (PaymentException) {
            // Dicoba lagi di polling berikutnya.
        }

        $this->reload();
        $this->justPaid = $this->order->status === OrderStatus::Paid;
    }

    public function confirmReceived(OrderService $orders): void
    {
        $this->run(fn () => $orders->complete($this->order));
        session()->flash('status', 'Terima kasih. Pesanan ditandai selesai, sekarang kamu bisa menulis ulasan.');
    }

    public function cancel(OrderService $orders): void
    {
        if ($this->order->status !== OrderStatus::PendingPayment) {
            $this->addError('action', 'Pesanan yang sudah dibayar tidak bisa dibatalkan sendiri. Hubungi kami lewat chat.');

            return;
        }

        $this->run(fn () => $orders->cancelUnpaid($this->order));
    }

    public function render(): View
    {
        $this->order->load(['items.variant.product', 'items.review', 'payment']);

        return view('livewire.orders.order-show')->title("Pesanan {$this->order->code}");
    }

    private function run(callable $action): void
    {
        try {
            $action();
        } catch (OrderException $e) {
            $this->addError('action', $e->getMessage());
        }

        $this->reload();
    }

    private function reload(): void
    {
        $this->order->refresh();
        unset($this->activePayment);
    }
}
