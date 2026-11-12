<?php

use App\Livewire\Account\Addresses;
use App\Livewire\Account\AddressForm;
use App\Models\Address;
use App\Models\User;
use App\Services\AddressBook;
use App\Support\FakeRajaOngkir;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

beforeEach(function () {
    config(['services.rajaongkir.base_url' => 'https://rajaongkir.test/api/v1']);
    FakeRajaOngkir::register('https://rajaongkir.test/api/v1');
    $this->user = User::factory()->create();
});

it('halaman alamat butuh login', function () {
    $this->get('/akun/alamat')->assertRedirect('/login');
    $this->actingAs($this->user)->get('/akun/alamat')->assertOk()->assertSee('Alamat');
});

it('dropdown kota dan kecamatan mengikuti pilihan di atasnya', function () {
    Livewire::actingAs($this->user)->test(AddressForm::class)
        ->assertSee('Jawa Barat')
        ->set('province_id', 9)
        ->assertSee('Bandung')
        ->set('city_id', 23)
        ->assertSee('Coblong')
        ->set('province_id', 6)
        ->assertSet('city_id', null)
        ->assertSet('district_id', null);
});

it('menyimpan alamat baru dengan nama wilayah dari RajaOngkir', function () {
    Livewire::actingAs($this->user)->test(AddressForm::class)
        ->set('label', 'Rumah')
        ->set('recipient', 'Budi')
        ->set('phone', '081234567890')
        ->set('province_id', 9)
        ->set('city_id', 23)
        ->set('district_id', 1001)
        ->set('subdistrict_id', 10011)
        ->set('detail', 'Jl. Dago No. 1')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('address-saved');

    $address = $this->user->addresses()->first();

    expect($address->province_name)->toBe('JAWA BARAT')
        ->and($address->city_name)->toBe('BANDUNG')
        ->and($address->district_name)->toBe('COBLONG')
        ->and($address->subdistrict_id)->toBe(10011)
        ->and($address->subdistrict_name)->toBe('DAGO')
        ->and($address->postal_code)->toBe('40135')
        ->and($address->is_default)->toBeTrue();
});

it('pilihan kelurahan mengikuti kecamatan dan kode pos terisi otomatis tapi bisa diubah', function () {
    Livewire::actingAs($this->user)->test(AddressForm::class)
        ->set('province_id', 9)
        ->set('city_id', 23)
        ->set('district_id', 1001)
        ->assertSee('Dago')
        ->assertSee('Lebakgede')
        ->set('subdistrict_id', 10011)
        ->assertSet('postal_code', '40135')
        ->set('subdistrict_id', 10012)
        ->assertSet('postal_code', '40132')
        ->set('postal_code', '40199')
        ->assertSet('postal_code', '40199')
        ->set('district_id', 1002)
        ->assertSet('subdistrict_id', null)
        ->assertSee('Pasteur')
        ->assertDontSee('Lebakgede')
        ->set('province_id', 6)
        ->assertSet('district_id', null)
        ->assertSet('subdistrict_id', null);
});

it('kelurahan wajib dipilih kalau kecamatan punya data kelurahan', function () {
    Livewire::actingAs($this->user)->test(AddressForm::class)
        ->set('label', 'Rumah')->set('recipient', 'Budi')->set('phone', '081234567890')
        ->set('province_id', 9)->set('city_id', 23)->set('district_id', 1001)
        ->set('detail', 'Jl. Dago')->set('postal_code', '40135')
        ->call('save')
        ->assertHasErrors(['subdistrict_id' => 'required']);
});

it('menolak kelurahan dari kecamatan lain', function () {
    Livewire::actingAs($this->user)->test(AddressForm::class)
        ->set('label', 'Rumah')->set('recipient', 'Budi')->set('phone', '081234567890')
        ->set('province_id', 9)->set('city_id', 23)->set('district_id', 1001)
        ->set('subdistrict_id', 10021) // Pasteur, milik Sukajadi
        ->set('detail', 'Jl. Dago')->set('postal_code', '40135')
        ->call('save')
        ->assertHasErrors('subdistrict_id');

    expect($this->user->addresses()->count())->toBe(0);
});

