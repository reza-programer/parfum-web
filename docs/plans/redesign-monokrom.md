# Redesign Monokrom Premium — Rencana

> Arah tampilan ini sudah **diganti** oleh `redesign-marketplace.md` (12 Nov 2026). Fitur dan logikanya tetap dipakai; warna dan tata letaknya tidak lagi.

Status: **selesai dikerjakan 12 Nov 2026**, belum di-merge. Branch: `feat/redesign-monokrom` (dibuat di atas `feat/pembayaran-komerce`, jadi nanti di-merge berurutan).

## Hasil verifikasi

- **Otomatis:** `php artisan test` lulus 308 test (1012 assertion), termasuk test baru untuk konsentrasi, filter stok, urutan harga tertinggi, tambah cepat, wishlist di kartu, Beli Sekarang, produk terkait, galeri, dan toast. `pint --test` dan `npm run build` lulus.
  - Satu run sebelumnya sempat gagal di 1 test (`AddressesTest`, dropdown wilayah). Run itu berjalan bersamaan dengan pemuatan banyak halaman di server dev, yang memakai folder cache view Blade yang sama.
  - Test yang sama lulus saat dijalankan sendirian, dan run penuh berikutnya tanpa aktivitas browser lulus semua.
  - Saran: jangan menjalankan `php artisan test` sambil membuka halaman di server dev.
- **Overflow horizontal:** dicek di 375, 768, dan 1440px untuk beranda, katalog, kategori, brand, detail produk, keranjang, checkout, wishlist, riwayat pesanan, detail pesanan, alamat, dan profil. Hasilnya 0px di semua kombinasi.
- **Uji manual di browser:**
  - drawer filter (panel dari bawah di ponsel);
  - tambah cepat sebagai customer (toast dan angka keranjang bertambah);
  - checkout dengan ongkir RajaOngkir asli dan VA BNI sandbox Komerce, lalu simulasi bayar sampai Dibayar lewat polling.
- **Admin:** tema termuat. Sudut 0, header tabel `#FAFAFA` 12px, baris sekitar 56px, kolom Total rata kanan, badge kapital.
- **Belum dicek visual:** halaman login, daftar, dan lupa password sebagai tamu (sesi uji sedang login). Test autentikasi lulus.

## Penyimpangan kecil dari rencana

- **`ink-3`** memakai `#707070` (sama dengan teks sekunder), bukan `#BDBDBD`. Banyak teks kecil di view lama memakai `ink-3`, dan `#BDBDBD` tidak terbaca untuk teks. Warna disabled dan placeholder dipindah ke token baru `ink-faint` (`#BDBDBD`).
- **Produk terkait** sudah ada sebelumnya (hanya dari aroma yang sama). Sekarang aroma yang sama didahulukan, lalu kategori yang sama.

Sumber permintaan: brief "Redesign e-commerce parfum — premium monochrome" dari pemilik (12 Nov 2026). Rencana ini menerjemahkan brief itu ke kondisi project yang sebenarnya.

## Keputusan pemilik (12 Nov 2026)

1. **Foto produk:** ke-12 produk belum punya foto. Ilustrasi botol tetap dipakai dulu, tapi diubah jadi monokrom. Galeri, thumbnail, dan zoom dibangun lengkap dan otomatis aktif begitu foto diunggah lewat admin (fitur upload sudah ada). Tidak ada foto stok atau foto palsu.
2. **Konsentrasi parfum:** tambah kolom `products.concentration` (nullable) dengan pilihan Extrait, Parfum, EDP, EDT, EDC. Field-nya ada di admin, filternya di katalog. Produk lama dibiarkan kosong sampai diisi admin. Filter hanya muncul kalau sudah ada produk yang punya data konsentrasi.
3. **Branch:** dibuat di atas `feat/pembayaran-komerce`, sehingga tampilan checkout dan halaman bayar Komerce ikut diredesign.
4. **Admin:** ikut diredesign dengan tema Filament monokrom ringan (warna, font, kerapian tabel). Struktur halaman admin tidak berubah.

## Hasil audit singkat (kondisi sekarang)

