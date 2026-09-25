-- =========================================================
--  MIGRASI: rating ulasan pelanggan
--  Untuk database yang sudah dibuat dengan versi database.sql sebelumnya.
--  Jalankan sekali SEBELUM mengunggah api/testimoni.php versi baru:
--  mysql -u root -p < database/migrasi-rating-ulasan.sql
--
--  Sebelumnya form ulasan tidak mengirim rating, sehingga setiap ulasan dari
--  form publik tersimpan dengan rating 5.0 dari DEFAULT kolom, bukan pilihan
--  pelanggan. Kolom dibuat nullable (NULL = belum ada rating) dan nilai 5.0
--  palsu tersebut dikosongkan. Tidak ada baris yang dihapus.
-- =========================================================
USE holando_sejahtera;

ALTER TABLE testimoni
    MODIFY rating DECIMAL(2,1) NULL DEFAULT NULL;

-- Rating eksplisit hanya berasal dari data awal database.sql (dikecualikan di bawah).
-- Tidak ada endpoint yang pernah menyimpan rating, jadi 5.0 lainnya berasal dari DEFAULT.
UPDATE testimoni
SET rating = NULL
WHERE rating = 5.0
  AND (nama, pesan) NOT IN (
      ('Ibu Sari, Pembeli di Bandung', 'Produknya selalu segar dan bersih. Harga juga sangat masuk akal. Langganan terus!'),
      ('Pak Budi, Restoran Sehat', 'Pengiriman cepat, kemasan rapi. Sayurannya masih segar saat sampai. Terima kasih!')
  );
