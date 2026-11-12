# Pembayaran Komerce (fitur 8) — Rencana

Status: **disetujui 12 Nov 2026**, sedang dikerjakan. Branch: `feat/pembayaran-komerce`.

Midtrans diganti sepenuhnya dengan **Komerce Payment API** (VA bank + QRIS). Ongkir tetap lewat RajaOngkir versi Komerce yang sudah ada; untuk ongkir hanya API key dan ID gudang yang diisi di `.env`.

## Keputusan (disetujui pemilik, 12 Nov 2026)

1. **Gateway:** Komerce Payment, bukan Midtrans. PRD, CLAUDE.md, dan README diperbarui.
2. **QRIS:** lewat Payment API (`payment_type: qris`). QR dibuat Komerce dan berlaku 5 menit. Qrisly tidak dipakai.
3. **Tampilan:** customer memilih bank atau QRIS di checkout. Nomor VA atau kode QR tampil di halaman pesanan milik toko, bukan di halaman bayar Komerce.
4. **Status Dibayar** boleh berasal dari dua sumber. Keduanya server-ke-server, jadi tidak bisa dipalsukan dari browser:
   - callback Komerce yang tanda tangannya valid;
   - API cek status Komerce yang dipanggil server.

## Yang sudah dicek ke API asli

- **RajaOngkir:** key valid. Provinsi, kota, kecamatan, dan ongkir dari gudang ID `1331` terbaca (16 opsi kurir untuk 500 g ke Bandung).
- **Komerce Payment sandbox:** key valid. `GET /api/v1/user/methods` mengembalikan:
  - VA 11 bank: BCA, BNI, MANDIRI, PERMATA, SAHABAT_SAMPOERNA, BRI, CIMB, BSI, BJB, DBS, BNC;
  - QRIS dengan batas Rp10.000 sampai Rp10.000.000;
  - minimal Rp10.000 untuk semua metode.

## API Komerce yang dipakai

Base URL sandbox: `https://api-sandbox.collaborator.komerce.id/user`. Base URL produksi: `https://api.collaborator.komerce.id/user`. Header `x-api-key`.

| Endpoint | Dipakai untuk |
|---|---|
| `GET /api/v1/user/methods` | Daftar bank dan QRIS di checkout (cache 1 jam) |
| `POST /api/v1/user/payment/create` | Buat VA (`channel_code`, `expiry_duration` ≥ 3600 detik) atau QRIS (berlaku 5 menit) |
| `GET /api/v1/user/payment/status/{payment_id}` | Cek status: `PENDING`, `PAID`, `EXPIRED`, `CANCELED`. Batas 1 request per 3 detik per payment |
| `POST /api/v1/user/payment/cancel` | Batalkan VA/QR yang masih `PENDING` |

**Callback:** header `X-Callback-Api-Key` berisi HMAC-SHA256 dari raw body dengan `KOMERCE_CALLBACK_KEY`. Dokumentasi tidak menyebut format payload maupun encoding hash (hex atau base64). Karena itu callback hanya dipakai sebagai **pemicu**: setelah tanda tangan valid, server memanggil API cek status. Hasil cek status itulah yang dipakai, termasuk mencocokkan nominal.

## Rancangan

### Konfigurasi
- **`config/services.php` → `komerce`:** `payment_key`, `callback_key`, `production` (bool), dan `base_url` yang dipilih dari `production`.
- **`.env.example`:** tambah `KOMERCE_PAYMENT_API_KEY=`, `KOMERCE_CALLBACK_KEY=`, `KOMERCE_IS_PRODUCTION=false`.
- **ID gudang:** `SHIPPING_ORIGIN_DISTRICT_ID` dari pemilik diisi ke `RAJAONGKIR_ORIGIN` yang sudah ada, tanpa variabel baru.

### Database (migration baru, migration lama tidak diubah)
- **Satu order bisa punya beberapa percobaan bayar**, misalnya QR kedaluwarsa atau customer ganti bank. Unique di `payments.order_id` dihapus.
- **Ganti nama:** `midtrans_order_id` → `reference`, yaitu `order_id` yang dikirim ke Komerce, contoh `TP-2611-0001-2`.
- **Hapus:** `snap_token`.
- **Tambah kolom:** `provider_payment_id` (unique, contoh `KOMPAY-...`), `channel_code`, `va_number`, `qr_string` (text), `amount`, `expires_at`.
- **`status`:** `pending`, `paid`, `expired`, atau `canceled`.
- **Relasi `Order`:** `payments()` (hasMany). `payment()` tetap ada sebagai `latestOfMany()`, supaya view dan Filament yang sudah ada tetap jalan.

