# Latar Putih Gambar Produk — Implementation Plan

> Pelaksanaan: gunakan superpowers:executing-plans setelah pemilik menyetujui rencana.

**Goal:** Semua gambar parfum demo berlatar putih polos, dengan bentuk, label, dan warna botol tetap.

**Architecture:** Ubah latar di generator SVG produk, lalu render ulang aset WebP dan salin ke lokasi media demo yang dipakai aplikasi. Latar komponen gambar produk juga menjadi putih. Jangan mengubah backdrop bersama yang dipakai banner.

**Tech Stack:** Laravel Blade, SVG, renderer Node yang sudah digunakan project.

**Desain:** Putih #ffffff pada foto depan, foto kemasan, dan detail; bayangan kontak tipis netral untuk menjaga bentuk botol. Banner dan ubin kategori/aroma tetap.

## Langkah

- [x] Cek git status dan buat branch codex/background-produk-putih tanpa menimpa pekerjaan lain.
- [x] Periksa render-lib.mjs dan dependensi renderer; periksa contoh gambar asli.
- [x] Tambahkan pengujian piksel pada 36 gambar hasil generator; buktikan gagal sebelum perubahan. Latar komponen diverifikasi lewat browser.
- [x] Ubah database/seeders/images/source/render-products.mjs memakai bidang putih lokal, tanpa mengubah backdrop banner; netralkan bayangan produk jika berwarna.
- [x] Ubah resources/views/components/shop/product-image.blade.php menjadi bg-white.
- [x] Render ulang 36 gambar demo ke database/seeders/images/products; tinjau foto depan, kemasan, dan detail untuk memastikan warna serta label botol tetap.
- [x] Salin hanya file demo terkait ke storage/app/public/products/demo; jangan jalankan reset database atau menimpa foto unggahan admin.
- [x] Jalankan php artisan test dan npm run build; cek beranda, katalog, dan detail produk pada desktop/mobile.
- [x] Commit perubahan terkait dengan pesan yang jelas dan laporkan hasil verifikasi.

## Hasil verifikasi

- Test piksel sebelum perubahan: gagal, kanal warna latar 220 (target minimal 252 untuk toleransi kompresi WebP).
- `php artisan test --compact`: 358 lolos, 1 gagal karena Windows menolak rename cache Blade (`Access is denied`). Test piksel gambar lolos.
- Pengulangan `php artisan test --filter=AuthenticationTest` setelah pemeriksaan browser: 5 lolos, termasuk test navigasi yang sebelumnya terkendala cache.
- `npm run build`: berhasil. Pint untuk test baru: lolos. `git diff --check`: bersih.
- Browser: katalog desktop 1366px, detail produk dan beranda mobile 390px menampilkan foto berlatar putih.
- Konfigurasi lokal `.env` disesuaikan dari URL LAN lama ke `http://127.0.0.1:8000` karena sumber gambar sebelumnya tidak bisa diakses; `.env` tidak masuk commit.
- Generator banner dan pustaka botol bersama tidak berubah. Media demo hasil render disalin ke storage lokal.
