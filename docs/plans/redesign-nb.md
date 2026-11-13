# Tampilan Gaya newbalance.co.id (Redesign v3) — Rencana

Status: **disetujui 13 Nov 2026**, sedang dikerjakan. Branch: `feat/tampilan-nb` (dari `feat/tampilan-marketplace`).

Permintaan pemilik (13 Nov 2026): "UNDO", lalu "Ubah jadi seperti ini full: https://www.newbalance.co.id/".

Rencana ini **menggantikan tampilan marketplace** (`redesign-marketplace.md`). Fitur, logika, dan data tetap; yang berubah tampilannya.

## Keputusan pemilik

1. **Foto besar:** render editorial baru dari botol produk demo, bisa diganti foto asli nanti.
2. **Kartu produk:** polos ala NB plus wishlist. Isinya foto, nama, harga, satu tag hitam (Terlaris/Baru/Habis dari data), dan ikon wishlist.
   - Rating dan jumlah terjual hanya tampil di halaman detail.
   - Tombol tambah cepat dihapus.
3. **Basis:** logika versi marketplace dipakai lagi (filter lipat, chip filter, harga cepat, label dari data, harga decant di banner, bilah beli di HP); hanya tampilannya yang diganti.

## Batasan

- **Yang diikuti hanya pola tata letak dan gaya visual.** Identitas New Balance tidak dipakai: logo "N", nama, foto, font, dan teks kampanye mereka (misalnya "Perfect Pair Savings", "Shop now"). Logo kita tetap wordmark TOKO PARFUM.
- **Tidak ada promo palsu.**
  - NB menampilkan "GRATIS PENGIRIMAN", "Buy 2 Get 20% off", dan menu "Sale". Kita tidak punya promo itu, jadi tidak ditiru.
  - Bilah pengumuman diisi fakta layanan.
  - Tag dan harga diambil dari database.
- **Tidak ada emoji di tombol** dan **tidak ada gradien ungu** (aturan CLAUDE.md).

## Gaya NB yang diamati

- **Umum:** latar putih polos tanpa kartu, bayangan, atau garis tepi berlebih, jadi foto yang menonjol. Warnanya hitam, putih, dan abu, dengan merah sebagai aksen (menu promo, tombol beli, titik carousel aktif). Sudut tegas, judul bagian huruf kapital tebal, dan teks isi kecil.
- **Header:**
  - bilah utilitas abu-abu tipis (kontak, lacak pesanan);
  - header putih: logo di kiri, menu kategori dengan **mega menu** saat di-hover (kolom-kolom link), kolom cari bergaris, "Masuk | Daftar", dan tas belanja;
  - bilah pengumuman satu baris di bawahnya.
- **Beranda:**
  - banner selebar layar yang berganti otomatis (judul besar di kiri, subjudul, link bergaris bawah);
  - judul kapital rata tengah dengan baris ubin foto berlabel tebal;
  - ubin kategori besar 2 kolom (judul kapital, tagline, "BELANJA >");
  - footer gelap.
- **Daftar produk:**
  - breadcrumb kapital kecil dan sidebar "SARING" dengan bagian lipat berisi checkbox;
  - judul kapital dengan jumlah hasil berwarna abu, plus dropdown urutan di kanan;
  - grid 4 kolom tanpa garis tepi, dengan tag hitam kotak di kartu dan banner promo yang disisipkan di tengah grid.
- **Detail produk:**
  - 2 kolom: galeri besar | info;
  - info: brand kecil merah kapital, nama besar, harga tebal, pilihan ukuran berupa kotak, jumlah;
  - tombol merah penuh "BELI SEKARANG" dan tombol kedua "TAMBAH KE KERANJANG";
  - deskripsi di bawah dengan judul kapital.

## Design system v3

| Token | Nilai | Dipakai untuk |
|---|---|---|
| Latar | `#FFFFFF` | semua halaman, tanpa latar abu-abu |
| Teks | `#111111` / `#6B6B6B` | utama / sekunder |
| Garis | `#E5E5E5` | pemisah bagian, input |
| Abu muda | `#F5F5F5` | bilah utilitas, latar foto, kotak ringkasan |
| Footer | `#1A1A1A` | footer gelap dengan teks putih |
| Aksen (`primary`) | `#DC2626` (hover `#B91C1C`) | tombol beli, harga promo, titik aktif, brand di detail |

