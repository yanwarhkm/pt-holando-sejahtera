-- =========================================================
--  MIGRASI: Galeri "Dokumentasi Budidaya & Pembibitan"
--  Untuk database yang sudah dibuat dengan versi database.sql sebelumnya.
--  BACKUP DULU:  mysqldump -u root -p --databases holando_sejahtera > backup.sql
--  Jalankan sekali:  mysql -u root -p < database/migrasi-dokumentasi.sql
--
--  - Tabel baru `dokumentasi`; tidak ada tabel/data lain yang diubah.
--  - Data awal = 4 kartu galeri yang sebelumnya hardcode di index.html
--    (judul, keterangan, tanggal, dan berkas gambar persis seperti aslinya).
--    Gambar tetap memakai berkas lama di root project — tidak dipindah/dihapus.
--  - Seed hanya dijalankan bila tabel masih kosong (aman dijalankan ulang).
-- =========================================================
USE holando_sejahtera;

CREATE TABLE IF NOT EXISTS dokumentasi (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul      VARCHAR(100) NOT NULL,
    keterangan VARCHAR(100) NOT NULL,   -- lokasi / keterangan singkat
    tanggal    DATE         NOT NULL,   -- tanggal dokumentasi
    gambar     VARCHAR(255) NOT NULL,   -- path relatif berkas gambar (bukan binary)
    aktif      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_dokumentasi_tampil (aktif, tanggal)
) ENGINE=InnoDB;

INSERT INTO dokumentasi (judul, keterangan, tanggal, gambar)
SELECT * FROM (
    SELECT 'Kebun Kentang' AS judul, 'Desa Cihideung' AS keterangan, DATE('2026-09-15') AS tanggal, 'WhatsApp Image 2026-09-09 at 09.08.51 (1).jpeg' AS gambar
    UNION ALL SELECT 'Panen Kentang', 'Kebun Pak Asep', DATE('2026-09-12'), 'WhatsApp Image 2026-09-09 at 09.08.51.jpeg'
    UNION ALL SELECT 'Penanaman Bibit Baru', 'Musim Kemarau', DATE('2026-09-10'), 'FOTO.jpeg'
    UNION ALL SELECT 'Pembibitan', 'Rumah Bibit', DATE('2026-09-08'), 'DAUN.jpeg'
) AS awal
WHERE NOT EXISTS (SELECT 1 FROM dokumentasi);
