-- =========================================================
--  MIGRASI: URL QR resmi pada data verifikasi (kode_produk)
--  Untuk database yang sudah dibuat dengan versi database.sql sebelumnya,
--  termasuk database production (InfinityFree).
--
--  CARA MENJALANKAN
--  1. BACKUP DULU. phpMyAdmin -> pilih database -> Export -> Quick -> SQL.
--     (lokal: mysqldump -u root -p --databases holando_sejahtera > backup.sql)
--  2. PILIH database target dulu (phpMyAdmin: klik nama database di kiri,
--     lalu tab SQL; lokal: mysql -u root -p holando_sejahtera < file ini).
--     Sengaja TIDAK ada "USE ..." karena nama database production berbeda
--     (mis. if0_xxxxxxxx_...).
--  3. Jalankan seluruh isi file. Hasil akhir berupa tabel "cek" - semua OK.
--
--  AMAN DIJALANKAN ULANG (idempotent): setiap langkah memeriksa
--  information_schema dan hanya berjalan bila perubahan belum ada.
--  Tidak ada DROP TABLE, DELETE, UPDATE, atau perubahan isi data.
--  Catatan: ALTER TABLE di MySQL/MariaDB auto-commit (tidak bisa di-rollback),
--  karena itu backup di langkah 1 wajib.
--
--  PERUBAHAN
--  kode_produk.kode       -> boleh NULL (label bisa hanya punya QR URL).
--                           Tipe, charset & collation lama dipertahankan;
--                           UNIQUE tetap berlaku untuk kode yang diisi.
--  kode_produk.url_resmi  -> kolom baru VARCHAR(255) NULL, utf8mb4_bin agar
--                           pencocokan persis (path URL peka huruf besar/kecil).
--                           TIDAK UNIQUE: satu URL boleh terhubung ke
--                           beberapa data internal.
--  idx_kode_produk_url    -> index biasa (prefix 191 agar aman di batas
--                           767 byte pada server/row format lama).
--  chk_kode_produk_isi    -> CHECK minimal kode atau url_resmi terisi (hanya
--                           bila server mendukung CHECK; API tetap memvalidasi).
--  log_verifikasi.kode    -> diperlebar ke VARCHAR(255) agar URL bisa dicatat.
-- =========================================================

SET @db := DATABASE();

