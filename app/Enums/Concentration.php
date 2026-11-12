<?php

namespace App\Enums;

/**
 * Konsentrasi minyak parfum. Diisi admin per produk (boleh kosong).
 */
enum Concentration: string
{
    case Extrait = 'extrait';
    case Parfum = 'parfum';
    case Edp = 'edp';
    case Edt = 'edt';
    case Edc = 'edc';

    public function label(): string
    {
        return match ($this) {
            self::Extrait => 'Extrait de Parfum',
            self::Parfum => 'Parfum',
            self::Edp => 'Eau de Parfum',
            self::Edt => 'Eau de Toilette',
            self::Edc => 'Eau de Cologne',
        };
    }

    /**
     * Singkatan untuk kartu dan detail: "EDP", "EDT".
     */
    public function short(): string
    {
        return match ($this) {
            self::Extrait => 'Extrait',
            self::Parfum => 'Parfum',
            default => strtoupper($this->value),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
