-- =========================================================
--  MIGRASI: akses panduan berbasis pesanan
--  Untuk database yang sudah dibuat dengan versi database.sql sebelumnya.
--  Jalankan sekali: mysql -u root -p < database/migrasi-akses-panduan.sql
-- =========================================================
USE holando_sejahtera;

-- Catatan lama (dari klik tombol, tanpa pesanan) tetap disimpan dengan pesanan_id NULL.
ALTER TABLE akses_panduan
    ADD COLUMN pesanan_id INT UNSIGNED NULL AFTER sumber,
    ADD CONSTRAINT fk_akses_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan(id)
        ON DELETE SET NULL ON UPDATE CASCADE;