- **Sudut 0:** semua tombol, input, kartu, dan gambar bersudut tegas. Hanya titik dan lencana angka yang bulat.
- **Font:** Inter.
  - Judul bagian dan tombol: KAPITAL tebal dengan sedikit jarak huruf.
  - Judul banner: besar, huruf biasa.
  - Teks isi: kalimat biasa (tidak kapital semua) supaya mudah dibaca.
- **Tombol:**
  - utama: merah penuh, persegi, tinggi 48px, teks kapital;
  - kedua: bergaris hitam;
  - link: teks bergaris bawah.
- **Tanpa bayangan.** Pemisah memakai garis tipis atau ruang kosong.

## Halaman

### Header (semua halaman)
- **Bilah utilitas abu-abu** di atas: "Lacak pesanan" (ke halaman Pesanan), "Bantuan" (membuka chat), dan "Bayar VA bank & QRIS".
- **Header putih:** logo, lalu menu BARU · PRIA · WANITA · UNISEX · DECANT · BRAND, kolom cari bergaris, "Masuk | Daftar" atau nama akun, wishlist, dan tas belanja dengan angka.
- **Mega menu** saat hover di menu kategori, berisi kolom:
  - Keluarga aroma: 5 link yang tersaring ke kategori itu;
  - Ukuran: Decant, 30ml, 50ml, 100ml (sesuai data);
  - Brand;
  - satu ubin foto render di kanan.
- **Bilah pengumuman** satu baris: "Original · Bayar VA bank & QRIS · Kirim ke seluruh Indonesia".
- **HP:** hamburger membuka menu berlapis (kategori → isi mega menu), lalu logo, cari, dan tas. Perbaikan menu HP sebelumnya (teleport ke body) tetap dipakai.

### Beranda
1. **Banner selebar layar** (tanpa sudut, tanpa jarak tepi), 3 slide dari render baru:
   - isi teks dari data, sama seperti sekarang: koleksi oriental, koleksi fresh, dan "Coba dulu lewat decant, mulai Rp49.000";
   - tampilan: judul besar, subjudul, dan link bergaris bawah "Belanja sekarang";
   - titik penanda merah, geser otomatis, bisa di-swipe;
   - di HP gambarnya potret dan teks berada di bawah gambar.
2. **BELANJA BERDASARKAN AROMA:** judul kapital di tengah, lalu 5 ubin foto persegi besar (Woody, Floral, Fresh, Oriental, Gourmand) dengan label tebal di bawahnya. Di HP bisa digeser.
3. **Ubin kategori 2 kolom:** PRIA, WANITA, UNISEX, DECANT. Masing-masing berisi foto besar, judul kapital, tagline singkat yang jujur, dan "BELANJA >".
4. **BARU MASUK:** grid 4 kolom kartu polos, dengan link "Lihat semua".
5. **PALING LARIS:** hanya muncul kalau sudah ada penjualan.

### Kartu produk
- Foto 1:1 tanpa garis tepi.
- Tag hitam kotak kapital di kiri atas, hanya satu: TERLARIS, BARU, atau HABIS (aturan dari data yang sudah ada).
- Ikon wishlist di kanan atas.
- Nama (maksimal 2 baris).
- Harga tebal, dengan kata kecil "mulai" kalau ada beberapa ukuran berbeda harga.
- Yang dihapus dari kartu: rating, jumlah terjual, "Ada decant", "Stok terbatas", dan tambah cepat.
  - Rating, terjual, dan stok tetap ada di halaman detail.
  - Method `add()` di `ProductActions` beserta test-nya ikut dihapus karena tidak dipakai lagi.

### Katalog, kategori, dan brand
- **Kepala halaman:** breadcrumb kapital kecil, lalu judul kapital dengan "(n hasil)" berwarna abu.
- **Sidebar "SARING"** (polos, tanpa kotak): bagian lipat Kategori, Keluarga aroma, Brand, Harga (input "Rp" + pilihan cepat), Ukuran, Konsentrasi, dan Ketersediaan. Logikanya sama dengan sekarang.
- **Dropdown "Urutkan"** di kanan atas, menggantikan tab urutan.
- **Chip filter aktif:** persegi bergaris hitam dengan tanda silang, ditambah "Hapus semua".
- **Grid:** 4 kolom di desktop, 3 di tablet, 2 di HP.
- **Ubin promo decant** disisipkan setelah baris kedua, hanya kalau ada decant yang berstok.
- **HP:** tombol "SARING" dan "URUTKAN" menempel di atas.

