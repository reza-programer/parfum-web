# Toko Parfum - Aturan Project

Baca docs/PRD.md sebelum mengerjakan apa pun. PRD adalah sumber kebenaran untuk fitur, skema database, dan prioritas (P0 dulu, baru P1).

## Stack
- Laravel 11/12, PHP 8.2+, MySQL 8
- Sisi customer: Blade + Livewire 3 + Tailwind + Alpine
- Panel admin: Filament 3 di /admin (satu project, satu database)
- Ongkir: RajaOngkir lewat app/Services/ShippingService.php
- Pembayaran: Komerce Payment (VA bank + QRIS) lewat app/Services/PaymentService.php. Rencana dan hasil uji sandbox ada di docs/plans/pembayaran-komerce.md
- Chatbot: rule-based dari tabel faqs lewat app/Services/ChatbotService.php
- Test: Pest

## Alur kerja (wajib, pakai skill Superpowers)
1. Fitur baru: brainstorming dulu, tanya kalau ada yang ambigu, jangan menebak.
2. Tulis rencana dengan writing-plans, simpan di docs/plans/, tunggu persetujuan saya.
3. Kerjakan dengan test-driven-development: test gagal dulu, baru kode.
4. Bug: pakai systematic-debugging, cari akar masalah sebelum mengubah kode.
5. Sebelum bilang selesai: verification-before-completion, jalankan php artisan test dan tunjukkan hasilnya.
6. Satu fitur = satu branch + commit kecil dengan pesan jelas.

## UI dan teks (pakai skill unslop)
- Gaya newbalance.co.id (docs/plans/redesign-nb.md, keputusan pemilik 13 Nov 2026):
  - latar putih polos tanpa kartu atau bayangan, pemisah memakai garis tipis atau ruang kosong;
  - teks hitam, aksen merah `primary` hanya untuk tombol beli, titik carousel aktif, dan brand di detail;
  - sudut tegas (semua token radius 0, kecuali lingkaran);
  - judul bagian dan tombol berhuruf kapital tebal, teks isi kalimat biasa;
  - font Inter, serif hanya untuk logo.
  - Pakai komponen yang ada: `.btn-*`, `.field`, `.chip`, `.tag`, `x-ui.badge`, `x-shop.product-card`.
- Kartu produk polos: foto, satu tag hitam (Terlaris/Baru/Habis), wishlist, nama, harga. Rating, jumlah terjual, dan stok terbatas hanya di halaman detail.
- Label, promo, rating, dan jumlah terjual harus dari data asli. Jangan mengarang diskon, "gratis ongkir", atau angka penjualan.
- Jangan meniru identitas brand yang dijadikan acuan (New Balance, Shopee, Tokopedia): logo, nama, foto, font, warna khas, dan teks kampanye mereka. Yang boleh diikuti hanya pola tata letak dan gaya visual umum.
- Gambar beranda dan menu (banner, ubin aroma, ubin kategori): dari `database/seeders/images/source/render-banners.mjs`. Teks dan tombolnya ditulis di HTML.
- Hindari tampilan template AI: gradien ungu, emoji di tombol, bayangan tebal, copy seperti "Elevate your scent journey".
- Semua teks UI dalam bahasa Indonesia yang natural dan singkat.
- Ikuti wireframe di PRD bagian Wireframe.
- Gambar wireframe belum ada di docs/PRD.md (hanya placeholder "[embedded content: ...]"). Pakai deskripsi teks di bawah tiap wireframe; kalau tata letak tidak jelas, tanya dulu, jangan mengarang.

## Batasan teknis
- Target shared hosting: tidak ada proses yang jalan terus. Queue pakai driver database, diproses lewat scheduler (cron schedule:run tiap menit). Chat realtime pakai Livewire wire:poll, bukan Reverb.
- Stok dikurangi di dalam DB transaction dengan lockForUpdate.
- Status order Dibayar HANYA lewat PaymentService, berdasarkan jawaban Komerce dari server ke server: callback yang tanda tangan HMAC-nya valid, atau API cek status. Nominal harus cocok. Tidak ada tombol "tandai dibayar" manual, dan halaman sukses di browser tidak pernah mengubah status.
- Test tidak boleh memanggil API asli: pakai Http::fake (FakeRajaOngkir, tests/Support/FakeKomerce). TestCase memakai Http::preventStrayRequests().
- API key hanya di .env, jangan pernah di-commit.
- Harga, nama produk, dan alamat disalin ke order (snapshot).

## Perintah
- php artisan test
- ./vendor/bin/pint
- npm run dev / npm run build