### `App\Services\PaymentService` (satu-satunya pintu ke Komerce)
- **`methods(int $amount)`:** daftar metode yang aktif untuk nominal itu (sesuai `min_amount`/`max_amount`), di-cache.
- **`create(Order $order, string $method, ?string $bank)`:**
  - Membatalkan payment lama yang masih pending, lalu membuat payment baru.
  - VA berlaku sampai `orders.expires_at` (minimal 1 jam). Item dikirim sebagai satu baris "Pesanan {kode}" senilai total, supaya jumlah item selalu sama dengan `amount` (ada ongkir dan diskon).
  - Panggilan HTTP dilakukan **di luar** transaction stok.
- **`refresh(Payment $payment)`:** cek status ke API, dengan throttle 3 detik per payment lewat `Cache::lock`.
  - `PAID` dengan nominal cocok → `OrderService::markPaid()`.
  - `PAID` dengan nominal berbeda → tidak dibayar dan dicatat di log.
  - `EXPIRED`/`CANCELED` → payment ditandai, order tetap Menunggu Bayar sampai batas 24 jam.
- **`cancel(Payment $payment)`:** dipanggil saat customer ganti metode, membatalkan pesanan, atau pesanan kedaluwarsa.
- **Error API:** dilempar sebagai `PaymentException` dengan pesan singkat untuk customer. Detailnya hanya masuk log.

### `OrderService`
- **`markPaid()`:** komentar dan status payment disesuaikan (`paid`, bukan `settlement`). Tetap idempotent.
- **Uang masuk setelah pesanan batal:** payment ditandai `paid`, order tetap Dibatalkan, lalu dicatat sebagai "perlu refund manual" dan tampil di admin. Callback tetap dijawab 200 supaya tidak dikirim ulang terus.
- **`cancelFrom()`:** setelah transaction selesai, membatalkan payment Komerce yang masih pending.
- **`expireOverdue()`:** sebelum membatalkan, cek status payment yang masih pending. Pesanan yang ternyata sudah dibayar tidak ikut dibatalkan.

### Callback
- **Rute:** `POST /pembayaran/komerce/callback`, dikecualikan dari CSRF di `bootstrap/app.php`, dengan rate limit.
- **Tanda tangan tidak valid:** jawab 401 tanpa perubahan apa pun.
- **Tanda tangan valid:** cari payment dari `payment_id`/`order_id` di payload, panggil `refresh()`, lalu jawab 200. Semua callback dicatat di log, tanpa header kunci.

### Tampilan customer (Bahasa Indonesia singkat, mengikuti gaya yang ada)
- **Checkout:**
  - Bagian baru "Metode pembayaran" berisi daftar bank VA dengan logo kecil, plus QRIS. Wajib dipilih.
  - Tombol "Bayar Sekarang" membuat order, lalu langsung membuat payment.
  - Kalau pembuatan payment gagal, order tetap tersimpan dan halaman pesanan menampilkan tombol "Coba lagi".
- **Halaman pesanan (Menunggu Bayar):**
  - **VA:** nama bank, nomor VA dengan tombol "Salin", nominal, dan batas bayar.
  - **QRIS:** gambar QR (SVG dari `qr_string`), hitung mundur 5 menit, dan tombol "Buat QR baru" setelah kedaluwarsa.
  - Tautan "Ganti metode" membuka pilihan metode lagi.
  - `wire:poll.5s` memanggil `refresh()` selama payment pending. Begitu dibayar, halaman langsung menampilkan "Pembayaran diterima".
- **QR:** dirender di server dengan `bacon/bacon-qr-code` (dependency baru, output SVG, tanpa imagick).

### Panel admin
- **Aksi "Cek status"** di halaman pesanan, hanya untuk Menunggu Bayar. Memanggil `refresh()`.
- **Riwayat percobaan bayar:** metode, bank, status, waktu bayar, dan penanda "perlu refund".

