<?php

namespace App\Services;

use App\Models\Payment;

/**
 * Satu metode bayar dari GET /methods Komerce: VA per bank, atau QRIS.
 */
final class PaymentMethod
{
    private const SHORT_NAMES = [
        'BCA' => 'BCA',
        'BNI' => 'BNI',
        'BRI' => 'BRI',
        'MANDIRI' => 'Mandiri',
        'PERMATA' => 'Permata',
        'CIMB' => 'CIMB Niaga',
        'BSI' => 'BSI',
        'BJB' => 'bank bjb',
        'DBS' => 'DBS',
        'BNC' => 'Neo Commerce',
        'SAHABAT_SAMPOERNA' => 'Sahabat Sampoerna',
    ];

    public function __construct(
        public readonly string $type,
        public readonly ?string $bankCode,
        public readonly string $displayName,
        public readonly ?string $logoUrl,
        public readonly int $minAmount,
        public readonly int $maxAmount,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromArray(array $row): self
    {
        $isQris = strtolower((string) ($row['payment_type'] ?? '')) === 'qris';
        $bank = strtoupper(trim((string) ($row['bank_code'] ?? '')));

        return new self(
            type: $isQris ? Payment::TYPE_QRIS : Payment::TYPE_VA,
            bankCode: $isQris || $bank === '' ? null : $bank,
            displayName: (string) ($row['display_name'] ?? $bank),
            logoUrl: ($row['logo_url'] ?? null) ?: null,
            minAmount: (int) ($row['min_amount'] ?? 0),
            maxAmount: (int) ($row['max_amount'] ?? PHP_INT_MAX),
        );
    }

    /**
     * Nilai radio di checkout: "qris" atau "va:BCA".
     */
    public function key(): string
    {
        return $this->isQris() ? 'qris' : 'va:'.$this->bankCode;
    }

    public function isQris(): bool
    {
        return $this->type === Payment::TYPE_QRIS;
    }

    public function isValid(): bool
    {
        return $this->isQris() || $this->bankCode !== null;
    }

    public function accepts(int $amount): bool
    {
        return $amount >= $this->minAmount && $amount <= $this->maxAmount;
    }

    /**
     * Nama singkat yang dikenal customer: "BCA", "Mandiri", "QRIS".
     */
    public function label(): string
    {
        return $this->isQris() ? 'QRIS' : self::bankLabel($this->bankCode, $this->displayName);
    }

    public static function bankLabel(?string $bankCode, ?string $fallback = null): string
    {
        $code = strtoupper((string) $bankCode);

        return self::SHORT_NAMES[$code] ?? ($fallback ?: $code);
    }
}
