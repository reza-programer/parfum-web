# Tampilan Marketplace (Redesign v2) — Rencana

Status: **selesai 12 Nov 2026**, belum di-merge. Branch: `feat/tampilan-marketplace` (dari `main` terbaru).

## Hasil verifikasi

- **Test:** run penuh `php artisan test`: 347 lulus, 1 gagal (`navigation menu can be rendered`). Test itu lulus saat diulang sendiri; gagalnya karena cache view Blade dipakai bersama dev server yang sedang dibuka di browser. Run ulang dengan cache view terpisah terhenti di tengah (140 lulus, 0 gagal) saat sesi berakhir. Pint dan `npm run build` lulus.
- **Arah tampilan ini lalu diganti** (13 Nov 2026) oleh `redesign-nb.md` atas permintaan pemilik. Logika dan test di branch ini dipakai ulang di sana.
- **Overflow horizontal:** 0 px di semua halaman toko dan akun pada lebar 375 px. Pada 768 px dan 1440 px juga 0 px untuk halaman yang dicek (beranda, katalog, detail, checkout, pesanan, brand, wishlist).
- **Carousel:** geser otomatis jalan, panah dan titik memindahkan slide, dan posisi scroll tepat per slide. Teks banner tidak tertutup panah di tablet maupun desktop.
- **Katalog:** chip "Woody" menghapus filternya sendiri (URL ikut berubah), dan pilihan "Rp100rb – Rp250rb" mengisi batas harga sekaligus menandai chip-nya aktif.
- **Alur belanja di sandbox Komerce:**
  - detail produk → Keranjang → checkout (POS Reguler, VA BCA) → pesanan INV-20261112-9VJWH;
  - simulasi bayar di sandbox → status **Dibayar** lewat cek status API, nominal Rp278.900 cocok.
- **Data label saat ini:** label "Terlaris" dan jumlah terjual muncul di beberapa produk karena ada pesanan uji sandbox yang sudah Dibayar. Rating belum muncul karena belum ada ulasan.

Masukan pemilik (12 Nov 2026), dengan screenshot beranda dan katalog:
- Gambar hero di beranda jelek (ilustrasi botol abu-abu).
- Filter di kiri tidak rapi.
- Semua halaman harus dirapikan seperti e-commerce besar (Shopee/Tokopedia).

Rencana ini **menggantikan arah "monokrom premium"** dari `redesign-monokrom.md`. Fitur, logika, dan datanya tetap; yang berubah tampilannya.

## Keputusan pemilik

1. **Gaya:** ikut gaya marketplace penuh, yaitu tata letak, warna aksen cerah, dan label.
2. **Banner beranda:** carousel 3 banner dari render produk kita sendiri. Bergeser otomatis dan bisa digeser di HP. Teksnya memakai data asli, tanpa promo palsu.
3. **Filter katalog:** satu kotak putih "Filter" dengan bagian yang bisa dilipat, seperti Tokopedia.
4. **Kartu produk:** menampilkan rating dan jumlah terjual, hanya kalau datanya ada.

## Batasan yang tetap dipegang

- **Tidak meniru identitas Shopee/Tokopedia.** Logo, warna oranye/hijau khas mereka, maupun nama fitur mereka tidak dipakai. Yang diikuti hanya pola tata letak yang sudah umum di marketplace.
- **Label dan promo harus benar.** Tidak ada diskon, "gratis ongkir", atau jumlah terjual yang dikarang. Semua label dihitung dari database.
- **Tidak ada emoji di tombol, tidak ada gradien ungu** (aturan CLAUDE.md).

## Design system v2

| Token | Nilai | Dipakai untuk |
|---|---|---|
| Aksen (`primary`) | `#E11D48` (merah mawar) | tombol utama, harga, badge promo, tab aktif |
| Aksen gelap | `#BE123C` | hover/aktif |
| Aksen muda | `#FFF1F2` | latar badge, chip aktif |
| Latar halaman | `#F5F5F5` | khas marketplace: konten di kartu putih |
| Kartu | `#FFFFFF` | radius 8px, garis `#EAEAEA`, bayangan tipis hanya saat hover |
| Teks | `#212121` / `#6D6D6D` | utama / sekunder (kontras AA) |
| Hijau info | `#0E9F6E` | label stok tersedia dan status sukses |

- **Warna aksen:** merah mawar dipilih karena cerah dan cocok untuk parfum, tapi bukan warna khas Shopee maupun Tokopedia. Kalau kamu punya warna brand sendiri, tinggal ganti satu token.
- **Font:** Inter untuk semua teks. Logo tetap memakai wordmark serif sebagai identitas toko.
- **Sudut membulat** 8px untuk kartu, 6px untuk tombol dan input.
- **Ukuran teks:** judul halaman 20–24px (marketplace tidak memakai judul raksasa), nama produk 14px (maksimal 2 baris), harga 16px tebal.

## Halaman

### Header (semua halaman)
- **Bilah tipis di atas** berisi info yang benar: "Bayar VA bank & QRIS · Kirim ke seluruh Indonesia".
- **Header utama:** logo, **kolom pencarian besar di tengah** dengan tombol cari, keranjang dengan angka, wishlist, serta Masuk/Daftar atau menu akun.
- **Baris kategori:** Pria, Wanita, Unisex, Decant, Brand, dan keluarga aroma.
- **HP:** logo dan ikon di baris atas, kolom pencarian penuh di baris kedua. Menu hamburger yang sudah diperbaiki tetap dipakai.

