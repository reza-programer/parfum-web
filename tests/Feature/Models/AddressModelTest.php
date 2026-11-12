<?php

use App\Models\Address;
use App\Support\AddressText;

it('teks alamat lengkap memuat kelurahan kalau ada', function () {
    $address = Address::factory()->make([
        'detail' => 'Jl. Raya Darma No. 5',
        'subdistrict_id' => 16472,
        'subdistrict_name' => 'SAKERTA TIMUR',
        'district_name' => 'DARMA',
        'city_name' => 'KUNINGAN',
        'province_name' => 'JAWA BARAT',
        'postal_code' => '45562',
    ]);

    expect($address->fullText())->toBe('Jl. Raya Darma No. 5, Kel. Sakerta Timur, Darma, Kuningan, Jawa Barat 45562')
        ->and($address->needsSubdistrict())->toBeFalse();
});

it('alamat lama tanpa kelurahan tetap tampil rapi dan ditandai perlu dilengkapi', function () {
    $address = Address::factory()->make([
        'detail' => 'Jl. Dago 1', 'subdistrict_id' => null, 'subdistrict_name' => null,
        'district_name' => 'COBLONG', 'city_name' => 'BANDUNG', 'province_name' => 'JAWA BARAT', 'postal_code' => '40132',
    ]);

    expect($address->fullText())->toBe('Jl. Dago 1, Coblong, Bandung, Jawa Barat 40132')
        ->and($address->needsSubdistrict())->toBeTrue();
});

it('snapshot order ikut menyimpan kelurahan', function () {
    $address = Address::factory()->create(['subdistrict_id' => 16472, 'subdistrict_name' => 'SAKERTA TIMUR']);

    expect($address->fresh()->toSnapshot())
        ->toHaveKey('subdistrict_id', 16472)
        ->toHaveKey('subdistrict_name', 'SAKERTA TIMUR');
});

it('teks alamat dari snapshot sama formatnya, termasuk snapshot lama tanpa kelurahan', function () {
    $new = ['detail' => 'Jl. Raya Darma No. 5', 'subdistrict_name' => 'SAKERTA TIMUR', 'district_name' => 'DARMA', 'city_name' => 'KUNINGAN', 'province_name' => 'JAWA BARAT', 'postal_code' => '45562'];
    $old = ['detail' => 'Jl. Dago 1', 'district_name' => 'Coblong', 'city_name' => 'Bandung', 'province_name' => 'Jawa Barat', 'postal_code' => '40135'];

    expect(AddressText::fromSnapshot($new))->toBe('Jl. Raya Darma No. 5, Kel. Sakerta Timur, Darma, Kuningan, Jawa Barat 45562')
        ->and(AddressText::fromSnapshot($old))->toBe('Jl. Dago 1, Coblong, Bandung, Jawa Barat 40135')
        ->and(AddressText::fromSnapshot([]))->toBe('');
});