### Detail produk
- **Kolom kiri:** galeri besar dengan thumbnail di bawah (zoom dan lightbox tetap ada).
- **Kolom kanan:**
  - brand merah kecil kapital, nama besar, lalu konsentrasi · aroma · kategori;
  - rating dan jumlah terjual (kalau ada);
  - harga tebal besar;
  - pilihan ukuran berupa kotak persegi, stok dan berat, jumlah;
  - tombol BELI SEKARANG (merah penuh), TAMBAH KE KERANJANG (bergaris), dan link wishlist;
  - 3 baris info layanan.
- **Di bawah:** DETAIL PRODUK (deskripsi dan tabel notes), ULASAN, dan PRODUK TERKAIT.
- **HP:** bilah beli menempel di bawah layar tetap dipakai.

### Keranjang, checkout, pesanan, akun, login
- **Tampilan:** putih polos. Setiap bagian dipisah garis tipis dan berjudul kapital. Tombol persegi. Ringkasan belanja di kotak abu muda.
- **Status pesanan:** badge persegi berwarna netral. Langkah progres pesanan tetap ada dengan gaya baru.
- **Isi dan alur** tidak berubah.

### Admin
Tidak berubah.

## Aset gambar baru

Script `render-banners.mjs` diperluas. Gayanya editorial: latar polos lembut, cahaya studio, botol besar, tanpa podium berwarna mencolok. Semua file WebP disimpan di `public/images`.
- **Banner:** 3 slide, desktop 2400×1000 dan HP 1080×1350 (potret).
- **Ubin aroma:** 5 gambar, 1000×1000. Setiap gambar berisi satu botol beraroma itu di latar warna lembut yang senada.
- **Ubin kategori:** 4 gambar, 1600×1000 (Pria, Wanita, Unisex, Decant), berisi kelompok botol dari kategori itu.
- **Mega menu:** memakai potongan dari ubin kategori, jadi tidak ada file tambahan.

## Yang dipakai ulang dari versi marketplace

Bagian ini dipakai apa adanya beserta test-nya:
- `CatalogQuery` (terjual dan rating);
- `BestsellerIds`, serta aturan label Terlaris/Baru/Habis;
- pilihan harga cepat, `removeFilter`, dan chip filter aktif;
- data banner di `HomeController`;
- komponen carousel (logika geser, swipe, dan auto-geser).

## Yang tidak berubah

Logika order, stok, ongkir, kelurahan, pembayaran Komerce, autentikasi, admin, dan data produk.

## Test

- **Diperbarui:**
  - test kartu yang memeriksa label decant, stok terbatas, dan rating di kartu (sekarang di detail);
  - tab urutan (sekarang dropdown);
  - tambah cepat (dihapus).
- **Baru:**
  - mega menu berisi link aroma yang tersaring ke kategori;
  - kartu hanya menampilkan satu tag;
  - ubin promo decant hanya muncul kalau ada decant berstok;
  - rating dan terjual tampil di detail kalau datanya ada.

## Urutan kerja (commit kecil)

1. **Token:** putih, hitam, merah, sudut 0, kapital. Ditambah komponen dasar, header dengan mega menu dan bilah pengumuman, serta footer gelap.
2. **Beranda:** render aset baru (banner, ubin aroma, ubin kategori), lalu beranda baru.
3. **Kartu produk polos:** hapus tambah cepat dan perbarui test.
4. **Katalog:** sidebar SARING, dropdown urutan, chip, grid 4 kolom, ubin promo di grid.
5. **Detail produk 2 kolom.**
6. **Keranjang, checkout, pesanan, akun, dan login.**
7. **Dokumentasi:** CLAUDE.md bagian UI diganti ke gaya ini, README aset diperbarui.
8. **Verifikasi:**
   - `php artisan test` dengan cache view terpisah, Pint, build;
   - cek 375, 768, dan 1440px serta overflow;
   - uji alur belanja di sandbox sampai Dibayar.
