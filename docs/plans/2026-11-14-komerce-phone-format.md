# Perbaikan format nomor HP Komerce

## Penyebab

Komerce mengembalikan HTTP 422 `customer.phone must be numeric`. Dua pesanan terbaru memiliki nomor snapshot berawalan `+`, format yang sah menurut validasi form alamat project. PaymentService sebelumnya meneruskan nomor tersebut tanpa penyesuaian.

## Perubahan

Hapus tanda `+` di awal nomor pada payload Komerce saja. Nomor tetap string agar nol awal terjaga. Snapshot pesanan tidak diubah, sehingga percobaan ulang pesanan lama juga memakai format yang diterima gateway.

## Verifikasi

- Dua test regresi (VA BCA dan QRIS) gagal sebelum perbaikan: payload masih mengandung `+`.
- Setelah perbaikan, 20 test PaymentService lulus (89 assertions), termasuk payload dan snapshot tetap utuh.
- Pint untuk kedua file terkait lulus.
- Uji manual terpisah dari test otomatis: mode local dan URL sandbox diperiksa sebelum request asli. Pesanan `INV-20261114-EW94V` berhasil dibuatkan VA BCA Rp94.000. API cek status mengembalikan pending; pesanan tetap pending_payment.
- Tidak dilakukan pembayaran nyata atau simulasi pelunasan pada percobaan ini. QRIS diverifikasi dengan Http::fake, bukan request sandbox asli pada sesi ini.
- `php artisan test --compact`: 361 test lulus, 2201 assertions, durasi 243,64 detik.