- **Gaya sekarang:** hangat ("kertas"), aksen cokelat `ember`, font Instrument Sans dan Instrument Serif, kartu `rounded-2xl` dengan border di banyak tempat, titik warna per keluarga aroma.
- **Sudah ada dan dipertahankan:**
  - navbar sticky dengan pencarian, wishlist, keranjang, dan menu akun;
  - filter katalog: pencarian, kategori, brand, aroma, ukuran, rentang harga;
  - urutan Terbaru, Termurah, Terlaris;
  - rating dan ulasan di detail produk;
  - keranjang, wishlist, checkout, alamat, kurir, pembayaran Komerce, riwayat dan detail pesanan, ulasan.
- **Belum ada:**
  - urutan "Harga tertinggi";
  - filter stok tersedia dan filter konsentrasi;
  - wishlist dan tambah cepat di kartu produk;
  - tombol "Beli Sekarang";
  - produk terkait;
  - toast notifikasi;
  - skeleton loading;
  - zoom gambar.
- **Kontras warna:** teks sekunder `#777777` dari brief hanya punya kontras 4,2:1 di atas `#F8F8F6`, di bawah standar WCAG AA (4,5:1) untuk teks kecil. Usulan: teks sekunder memakai `#707070` (4,7:1). Warnanya hampir sama, tapi tetap terbaca. Kalau tetap ingin persis `#777777`, beri tahu saya.

## Design system

### Warna (token Tailwind)

Nama token lama dipertahankan supaya semua view berubah serentak dan konsisten. Hanya nilainya yang diganti.

| Token | Nilai | Dipakai untuk |
|---|---|---|
| `paper` | `#F8F8F6` | latar halaman |
| `paper.card` | `#FFFFFF` | permukaan (surface): panel, dropdown, drawer |
| `paper.raised` | `#F1F1EF` | latar foto/ilustrasi produk, skeleton |
| `ink` | `#171717` | teks utama |
| `ink-2` | `#707070` | teks sekunder (lihat catatan kontras) |
| `ink-3` | `#707070` | teks tersier (lihat "Penyimpangan kecil") |
| `ink-faint` | `#BDBDBD` | disabled dan placeholder |
| `primary` (`ember` diganti) | `#111111` | tombol utama, harga, badge aktif |
| `line` | `#E8E8E8` | border tipis |

- **Titik warna aroma** diganti label teks kecil kapital ("ORIENTAL"). Tidak ada warna dekoratif.
- **Status pesanan** memakai badge abu-abu dan hitam. Hanya Dibatalkan yang memakai abu-abu teks, tanpa warna mencolok.
- **Pesan error** tetap memakai merah tua (`#B42318`), karena fungsinya peringatan, bukan dekorasi.

### Tipografi

- **Heading:** Cormorant Garamond (500/600). Dipilih karena lebih tipis dan lebih "parfum mewah" dibanding Playfair yang sudah sangat umum.
- **Body, navigasi, tombol:** Inter (400/500/600).
- **Sumber font:** bunny.net, mengikuti cara layout sekarang memuat font.

| Elemen | Desktop | Mobile |
|---|---:|---:|
| Hero heading | 52px | 36px |
| Page heading | 40px | 30px |
| Section heading | 24px | 22px |
| Nama produk | 15px | 14px |
| Body | 14px | 14px |
| Navigasi | 13px medium | 14px |
| Label | 12px | 12px |
| Tombol | 13px medium, huruf kapital, spasi huruf sedikit | sama |
| Caption | 11px | 11px |

- Ukuran ditulis sebagai token Tailwind (`text-hero`, `text-page`, `text-section`, dan seterusnya) dengan `clamp()` supaya menyesuaikan layar.
- Line-height heading 1,1–1,2, body 1,6.

### Bentuk dan komponen dasar

- **Sudut:** tegas, radius 0 untuk tombol, input, foto, dan drawer. Gaya ini umum di toko parfum dan fashion mewah, sekaligus membedakan dari template e-commerce generik yang serba bulat.
- **Tanpa bayangan.** Pemisah memakai whitespace atau satu garis `#E8E8E8`.
- **Tombol:**
  - primary: hitam dengan teks putih;
  - secondary: putih dengan garis hitam tipis;
  - link: teks bergaris bawah.
  - Tinggi 44px (area sentuh minimal), atau 40px untuk versi kecil di desktop.