it('kecamatan yang tidak punya data kelurahan boleh disimpan tanpa kelurahan', function () {
    Livewire::actingAs($this->user)->test(AddressForm::class)
        ->set('label', 'Vila')->set('recipient', 'Budi')->set('phone', '081234567890')
        ->set('province_id', 1)->set('city_id', 114)->set('district_id', 5102)
        ->assertSee('Kode pos diketik manual')
        ->set('detail', 'Jl. Gatot Subroto')->set('postal_code', '80116')
        ->call('save')
        ->assertHasNoErrors();

    expect($this->user->addresses()->first()->subdistrict_id)->toBeNull();
});

it('alamat lama tanpa kelurahan wajib dilengkapi saat diubah', function () {
    $address = app(AddressBook::class)->add($this->user, Address::factory()->raw([
        'subdistrict_id' => null, 'subdistrict_name' => null,
    ]));

    Livewire::actingAs($this->user)->test(AddressForm::class, ['addressId' => $address->id])
        ->assertSet('subdistrict_id', null)
        ->call('save')
        ->assertHasErrors('subdistrict_id')
        ->set('subdistrict_id', 10013)
        ->call('save')
        ->assertHasNoErrors();

    expect($address->fresh()->subdistrict_name)->toBe('SEKELOA')
        ->and($address->fresh()->postal_code)->toBe('40134');
});

it('daftar alamat menandai alamat lama yang belum punya kelurahan', function () {
    $book = app(AddressBook::class);
    $book->add($this->user, Address::factory()->raw(['label' => 'Lama', 'subdistrict_id' => null, 'subdistrict_name' => null]));

    Livewire::actingAs($this->user)->test(Addresses::class)->assertSee('Lengkapi kelurahan');

    $this->user->addresses()->update(['subdistrict_id' => 10011, 'subdistrict_name' => 'DAGO']);

    Livewire::actingAs($this->user)->test(Addresses::class)->assertDontSee('Lengkapi kelurahan');
});

it('menolak kecamatan yang bukan milik kota terpilih', function () {
    Livewire::actingAs($this->user)->test(AddressForm::class)
        ->set('label', 'Rumah')->set('recipient', 'Budi')->set('phone', '081234567890')
        ->set('province_id', 9)->set('city_id', 23)
        ->set('district_id', 2101)
        ->set('detail', 'Jl. Dago')->set('postal_code', '40135')
        ->call('save')
        ->assertHasErrors('district_id');
});

it('mengubah alamat milik sendiri', function () {
    $address = app(AddressBook::class)->add($this->user, Address::factory()->raw(['province_id' => 9, 'city_id' => 23, 'district_id' => 1001]));

    Livewire::actingAs($this->user)->test(AddressForm::class, ['addressId' => $address->id])
        ->assertSet('recipient', $address->recipient)
        ->set('recipient', 'Siti')
        ->call('save')
        ->assertHasNoErrors();

    expect($address->fresh()->recipient)->toBe('Siti');
});

it('tidak bisa membuka alamat milik orang lain', function () {
    $other = Address::factory()->create();

    expect(fn () => Livewire::actingAs($this->user)->test(AddressForm::class, ['addressId' => $other->id]))
        ->toThrow(ModelNotFoundException::class);
});

it('menjadikan alamat utama dan menghapus dari daftar', function () {
    $book = app(AddressBook::class);
    $first = $book->add($this->user, Address::factory()->raw());
    $second = $book->add($this->user, Address::factory()->raw());

    Livewire::actingAs($this->user)->test(Addresses::class)
        ->call('makeDefault', $second->id)
        ->call('delete', $first->id);

    expect($second->fresh()->is_default)->toBeTrue()
        ->and(Address::find($first->id))->toBeNull();
});

it('tidak bisa menghapus alamat milik orang lain', function () {
    $other = Address::factory()->create();

    expect(fn () => Livewire::actingAs($this->user)->test(Addresses::class)->call('delete', $other->id))
        ->toThrow(ModelNotFoundException::class);

    expect(Address::find($other->id))->not->toBeNull();
});
