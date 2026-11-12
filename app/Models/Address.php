<?php

namespace App\Models;

use App\Support\AddressText;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'label', 'recipient', 'phone',
        'province_id', 'province_name', 'city_id', 'city_name', 'district_id', 'district_name',
        'subdistrict_id', 'subdistrict_name',
        'detail', 'postal_code', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'province_id' => 'integer',
            'city_id' => 'integer',
            'district_id' => 'integer',
            'subdistrict_id' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullText(): string
    {
        return AddressText::fromSnapshot($this->only([
            'detail', 'subdistrict_name', 'district_name', 'city_name', 'province_name', 'postal_code',
        ]));
    }

    /**
     * Alamat lama (sebelum ada kelurahan) tetap sah, tapi sebaiknya dilengkapi.
     */
    public function needsSubdistrict(): bool
    {
        return $this->subdistrict_id === null;
    }

    /**
     * Data alamat yang disalin ke order (snapshot).
     *
     * @return array<string, mixed>
     */
    public function toSnapshot(): array
    {
        return $this->only([
            'label', 'recipient', 'phone',
            'province_id', 'province_name', 'city_id', 'city_name', 'district_id', 'district_name',
            'subdistrict_id', 'subdistrict_name',
            'detail', 'postal_code',
        ]);
    }
}
