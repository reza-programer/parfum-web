<?php

namespace App\Support;

/**
 * Satu format teks alamat untuk semua tempat (daftar alamat, checkout, detail pesanan, admin, email):
 * "detail, Kel. Sakerta Timur, Darma, Kuningan, Jawa Barat 45562". Bagian kosong dilewati.
 */
class AddressText
{
    /**
     * @param  array<string, mixed>  $snapshot  kolom alamat (atau address_snapshot order)
     */
    public static function fromSnapshot(array $snapshot): string
    {
        $subdistrict = trim((string) ($snapshot['subdistrict_name'] ?? ''));

        $parts = array_filter([
            trim((string) ($snapshot['detail'] ?? '')),
            $subdistrict !== '' ? 'Kel. '.region_name($subdistrict) : '',
            region_name($snapshot['district_name'] ?? ''),
            region_name($snapshot['city_name'] ?? ''),
            region_name($snapshot['province_name'] ?? ''),
        ], fn (string $part) => $part !== '');

        return trim(implode(', ', $parts).' '.trim((string) ($snapshot['postal_code'] ?? '')));
    }
}
