# Daftar kurir dua kolom

Tujuan: memendekkan daftar kurir di checkout dengan dua pilihan per baris pada layar lebar.

## Desain

- Container pilihan kurir di `resources/views/livewire/checkout.blade.php` menjadi `grid grid-cols-1 gap-2 md:grid-cols-2`.
- Layar di bawah 768px tetap satu kolom agar nama layanan, estimasi, dan harga terbaca.
- Urutan pilihan tetap dari kiri ke kanan per baris; data ongkir dan radio Livewire tetap memakai implementasi sekarang.

## Pelaksanaan setelah persetujuan

- [x] Buat branch `codex/kurir-dua-kolom`.
- [x] Ubah kelas container pilihan kurir saja.
- [ ] Build frontend dan periksa tampilan desktop/mobile, termasuk nama layanan panjang dan status pilihan aktif.
- [x] Jalankan `php artisan test`, laporkan hasil, dan commit perubahan terkait saja.

Perubahan ini hanya tata letak CSS; verifikasi visual dan test checkout yang sudah ada dipakai tanpa menambahkan test yang sekadar menyalin nama kelas.

## Verifikasi

- `npm run build`: berhasil.
- `php artisan test --compact`: 359 passed, 2197 assertions.
- `git diff --check`: bersih.
- Pemeriksaan visual checkout terautentikasi belum dilakukan; perubahan hanya pada grid responsif pembungkus pilihan kurir. Radio, data layanan, serta harga tidak berubah.