### Beranda
1. **Carousel banner** (3 slide, rasio 3:1 di desktop dan 4:3 di HP): geser otomatis 5 detik, titik penanda, panah di desktop, dan swipe di HP. Gambar banner dibuat dari render botol produk kita dengan latar berwarna. Teks dan tombolnya ditulis di HTML, bukan di dalam gambar, supaya tajam dan bisa dibaca mesin pencari.
   - **Koleksi Oriental:** Malam Rempah, Dupa Keraton → katalog aroma oriental.
   - **Segar seharian:** koleksi Fresh dari Kala Scent → katalog aroma fresh.
   - **Coba dulu lewat decant, mulai Rp49.000** → katalog decant. Harga termurah diambil dari database, bukan ditulis tetap.
2. **Shortcut kategori:** ikon bulat berisi foto botol untuk Pria, Wanita, Unisex, Decant, dan 5 keluarga aroma.
3. **Produk:** bagian "Baru masuk", "Terlaris" (kalau sudah ada penjualan), dan "Pilihan per brand", masing-masing dengan link "Lihat semua". Grid 2 kolom di HP, 4 di tablet, 5 di desktop.

### Kartu produk
- **Tampilan dasar:** kartu putih, foto 1:1, nama 2 baris, harga merah tebal ("mulai" kalau ada beberapa ukuran), brand, dan aroma.
- **Label dari data asli:**
  - "Baru" (masuk ≤ 30 hari);
  - "Terlaris" (5 teratas yang punya penjualan);
  - "Ada decant";
  - "Stok terbatas" (total ≤ 3);
  - "Habis".
- **Rating dan terjual,** misalnya "★ 4,8 · 12 terjual", hanya tampil kalau ada. Saat ini belum ada ulasan, jadi baris ini akan kosong sampai ada pembeli.
- **Wishlist dan tambah cepat** tetap ada.

### Katalog, kategori, dan brand
- **Kotak Filter** (lebar 240px, menempel saat discroll):
  - judul "Filter" dan tombol "Reset";
  - setiap bagian bisa dibuka-tutup: Kategori, Keluarga aroma, Brand (5 pertama + "Lihat semua"), Harga (input berawalan "Rp" + pilihan cepat <Rp100rb, Rp100–250rb, >Rp250rb), Ukuran, Konsentrasi, Ketersediaan.
- **Bilah atas hasil:** jumlah produk, tombol urutan bergaya tab (Terbaru, Terlaris, Harga ▲/▼), dan chip filter aktif yang bisa dihapus satu per satu.
- **HP:** tombol Filter dan Urutkan menempel di atas, dengan drawer filter yang sama.

### Detail produk
- **Tata letak 3 kolom di desktop:** galeri | info (nama, rating, terjual, harga besar, pilihan ukuran, deskripsi, notes) | kotak beli yang menempel (jumlah, subtotal, Tambah ke keranjang, Beli sekarang, wishlist).
- **HP:** galeri penuh, info, lalu bilah beli yang menempel di bawah layar.
- **Bawah halaman:** info toko singkat, ulasan, dan produk terkait.

### Keranjang, checkout, pesanan, akun
- **Semua bagian dalam kartu putih** di atas latar abu-abu muda, dengan judul bagian yang jelas. Isi dan alurnya tidak berubah.
- **Status pesanan:** badge berwarna (menunggu bayar, dibayar, dikirim, selesai, batal) dengan arti yang jelas.

## Aset gambar baru

- **3 gambar banner** (2400×800 dan versi HP 1200×900, WebP) dibuat dengan generator render yang sama (`database/seeders/images/source`). Isinya kelompok botol produk asli di set studio berwarna sesuai tema. Disimpan di `public/images/banners`.
- **Ikon kategori** diambil dari crop render produk yang sudah ada, jadi tidak ada gambar stok dari luar.

## Yang tidak berubah

Logika order, stok, ongkir, kelurahan, pembayaran Komerce, autentikasi, admin, dan data produk.

Teks UI yang diperiksa test diusahakan tetap sama. Test yang bergantung pada struktur lama diperbarui di commit yang sama.

## Urutan kerja (commit kecil, test untuk perilaku baru)

1. **Token warna, radius, dan komponen dasar.** Ditambah header baru beserta bilah info dan kolom cari besar.
2. **Banner:**
   - membuat gambarnya;
   - komponen carousel (Alpine, swipe, auto-geser, aksesibel);
   - beranda baru dengan shortcut kategori dan bagian produk;
   - test: harga decant termurah dari database dan link banner.
3. **Kartu produk** dengan label dan rating/terjual dari data. Test: aturan label "Baru", "Terlaris", "Stok terbatas", dan rating/terjual hanya tampil kalau ada.
4. **Katalog:**
   - kotak filter yang bisa dilipat;
   - pilihan harga cepat;
   - tab urutan;
   - chip filter aktif.
   - Test: chip menghapus filter dan pilihan harga cepat.
5. **Detail produk 3 kolom** dan bilah beli di HP.
6. **Keranjang, checkout, pesanan, dan akun** dalam kartu.
7. **CLAUDE.md** bagian UI diperbarui ke arah marketplace.
8. **Verifikasi:**
   - `php artisan test`, Pint, build;
   - screenshot 375, 768, dan 1440px;
   - cek overflow;
   - uji alur belanja sampai Dibayar di sandbox.
