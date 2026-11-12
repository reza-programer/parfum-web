# Kelurahan/Desa dan Kode Pos Otomatis — Rencana

Status: **disetujui 12 Nov 2026**, sedang dikerjakan. Branch: `feat/kelurahan-kode-pos` (dari `main` terbaru).

Permintaan pemilik (12 Nov 2026): form alamat jangan berhenti di kecamatan. Tambahkan kelurahan/desa (contoh: Kuningan › Darma › Sakerta Timur), dan kode pos langsung terisi begitu kelurahan dipilih.

## Keputusan pemilik

1. **Kelurahan wajib dipilih.** Pengecualian: kalau RajaOngkir tidak punya data kelurahan untuk kecamatan itu, pilihan ini boleh kosong dan kode pos diketik manual.
2. **Kode pos terisi otomatis dari kelurahan,** tapi masih bisa diubah customer (berjaga-jaga kalau data RajaOngkir kurang tepat).
3. **Alamat lama tanpa kelurahan tetap bisa dipakai checkout.** Di daftar alamat muncul catatan "Lengkapi kelurahan". Saat alamat itu diubah, kelurahan wajib diisi.

## Hasil cek API RajaOngkir (Komerce)

- `GET /destination/sub-district/{district_id}` mengembalikan `id`, `name`, dan `zip_code`.
  - Contoh untuk Darma (`district_id` 1235): 19 desa, termasuk Sakerta Timur dan Sakerta Barat, semuanya berkode pos `45562`.
- Data di tingkat kecamatan (`/destination/district/{city_id}`) **tidak** punya kode pos. Jadi pengisian otomatis memang harus dari kelurahan.
- "Kopeng" tidak ada di daftar Darma versi RajaOngkir. Kemungkinan itu dusun atau ada di kecamatan lain. Form hanya bisa menampilkan kelurahan yang tersedia di RajaOngkir; detail seperti dusun, RT/RW, dan patokan tetap ditulis di kolom alamat lengkap.
- **Ongkir tetap dihitung per kecamatan** (`calculate/district/domestic-cost`). Kelurahan tidak mengubah ongkir.

## Rancangan

### Database
- **Migration baru (nullable, data lama aman):** `addresses.subdistrict_id` (unsignedInteger) dan `addresses.subdistrict_name` (string).
- **Snapshot order** (`Address::toSnapshot()`) ikut menyimpan `subdistrict_id` dan `subdistrict_name`. Order lama tidak berubah.

### `ShippingService`
- **`subdistricts(int $districtId)`:** mengembalikan `[id => ['name' => ..., 'zip' => ...]]` dan di-cache 30 hari seperti wilayah lain.
- **`FakeRajaOngkir`** mendapat data kelurahan contoh (dengan kode pos) untuk test dan mode `RAJAONGKIR_FAKE`.

### Form alamat (`AddressForm`)
- **Pilihan Kelurahan/Desa** muncul setelah Kecamatan. Isinya ikut berganti saat kecamatan diganti, dan pilihan di bawahnya dikosongkan bila provinsi, kota, atau kecamatan diganti.
- **Memilih kelurahan mengisi kode pos otomatis.** Kolom kode pos tetap bisa diketik. Mengganti kelurahan menimpa kode pos dengan kode pos kelurahan yang baru.
- **Validasi:** `subdistrict_id` wajib dan harus termasuk kelurahan milik kecamatan terpilih (nilai dari luar daftar ditolak). Kalau daftar kelurahan untuk kecamatan itu kosong, field ini tidak wajib.
- **Tata letak:** provinsi, kota/kabupaten, kecamatan, dan kelurahan dalam grid dua kolom, lalu kode pos dan alamat lengkap. Di HP satu kolom. Gaya mengikuti design system monokrom.

### Tampilan alamat
- **`Address::fullText()`:** "detail, Kel. Sakerta Timur, Darma, Kuningan, Jawa Barat 45562". Bagian kelurahan dilewati kalau kosong.
- **Helper bersama untuk teks alamat dari snapshot,** dipakai detail pesanan, admin (OrderResource), dan email invoice, supaya formatnya sama di semua tempat.
- **Daftar alamat:** alamat tanpa kelurahan menampilkan catatan kecil "Lengkapi kelurahan" dengan link Ubah.

## Urutan kerja (TDD, commit kecil)

1. **Migration dan model:** kolom, fillable, snapshot, `fullText()` (test model).
2. **`ShippingService::subdistricts()` dan `FakeRajaOngkir`:**
   - test dengan `Http::fake`;
   - format respons sesuai API asli;
   - cache.
3. **`AddressForm`** (test Livewire):
   - daftar kelurahan mengikuti kecamatan;
   - kode pos otomatis tapi bisa diubah;
   - kelurahan wajib;
   - kelurahan dari kecamatan lain ditolak;
   - kecamatan tanpa data kelurahan boleh kosong;
   - alamat lama bisa diubah dan wajib melengkapi kelurahan.
4. **Tampilan:**
   - helper teks alamat;
   - detail pesanan, admin, email invoice, checkout;
   - catatan "Lengkapi kelurahan".
5. **Verifikasi:**
   - `php artisan test`, Pint, build;
   - uji manual di browser dengan data asli (Kuningan › Darma › Sakerta Timur → 45562) di lebar HP dan desktop.