-- ---------------------------------------------------------
-- 1. kode_produk.kode boleh NULL (pertahankan tipe/charset/collation)
-- ---------------------------------------------------------
SET @sql := (
    SELECT IF(IS_NULLABLE = 'NO',
        CONCAT('ALTER TABLE `kode_produk` MODIFY `kode` ', COLUMN_TYPE,
               ' CHARACTER SET ', CHARACTER_SET_NAME, ' COLLATE ', COLLATION_NAME, ' NULL DEFAULT NULL'),
        'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk' AND COLUMN_NAME = 'kode'
);
SET @sql := COALESCE(@sql, 'DO 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------
-- 2. kode tetap UNIQUE (tambah hanya bila belum ada index unik pada kode)
-- ---------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk'
       AND COLUMN_NAME = 'kode' AND NON_UNIQUE = 0 AND SEQ_IN_INDEX = 1) = 0
    AND (SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk') = 1,
    'ALTER TABLE `kode_produk` ADD UNIQUE KEY `uq_kode_produk` (`kode`)',
    'DO 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------
-- 3. kode_produk.url_resmi - tambah bila belum ada; samakan definisi bila
--    sudah ada tetapi berbeda (mis. dari percobaan manual sebelumnya)
-- ---------------------------------------------------------
SET @sql := (
    SELECT CASE
        WHEN (SELECT COUNT(*) FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk') = 0 THEN 'DO 0'
        WHEN COUNT(*) = 0 THEN
            'ALTER TABLE `kode_produk` ADD COLUMN `url_resmi` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL AFTER `kode`'
        WHEN MAX(COLUMN_TYPE) <> 'varchar(255)' OR MAX(COLLATION_NAME) <> 'utf8mb4_bin' OR MAX(IS_NULLABLE) <> 'YES' THEN
            'ALTER TABLE `kode_produk` MODIFY `url_resmi` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL'
        ELSE 'DO 0'
    END
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk' AND COLUMN_NAME = 'url_resmi'
);
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------
-- 4. url_resmi TIDAK boleh UNIQUE - lepas index unik bila pernah dibuat
--    (hanya index yang dilepas; data tidak berubah)
-- ---------------------------------------------------------
SET @sql := (
    SELECT COALESCE(
        CONCAT('ALTER TABLE `kode_produk` DROP INDEX `', MIN(INDEX_NAME), '`'),
        'DO 0')
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk'
      AND COLUMN_NAME = 'url_resmi' AND NON_UNIQUE = 0
);
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------
-- 5. Index biasa untuk lookup URL (bila belum ada index non-unik pada url_resmi)
-- ---------------------------------------------------------
SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk'
       AND COLUMN_NAME = 'url_resmi' AND SEQ_IN_INDEX = 1) = 0
    AND (SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk' AND COLUMN_NAME = 'url_resmi') = 1,
    'ALTER TABLE `kode_produk` ADD KEY `idx_kode_produk_url` (`url_resmi`(191))',
    'DO 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------
-- 6. CHECK minimal kode / url_resmi terisi - hanya pada server yang
--    menegakkan CHECK (MariaDB >= 10.2, MySQL >= 8.0.16) dan bila belum ada.
--    Semua data lama sudah punya kode, jadi constraint ini lolos.
-- ---------------------------------------------------------
SET @dukungCheck := (
    SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = 'information_schema' AND TABLE_NAME = 'CHECK_CONSTRAINTS'
);
SET @sql := IF(
    @dukungCheck = 1
    AND (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
         WHERE CONSTRAINT_SCHEMA = @db AND TABLE_NAME = 'kode_produk'
           AND CONSTRAINT_NAME = 'chk_kode_produk_isi') = 0
    AND (SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk' AND COLUMN_NAME = 'url_resmi') = 1,
    'ALTER TABLE `kode_produk` ADD CONSTRAINT `chk_kode_produk_isi` CHECK (`kode` IS NOT NULL OR `url_resmi` IS NOT NULL)',
    'DO 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------
-- 7. log_verifikasi.kode -> VARCHAR(255) (pertahankan charset/collation/NULL)
-- ---------------------------------------------------------
SET @sql := (
    SELECT IF(CHARACTER_MAXIMUM_LENGTH < 255,
        CONCAT('ALTER TABLE `log_verifikasi` MODIFY `kode` VARCHAR(255) CHARACTER SET ', CHARACTER_SET_NAME,
               ' COLLATE ', COLLATION_NAME, IF(IS_NULLABLE = 'NO', ' NOT NULL', ' NULL')),
        'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'log_verifikasi' AND COLUMN_NAME = 'kode'
);
SET @sql := COALESCE(@sql, 'DO 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------
-- 8. Hasil pemeriksaan (semua baris harus OK)
-- ---------------------------------------------------------
SELECT 'kode_produk.kode boleh NULL' AS cek,
       IF((SELECT IS_NULLABLE FROM information_schema.COLUMNS
           WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk' AND COLUMN_NAME = 'kode') = 'YES', 'OK', 'BELUM') AS hasil
UNION ALL
SELECT 'kode_produk.kode UNIQUE',
       IF((SELECT COUNT(*) FROM information_schema.STATISTICS
           WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk'
             AND COLUMN_NAME = 'kode' AND NON_UNIQUE = 0) > 0, 'OK', 'BELUM')
UNION ALL
SELECT 'kode_produk.url_resmi VARCHAR(255) NULL utf8mb4_bin',
       IF((SELECT COUNT(*) FROM information_schema.COLUMNS
           WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk' AND COLUMN_NAME = 'url_resmi'
             AND COLUMN_TYPE = 'varchar(255)' AND IS_NULLABLE = 'YES' AND COLLATION_NAME = 'utf8mb4_bin') = 1, 'OK', 'BELUM')
UNION ALL
SELECT 'kode_produk.url_resmi ber-index, TIDAK unique',
       IF((SELECT COUNT(*) FROM information_schema.STATISTICS
           WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk' AND COLUMN_NAME = 'url_resmi' AND NON_UNIQUE = 1) > 0
          AND (SELECT COUNT(*) FROM information_schema.STATISTICS
           WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'kode_produk' AND COLUMN_NAME = 'url_resmi' AND NON_UNIQUE = 0) = 0,
          'OK', 'BELUM')
UNION ALL
SELECT 'kode_produk CHECK minimal kode/url_resmi (chk_kode_produk_isi)',
       IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = @db AND TABLE_NAME = 'kode_produk'
             AND CONSTRAINT_NAME = 'chk_kode_produk_isi') > 0, 'OK',
          IF(@dukungCheck = 1, 'BELUM', 'tidak didukung server (API tetap memvalidasi)'))
UNION ALL
SELECT 'log_verifikasi.kode >= 255 karakter',
       IF((SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
           WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'log_verifikasi' AND COLUMN_NAME = 'kode') >= 255, 'OK', 'BELUM')
UNION ALL
SELECT 'Foreign key kode_produk -> produk (harus sama seperti sebelum migrasi)',
       IF((SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = @db AND TABLE_NAME = 'kode_produk' AND REFERENCED_TABLE_NAME = 'produk') > 0, 'ada', 'tidak ada')
UNION ALL
SELECT 'Foreign key log_verifikasi -> kode_produk (harus sama seperti sebelum migrasi)',
       IF((SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = @db AND TABLE_NAME = 'log_verifikasi' AND REFERENCED_TABLE_NAME = 'kode_produk') > 0, 'ada', 'tidak ada');
