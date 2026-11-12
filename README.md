# Toko Parfum

Toko parfum online: Laravel 12, Livewire 3, Filament 3 (`/admin`), MySQL 8. Spesifikasi ada di [docs/PRD.md](docs/PRD.md), aturan kerja di [CLAUDE.md](CLAUDE.md), dan rencana per fase di [docs/plans](docs/plans).

## Menjalankan di lokal

Kebutuhan: PHP 8.2+ (ekstensi intl, pdo_mysql, gd, zip, mbstring, fileinfo), Composer, Node 20+, MySQL 8.

```bash
composer install
npm install && npm run build
cp .env.example .env   # isi DB_*, lalu:
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan app:create-admin
php artisan serve
```

- **Windows:** aktifkan OPcache di `php.ini` (`zend_extension=opcache`, `opcache.enable=1`, `opcache.revalidate_freq=0`). Tanpa OPcache, `php artisan serve` di Windows hanya melayani satu request dalam satu waktu dan membaca ulang ribuan file PHP setiap request, sehingga halaman (terutama login dan `/admin`) bisa loading belasan detik sampai timeout.
- **Customer uji dari seeder:** `customer@tokoparfum.test` / `password`.
- **Admin:** buat lewat `php artisan app:create-admin`. Seeder sengaja tidak membuat admin.
- **Foto produk demo:** katalog contoh (brand dan produk fiktif) memakai render digital bergaya foto studio, 3 gambar per produk, dari `DemoProductMediaSeeder`. File-nya ada di `database/seeders/images/products`, dan cara membuat ulangnya ada di `database/seeders/images/source/render-products.mjs`. Seeder ini juga mengisi konsentrasi, tidak menimpa foto atau konsentrasi yang sudah diisi admin, dan tidak mengubah harga maupun stok. Untuk memasang ke database yang sudah ada: `php artisan db:seed --class=DemoProductMediaSeeder`. Produk asli sebaiknya memakai foto asli yang diunggah lewat admin.
- **Ongkir tanpa API key:** set `RAJAONGKIR_FAKE=true`. Ini hanya berlaku di `APP_ENV=local` dan memakai data wilayah serta tarif contoh.
- **Pembayaran (Komerce sandbox):** isi `KOMERCE_PAYMENT_API_KEY` dan `KOMERCE_CALLBACK_KEY`, lalu biarkan `KOMERCE_IS_PRODUCTION=false`. Setelah checkout, halaman pesanan menampilkan tautan "Simulasikan pembayaran (sandbox)". Buka tautan itu, klik "Click here to simulate your payment", dan dalam beberapa detik status pesanan berubah jadi Dibayar karena halaman pesanan mengecek ke Komerce tiap 5 detik.
- **Callback di laptop:** Komerce tidak bisa memanggil `127.0.0.1`. Kalau perlu menguji callback, jalankan `ngrok http 8000` (ngrok ada di Laragon dan perlu authtoken), lalu isi `KOMERCE_CALLBACK_URL=https://xxxx.ngrok-free.app/pembayaran/komerce/callback`. Tanpa callback pun status tetap terbarui lewat cek status.
- **Email:** di lokal `MAIL_MAILER=log`, jadi isi email tertulis di `storage/logs/laravel.log`. Email dikirim lewat queue, jadi jalankan `php artisan queue:work` atau `php artisan schedule:work`.

## Test

```bash
php artisan test
./vendor/bin/pint --test
```

Test memakai database MySQL `parfum_test` (lihat `phpunit.xml`).

## Produksi (shared hosting)

- Cukup satu cron: `* * * * * cd /path/app && php artisan schedule:run >> /dev/null 2>&1`. Scheduler membatalkan order yang lewat 24 jam (`orders:expire`, yang lebih dulu mengecek status bayar ke Komerce) dan memproses queue email (`queue:work --stop-when-empty`).
- Isi `RAJAONGKIR_API_KEY`, lalu `RAJAONGKIR_ORIGIN` dengan ID kecamatan gudang. Pastikan `RAJAONGKIR_FAKE=false`.
- Isi key Komerce Payment produksi, lalu set `KOMERCE_IS_PRODUCTION=true`. Daftarkan URL callback `https://domain-toko/pembayaran/komerce/callback` di dashboard Komerce, atau biarkan aplikasi mengirimnya otomatis di setiap pembayaran.
- Pembayaran yang masuk untuk pesanan yang sudah batal, atau pembayaran ganda, ditandai **Perlu refund manual** di detail pesanan admin.
- `APP_ENV=production`, `APP_DEBUG=false`, dan HTTPS wajib.

## Status fitur

Semua 14 fitur PRD sudah dikerjakan. Pembayaran (fitur 8) memakai Komerce Payment (VA bank + QRIS) sebagai pengganti Midtrans. Keputusan, hasil uji sandbox, dan rancangannya ada di [docs/plans/pembayaran-komerce.md](docs/plans/pembayaran-komerce.md).