### Scheduler
- **`orders:expire` (tiap menit)** ikut mengecek status sebelum membatalkan. Tidak perlu cron baru.

## Urutan kerja (TDD, commit kecil)

1. **Konfigurasi dan dokumen:** `.env.example`, `config/services.php`, PRD, CLAUDE.md, README.
2. **Migration dan model `Payment`/`Order`:** test model dan relasi.
3. **`PaymentService`:** semua panggilan memakai `Http::fake` lewat `App\Support\FakeKomerce` (pola sama dengan `FakeRajaOngkir`). Test mencakup:
   - isi request `create` untuk VA dan QRIS;
   - filter metode berdasarkan nominal;
   - status `PAID`, nominal tidak cocok, `EXPIRED`, dan throttle;
   - error API.
4. **Perubahan `OrderService`:** cancel dan expire membatalkan payment; expire mengecek status dulu; uang masuk setelah batal.
5. **Callback controller:**
   - tanda tangan salah → 401;
   - tanda tangan benar → Dibayar, dan event `OrderPaid` hanya sekali;
   - callback ganda tetap idempotent;
   - payment tidak dikenal → 404.
6. **Checkout:** pilihan metode, validasi, dan payment dibuat setelah order.
7. **Halaman pesanan:** VA, QR, ganti metode, polling.
8. **Admin:** "Cek status" dan riwayat payment.
9. **Verifikasi:**
   - `php artisan test` dan `./vendor/bin/pint --test`;
   - uji manual di sandbox: buat VA dan QR asli, lalu tes callback lewat `ngrok` (sudah ada di Laragon).

## Hasil uji sandbox sebelum menulis kode (12 Nov 2026)

- **`payment_type`:** VA harus `bank_transfer`. Nilai `va` ditolak dengan pesan "payment_type must be bank_transfer or qris".
- **Respons `payment/create`:** `payment_id` (format `KPAY-xxxx/KM/2026`, **mengandung garis miring**, jadi harus di-URL-encode untuk endpoint status), `payment_url`, `va_number`, `qr_string`, `bank_code`, `bank_name`, `amount`, `status`, `expired_at` (ISO 8601, +07:00).
- **QRIS:** berlaku 5 menit. Di sandbox `qr_string` berisi teks contoh, bukan QRIS asli.
- **Respons status:** `payment_id`, `order_id`, `amount`, `merchant_receives`, `payment_type`, `status`, `paid_at` (format `Y-m-d H:i:s` WIB, string kosong kalau belum bayar), dan `expired_at`. Request kedua dalam 3 detik dijawab 429.
- **Simulasi bayar sandbox:** buka `payment_url`, lalu klik "Click here to simulate your payment". Status langsung berubah jadi `PAID`. Di mode sandbox, halaman pesanan menampilkan tautan ini.
- **Cancel:**
  - VA yang pending → 200 dengan status `CANCELED`.
  - QRIS → 404 "item does not exist", jadi QRIS tidak bisa dibatalkan dan hanya ditandai batal di database.
  - Payment yang sudah `PAID` → 400.
- **Biaya di sandbox:** VA Rp10.000 → merchant menerima Rp5.560. QRIS Rp10.000 → Rp9.971.

## Risiko dan yang belum pasti

- **Simulasi bayar di sandbox:** dokumentasi tidak menjelaskan caranya. Saya cek dashboard atau halaman `pay-sandbox.komerce.id` saat langkah 9. Kalau tidak ada, saya tanyakan ke pemilik.
- **Isi respons `payment/create`** (nama field nomor VA, `payment_id`, `qr_string`, `expired_at`) belum tertulis lengkap di dokumentasi. Saya cek dengan satu request sandbox asli sebelum menulis parser, lalu memakai respons itu sebagai fixture test.
- **Encoding HMAC callback (hex atau base64):** keduanya diterima dan dibandingkan dengan `hash_equals`. Format yang benar dipastikan lewat callback sandbox.
- **Nilai `callback_API_KEY` di request create:** diasumsikan sama dengan `KOMERCE_CALLBACK_KEY`, lalu dicek saat uji sandbox.
