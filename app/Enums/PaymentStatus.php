<?php

namespace App\Enums;

/**
 * Status satu percobaan bayar di Komerce. Nilainya sama dengan status API (huruf kecil).
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Expired = 'expired';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Paid => 'Dibayar',
            self::Expired => 'Kedaluwarsa',
            self::Canceled => 'Dibatalkan',
        };
    }

    /**
     * Warna badge Filament.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Expired, self::Canceled => 'gray',
        };
    }

    /**
     * Status dari API Komerce (PENDING, PAID, EXPIRED, CANCELED). Nilai lain dianggap belum berubah.
     */
    public static function fromKomerce(?string $status): ?self
    {
        return self::tryFrom(strtolower(trim((string) $status)));
    }
}
