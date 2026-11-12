<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Menunggu Bayar',
            self::Paid => 'Dibayar',
            self::Processing => 'Dikemas',
            self::Shipped => 'Dikirim',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Warna badge Filament (tema monokrom): hitam untuk yang perlu ditindaklanjuti admin,
     * abu-abu untuk menunggu/selesai, merah hanya untuk dibatalkan.
     */
    public function color(): string
    {
        return match ($this) {
            self::PendingPayment, self::Completed => 'gray',
            self::Paid, self::Processing, self::Shipped => 'primary',
            self::Cancelled => 'danger',
        };
    }

    /**
     * Status yang dihitung sebagai "terjual" (sudah dibayar, belum batal).
     *
     * @return list<string>
     */
    public static function soldValues(): array
    {
        return array_map(fn (self $s) => $s->value, [self::Paid, self::Processing, self::Shipped, self::Completed]);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