- **Input:** tinggi 44px, garis `#E8E8E8`, garis berubah hitam saat fokus.
- **Komponen Blade baru:** `x-ui.button`, `x-ui.badge`, `x-ui.toast` (Alpine store global, dipicu `$this->dispatch('toast', ...)` dari Livewire), `x-ui.skeleton`, `x-ui.drawer`, dan `x-ui.table` (tabel desktop yang berubah jadi daftar di mobile).
- **Mikro-interaksi:**
  - foto produk sedikit membesar (scale 1,03) saat hover;
  - ikon wishlist berubah terisi dengan transisi singkat;
  - transisi 150–250 ms;
  - skeleton saat grid katalog atau ongkir memuat;
  - tidak ada animasi berulang atau dekoratif.

## Halaman yang diaudit dan diubah

### 1. Layout, navbar, footer

- **Container:** maksimal 1280px dengan padding 24px di desktop dan 16px di mobile.
- **Navbar:**
  - sticky dengan tinggi 72px dan latar putih;
  - logo di kiri, lalu menu Pria, Wanita, Unisex, Brand;
  - pencarian ringkas, Wishlist, Keranjang (dengan angka), dan dropdown profil.
  - Di mobile: hamburger, logo, keranjang. Pencarian dan menu ada di drawer.
- **Footer** dibuat lebih tenang: tiga kolom teks dan garis tipis.
- **Widget chat** diselaraskan (hitam-putih, sudut tegas). Fungsinya tidak berubah.

### 2. Katalog, kategori, dan brand (komponen `Catalog` yang sama)

- **Tata letak desktop:** sidebar filter 220px dan grid 3 kolom. Grid jadi 4 kolom di layar ≥1280px, dengan gap 24px dan foto 1:1. Judul dan jumlah produk ada di atas, urutan di kanan atas.
- **Filter:**
  - pencarian, kategori aroma, brand, rentang harga, ukuran (sudah ada);
  - **baru:** konsentrasi dan "Hanya yang tersedia".
- **Urutan:** Terbaru, Harga terendah, Harga tertinggi (**baru**), Terlaris. Label "Termurah" diganti "Harga terendah" sesuai brief; nilai di URL tetap.
- **Kartu produk:**
  - foto atau ilustrasi, brand, nama, harga "mulai Rp", dan label aroma;
  - **wishlist** (ikon hati di pojok foto);
  - **tambah cepat:** pilihan ukuran muncul saat hover di desktop, atau lewat tombol "+" yang membuka sheet ukuran di mobile. Satu klik ukuran langsung masuk keranjang. Ini menghindari menebak ukuran untuk customer.
  - Tamu yang menekan wishlist atau tambah cepat diarahkan ke login, sama seperti perilaku sekarang.
  - Stok habis tetap tampil dengan label "Habis" (PRD).
- **Tablet:** 2–3 kolom, filter di drawer samping.
- **Mobile:** 2 kolom, filter di panel dari bawah (sesuai PRD wireframe 1).
- **Skeleton:** grid diganti skeleton saat filter berubah, lalu pagination ringkas.

### 3. Detail produk

- **Dua kolom.**
- **Kiri:** galeri dengan foto utama, thumbnail, dan zoom (klik untuk membuka lightbox, desktop juga bisa zoom saat hover). Aktif kalau produk punya foto; dengan ilustrasi hanya tampil satu gambar tanpa zoom.
- **Kanan:**
  - brand, nama, rating dengan jumlah ulasan, harga;
  - konsentrasi (kalau ada), deskripsi;
  - Top, Middle, Base Notes;
  - pilihan ukuran, jumlah, status stok;
  - **Tambah ke Keranjang** dan **Beli Sekarang** (**baru**: masuk keranjang lalu langsung ke checkout).
- **Di bawah:** ulasan (sudah ada), lalu **Produk terkait** (**baru**: 4 produk dari kategori atau keluarga aroma yang sama, produk ini tidak ikut).

### 4. Keranjang dan wishlist

- **Keranjang:** daftar item bergaris tipis, kontrol jumlah 44px, tombol hapus berupa teks, ringkasan di kanan (sticky di desktop, menempel di bawah layar di mobile).
- **Wishlist:** grid kartu yang sama dengan katalog.
- **Notifikasi:** "Ditambahkan ke keranjang", "Dihapus dari wishlist", dan sejenisnya muncul sebagai toast.

### 5. Checkout dan pesanan

- **Checkout:** alur tetap (alamat, kurir, pembayaran, barang, ringkasan) dengan tampilan baru. Pilihan bank VA jadi daftar bergaris, bukan kotak-kotak. Skeleton saat ongkir dimuat. Tombol bayar 48px, nyaman dijangkau satu tangan.
- **Riwayat pesanan:** tabel di desktop (kode, tanggal, jumlah barang, total rata kanan, status badge), daftar di mobile. Filter status tetap ada.
- **Detail pesanan dan halaman bayar** (VA, QRIS, hitung mundur, ganti metode): logika tidak berubah, hanya tampilan.

