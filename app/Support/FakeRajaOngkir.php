<?php

namespace App\Support;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/**
 * Data contoh RajaOngkir untuk development tanpa API key (RAJAONGKIR_FAKE=true, APP_ENV=local).
 * Bentuk respons mengikuti API Komerce supaya ShippingService tetap memakai jalur yang sama.
 */
class FakeRajaOngkir
{
    public const PROVINCES = [
        6 => 'DKI JAKARTA',
        9 => 'JAWA BARAT',
        10 => 'JAWA TENGAH',
        11 => 'JAWA TIMUR',
        1 => 'BALI',
    ];

    public const CITIES = [
        6 => [151 => 'JAKARTA SELATAN', 152 => 'JAKARTA PUSAT'],
        9 => [23 => 'BANDUNG', 115 => 'DEPOK'],
        10 => [398 => 'SEMARANG', 445 => 'SURAKARTA (SOLO)'],
        11 => [444 => 'SURABAYA', 255 => 'MALANG'],
        1 => [114 => 'DENPASAR'],
    ];

    public const DISTRICTS = [
        151 => [2101 => 'KEBAYORAN BARU', 2102 => 'TEBET'],
        152 => [2111 => 'MENTENG', 2112 => 'GAMBIR'],
        23 => [1001 => 'COBLONG', 1002 => 'SUKAJADI'],
        115 => [1101 => 'BEJI', 1102 => 'PANCORAN MAS'],
        398 => [3301 => 'SEMARANG TENGAH', 3302 => 'BANYUMANIK'],
        445 => [3401 => 'LAWEYAN', 3402 => 'BANJARSARI'],
        444 => [3501 => 'GUBENG', 3502 => 'WONOKROMO'],
        255 => [3601 => 'KLOJEN', 3602 => 'LOWOKWARU'],
        114 => [5101 => 'DENPASAR SELATAN', 5102 => 'DENPASAR UTARA'],
    ];

    /** district_id => [subdistrict_id => [nama, kode pos]] */
    public const SUBDISTRICTS = [
        2101 => [21011 => ['SENAYAN', '12190'], 21012 => ['GUNUNG', '12120']],
        2102 => [21021 => ['TEBET TIMUR', '12820'], 21022 => ['MANGGARAI', '12850']],
        2111 => [21111 => ['MENTENG', '10310'], 21112 => ['GONDANGDIA', '10350']],
        2112 => [21121 => ['GAMBIR', '10110'], 21122 => ['PETOJO SELATAN', '10160']],
        1001 => [10011 => ['DAGO', '40135'], 10012 => ['LEBAKGEDE', '40132'], 10013 => ['SEKELOA', '40134']],
        1002 => [10021 => ['PASTEUR', '40161'], 10022 => ['CIPEDES', '40162']],
        1101 => [11011 => ['BEJI', '16421'], 11012 => ['KUKUSAN', '16425']],
        1102 => [11021 => ['DEPOK', '16431'], 11022 => ['PANCORAN MAS', '16436']],
        3301 => [33011 => ['SEKAYU', '50132'], 33012 => ['PEKUNDEN', '50134']],
        3302 => [33021 => ['BANYUMANIK', '50264'], 33022 => ['SRONDOL WETAN', '50263']],
        3401 => [34011 => ['LAWEYAN', '57148'], 34012 => ['SONDAKAN', '57147']],
        3402 => [34021 => ['MANAHAN', '57139'], 34022 => ['KESTALAN', '57133']],
        3501 => [35011 => ['GUBENG', '60281'], 35012 => ['AIRLANGGA', '60286']],
        3502 => [35021 => ['DARMO', '60241'], 35022 => ['NGAGEL', '60246']],
        3601 => [36011 => ['KLOJEN', '65111'], 36012 => ['KAUMAN', '65119']],
        3602 => [36021 => ['LOWOKWARU', '65141'], 36022 => ['DINOYO', '65144']],
        5101 => [51011 => ['SANUR', '80227'], 51012 => ['SIDAKARYA', '80224']],
        // 5102 (Denpasar Utara) sengaja tanpa kelurahan: menguji kasus RajaOngkir tidak punya datanya.
    ];

    public static function register(string $baseUrl): void
    {
        $base = rtrim($baseUrl, '/');

        Http::fake([
            "{$base}/*" => function (Request $request) use ($base) {
                $path = trim(substr(strtok($request->url(), '?'), strlen($base)), '/');

                return Http::response(['meta' => ['code' => 200, 'status' => 'success'], 'data' => self::data($path, $request)]);
            },
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function data(string $path, Request $request): array
    {
        $rows = fn (array $items) => collect($items)->map(fn ($name, $id) => ['id' => $id, 'name' => $name])->values()->all();

        return match (true) {
            $path === 'destination/province' => $rows(self::PROVINCES),
            str_starts_with($path, 'destination/city/') => $rows(self::CITIES[(int) substr($path, 17)] ?? []),
            str_starts_with($path, 'destination/district/') => $rows(self::DISTRICTS[(int) substr($path, 21)] ?? []),
            str_starts_with($path, 'destination/sub-district/') => collect(self::SUBDISTRICTS[(int) substr($path, 25)] ?? [])
                ->map(fn ($row, $id) => ['id' => $id, 'name' => $row[0], 'zip_code' => $row[1]])->values()->all(),
            $path === 'calculate/district/domestic-cost' => self::costs((int) $request['destination'], (int) $request['weight']),
            default => [],
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function costs(int $destination, int $weight): array
    {
        $kg = max(1, (int) ceil($weight / 1000));
        $zone = $destination >= 5000 ? 3 : ($destination >= 3000 ? 2 : 1);

        return [
            ['name' => 'Jalur Nugraha Ekakurir (JNE)', 'code' => 'jne', 'service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => (8000 + 4000 * $zone) * $kg, 'etd' => (1 + $zone).'-'.(2 + $zone).' day'],
            ['name' => 'Jalur Nugraha Ekakurir (JNE)', 'code' => 'jne', 'service' => 'YES', 'description' => 'Yakin Esok Sampai', 'cost' => (16000 + 6000 * $zone) * $kg, 'etd' => '1 day'],
            ['name' => 'J&T Express', 'code' => 'jnt', 'service' => 'EZ', 'description' => 'Reguler', 'cost' => (8500 + 3500 * $zone) * $kg, 'etd' => (2 + $zone).' day'],
            ['name' => 'SiCepat Express', 'code' => 'sicepat', 'service' => 'REG', 'description' => 'Reguler', 'cost' => (7500 + 4000 * $zone) * $kg, 'etd' => (1 + $zone).'-'.(2 + $zone).' day'],
        ];
    }
}
