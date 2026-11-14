# Perbaikan responsif inbox chat admin

## Temuan

Kolom daftar percakapan ditetapkan 320px pada desktop (`lg:grid-cols-[320px_1fr]`). Empat tab status Filament tidak muat di ruang itu dan komponen tab menggunakan scroll horizontal. Grid menggunakan `1fr` tanpa minimum nol, dan teks pesan panjang belum memiliki aturan pemenggalan kata.

## Desain yang diusulkan

- Empat tab status tersusun 2 × 2 di panel daftar percakapan agar semua label dan badge terlihat tanpa scroll horizontal.
- Desktop tetap dua panel: daftar percakapan dan isi chat; kolom isi memakai `minmax(0,1fr)` dengan `min-w-0` pada panel.
- HP memakai satu kolom: daftar percakapan di atas dan isi chat di bawah. Daftar panjang dibatasi tinggi dan dapat digulir vertikal.
- Nama, email, dan isi pesan panjang tidak boleh melebarkan halaman. Pesan tanpa spasi dapat terpotong ke baris berikutnya.
- Teks kosong diganti menjadi “Pilih percakapan untuk melihat pesan.” agar sesuai desktop maupun HP.
- Pertahankan polling, pilihan tab, balasan, penutupan percakapan, serta data yang ada.

## Pelaksanaan setelah persetujuan

- [x] Buat branch `codex/inbox-chat-responsif`.
- [x] Perbaiki `resources/views/filament/pages/chat-inbox.blade.php`; gunakan CSS yang dibatasi khusus inbox pada tema admin.
- [x] Build aset dan cek browser desktop serta HP (360–390px), tab aktif, badge, kontak, dan pembungkusan pesan.
- [x] Jalankan `php artisan test`, laporkan hasil, lalu commit file terkait.

Test perilaku chat yang sudah ada dipertahankan; pemeriksaan responsif dilakukan secara visual, bukan test yang hanya menyalin kelas CSS.

## Bukti pemeriksaan

- `npm run build` berhasil; `git diff --check` bersih.
- Desktop 1366px: empat tab tersusun 2 × 2, lebar dokumen sama dengan viewport (1366px).
- HP 360px: daftar dan detail chat tersusun vertikal, lebar dokumen 345px (sisa ruang scrollbar), tanpa overflow horizontal.
- HP 390px: lebar dokumen 375px, tanpa overflow horizontal.
- Percakapan yang ada dibuka tanpa mengirim atau menutup chat. Tab aktif, badge jumlah percakapan, kontak, serta pesan tampil.
- Computed style gelembung pesan: `overflow-wrap: anywhere`; scrollWidth sama dengan clientWidth. Tidak menambahkan pesan sintetis ke percakapan customer untuk pengujian.
- `php artisan test --compact`: 361 test lulus (2201 assertions), durasi 367,28 detik.
