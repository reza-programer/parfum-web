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
- Hindari tampilan template AI: gradien ungu, emoji di tombol, kartu dengan bayangan berlebihan, copy seperti "Elevate your scent journey".
- Nuansa toko parfum: bersih, banyak ruang kosong, foto produk jadi fokus.
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