### 6. Akun dan autentikasi

- **Halaman:** alamat (beserta form alamat), profil, ubah password, hapus akun, login, daftar, lupa password, reset password, konfirmasi password.
- **Tampilan:** satu kolom sempit (maksimal 440px) untuk form autentikasi, label 12px, input 44px.

### 7. Beranda dan daftar brand

- **Beranda:** hero dengan heading 52px, teks pendek, dua tombol, lalu keluarga aroma sebagai baris teks, kategori, dan produk terbaru. Konten dan tautan dipertahankan, hanya ditata ulang supaya lebih lapang.
- **Daftar brand:** daftar huruf A–Z yang rapi.

### 8. Panel admin (Filament)

- **Tema sendiri** dibuat lewat `php artisan make:filament-theme admin` dan didaftarkan dengan `viteTheme`. Isinya:
  - warna primary `#111111` dan gray netral;
  - font Inter;
  - header tabel 12px medium dengan latar `#FAFAFA`;
  - baris sekitar 56px, garis `#E8E8E8`;
  - badge sederhana.
- **Kolom angka dan harga** di resource Pesanan, Produk, Voucher, dan Customer diberi `alignEnd()`. Badge status memakai warna netral.
- **Halaman Inbox Chat** diselaraskan.

## Perubahan perilaku (kecil, semuanya dengan test)

| Perubahan | Alasan teknis |
|---|---|
| Kolom `products.concentration` (nullable) + field admin + filter | Diminta brief, disetujui pemilik |
| Urutan `termahal` di `CatalogQuery::SORTS` | Diminta brief |
| Filter `tersedia` (punya varian dengan stok > 0) | Diminta brief |
| Tambah cepat dari kartu (pilih ukuran) dan wishlist di kartu | Diminta brief. Memakai `CartService` dan `WishlistService` yang sudah ada |
| Tombol Beli Sekarang | Diminta brief. Memakai `CartService`, lalu redirect ke checkout |
| Produk terkait | Diminta brief. Query baca saja |
| Toast notifikasi | Diminta brief. Hanya event UI |

**Tidak diubah:** logika order, stok, voucher, ongkir, pembayaran Komerce, autentikasi, dan data produk yang ada. Email transaksi juga tidak diubah di tahap ini.

## Urutan kerja (satu commit per langkah, test dulu untuk perubahan perilaku)

1. Token warna, font, komponen dasar (`x-ui.*`), toast, layout, navbar, footer.
2. Migration konsentrasi, field admin, filter konsentrasi dan stok, urutan termahal (TDD).
3. Kartu produk baru dengan wishlist dan tambah cepat (TDD), lalu halaman katalog, kategori, dan brand dengan drawer filter dan skeleton.
4. Detail produk dengan galeri dan zoom, Beli Sekarang, produk terkait (TDD).
5. Keranjang dan wishlist.
6. Checkout, riwayat pesanan (tabel atau daftar), detail pesanan dan halaman bayar.
7. Akun, autentikasi, beranda, daftar brand, widget chat.
8. Tema Filament dan perapian kolom tabel admin.
9. **Verifikasi:**
   - `php artisan test`, `./vendor/bin/pint --test`, `npm run build`;
   - screenshot setiap halaman di lebar 1440, 768, dan 375px;
   - cek tidak ada horizontal overflow di setiap halaman (`scrollWidth === clientWidth`) pada 375px;
   - uji manual alur belanja sampai Dibayar di sandbox Komerce.

## Risiko

- **Banyak test memeriksa teks halaman** (`assertSee`). Teks UI dipertahankan sebisa mungkin. Kalau ada label yang berubah sesuai brief (misalnya "Termurah" jadi "Harga terendah"), test-nya ikut diperbarui di commit yang sama.
- **Tampilan tanpa foto asli** tetap terasa kurang "retail mewah" sampai foto diunggah. Ilustrasi monokrom adalah solusi sementara, bukan pengganti foto.
- **Tema Filament** butuh build Vite terpisah (`resources/css/filament/admin/theme.css`). Di shared hosting, aset ini ikut di-build sebelum upload, sama seperti aset customer.
