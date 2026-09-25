-- =========================================================
--  MIGRASI: pemotongan & pengembalian stok pesanan
--  Untuk database yang sudah dibuat dengan versi database.sql sebelumnya.
--  Jalankan sekali SEBELUM mengunggah api/pesanan.php versi baru:
--  mysql -u root -p < database/migrasi-stok-pesanan.sql
--
--  stok_dipotong  : 1 = stok produk sudah dipotong untuk pesanan ini dan
--                   belum dikembalikan. Diset 1 saat checkout dan 0 saat
--                   pembatalan, masing-masing dalam transaksi yang sama
--                   dengan perubahan stok. Pesanan lama bernilai 0 karena
--                   dulu checkout tidak memotong stok, sehingga
--                   membatalkannya tidak menambah stok yang tidak pernah
--                   dipotong.
--  checkout_token : token acak dari browser per percobaan checkout. UNIQUE,
--                   sehingga request ulang (double-click, timeout lalu retry)
--                   mengembalikan pesanan yang sama, bukan membuat pesanan
--                   dan memotong stok dua kali. NULL untuk pesanan lama.
-- =========================================================
USE holando_sejahtera;

ALTER TABLE pesanan
    ADD COLUMN stok_dipotong  TINYINT(1) NOT NULL DEFAULT 0 AFTER status,
    ADD COLUMN checkout_token CHAR(32)   NULL AFTER stok_dipotong,
    ADD UNIQUE KEY uq_pesanan_checkout_token (checkout_token);
