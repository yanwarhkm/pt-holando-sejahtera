-- =========================================================
--  MIGRASI: kategori produk → jenis/varietas bibit kentang
--  Untuk database yang sudah dibuat dengan versi database.sql sebelumnya.
--  BACKUP DULU:  mysqldump -u root -p --databases holando_sejahtera > backup.sql
--  Jalankan sekali SEBELUM mengunggah api/produk.php versi baru:
--  mysql -u root -p < database/migrasi-kategori-bibit.sql
--
--  - Kategori lama (ENUM Sayuran/Buah/Umbi) tidak relevan untuk bibit dan dihapus.
--  - Kategori baru = jenis/varietas bibit, disimpan di master kategori_bibit
--    sehingga admin dapat menambah/mengubah/menonaktifkan tanpa ubah schema.
--  - Generasi & ukuran BUKAN kategori (belum ada kolomnya; tidak dibuat di sini).
--  - Tidak ada produk, pesanan, ulasan, atau log yang dihapus.
-- =========================================================
USE holando_sejahtera;

CREATE TABLE IF NOT EXISTS kategori_bibit (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(50) NOT NULL,
    aktif      TINYINT(1)  NOT NULL DEFAULT 1,
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_kategori_bibit_nama (nama)
) ENGINE=InnoDB;

ALTER TABLE produk
    ADD COLUMN kategori_id INT UNSIGNED NULL AFTER nama,
    ADD KEY idx_produk_kategori (kategori_id),
    ADD CONSTRAINT fk_produk_kategori FOREIGN KEY (kategori_id) REFERENCES kategori_bibit(id)
        ON UPDATE CASCADE;

-- Jenis bibit diambil dari data yang ADA: saat migrasi dibuat, semua produk
-- menyebut varietas "Granola" di namanya (mis. "SM Benih Generasi 2, Granola L, Ukuran Sedang").
-- Kategori hanya dibuat jika memang ada produknya.
INSERT INTO kategori_bibit (nama)
SELECT 'Granola' FROM DUAL
WHERE EXISTS (SELECT 1 FROM produk WHERE nama LIKE '%Granola%')
  AND NOT EXISTS (SELECT 1 FROM kategori_bibit WHERE nama = 'Granola');

UPDATE produk p
JOIN kategori_bibit k ON k.nama = 'Granola'
SET p.kategori_id = k.id
WHERE p.nama LIKE '%Granola%' AND p.kategori_id IS NULL;

ALTER TABLE produk DROP COLUMN kategori;

-- Produk yang jenisnya tidak dapat ditentukan dari nama (kategori_id NULL).
-- Tetapkan jenisnya lewat Dashboard Admin → Bibit → Edit.
SELECT id, nama AS 'Produk tanpa jenis bibit (perlu diisi admin)' FROM produk WHERE kategori_id IS NULL;
