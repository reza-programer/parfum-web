<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Services\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu percobaan bayar di Komerce (VA atau QRIS). Diisi lewat PaymentService saja.
 */
class Payment extends Model
{
    use HasFactory;

    public const TYPE_VA = 'bank_transfer';

    public const TYPE_QRIS = 'qris';

    protected $fillable = [
        'order_id', 'reference', 'provider_payment_id', 'payment_type', 'channel_code', 'bank_name',
        'va_number', 'qr_string', 'payment_url', 'amount', 'status', 'expires_at', 'paid_at',
        'needs_refund', 'raw_response',
    ];

    protected $attributes = [
        'status' => 'pending',
        'needs_refund' => false,
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'integer',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'needs_refund' => 'boolean',
            'raw_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isVirtualAccount(): bool
    {
        return $this->payment_type === self::TYPE_VA;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }

    /**
     * Masih bisa dibayar customer: pending dan belum lewat batas waktunya.
     */
    public function isPayable(): bool
    {
        return $this->isPending() && (! $this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Nama metode untuk ditampilkan: "VA BCA" atau "QRIS".
     */
    public function methodLabel(): string
    {
        return $this->isVirtualAccount() ? 'VA '.$this->bankLabel() : 'QRIS';
    }

    /**
     * Nama bank singkat: "BCA", "Mandiri".
     */
    public function bankLabel(): string
    {
        return PaymentMethod::bankLabel($this->channel_code, $this->bank_name);
    }
}
