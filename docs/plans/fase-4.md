# Fase 4: Pesanan, Tracking, Email — Plan (tanpa Midtrans)

Atas permintaan pengguna, pembayaran Midtrans (fitur 8) ditunda. Bagian lain di fase ini tetap dikerjakan.

> **Update 12 Nov 2026:** Midtrans diganti Komerce Payment. Fitur 8 dikerjakan di [pembayaran-komerce.md](pembayaran-komerce.md). Bagian "Midtrans nanti" di bawah tidak berlaku lagi.

## Keputusan

1. **Siklus status ada di `OrderService`:** `cancel`, `expireOverdue`, `markPaid`, `markProcessing`, `ship`, dan `complete`. Setiap perubahan dibungkus transaction dengan row lock pada order, supaya stok tidak dikembalikan dua kali.
2. **`markPaid()` hanya dipanggil `PaymentService`** setelah Komerce menyatakan PAID dengan nominal yang cocok. Panel admin sengaja tidak punya aksi "tandai dibayar".
3. **Pembatalan mengembalikan stok dan kuota voucher.** Customer hanya bisa membatalkan pesanan yang belum dibayar. Admin bisa membatalkan pesanan Menunggu Bayar, Dibayar, atau Dikemas; refund dilakukan manual.
4. **`orders:expire` dijalankan scheduler tiap menit.** Queue diproses lewat `queue:work --stop-when-empty` di scheduler, cukup dengan satu cron `schedule:run`.
5. **Email invoice (order dibuat), pembayaran diterima, dan pesanan dikirim (dengan resi)** dikirim sebagai mailable `ShouldQueue` yang dipicu event `OrderPlaced`, `OrderPaid`, dan `OrderShipped`.
6. **Customer menekan "Pesanan Diterima"** untuk mengubah status Dikirim menjadi Selesai. Setelah itu tombol "Tulis ulasan" muncul per item (fitur 11 ikut dikerjakan di sini).
7. **Halaman pesanan yang masih Menunggu Bayar** menampilkan batas waktu bayar beserta nomor VA atau kode QR (lihat pembayaran-komerce.md).

## Midtrans nanti (tidak berlaku lagi)

~~Yang masih perlu dibuat: `PaymentService` (Snap token, sandbox), endpoint `POST /midtrans/notification`, aksi "Cek status" di admin, dan tombol Bayar yang membuka popup Snap.~~ Diganti Komerce Payment.
