-- =========================================================
--  DATABASE PT KENTANG HOLANDO SEJAHTERA / BEKEN SEEDS
--  MySQL 5.7+ / MariaDB 10.2+
--  Import: mysql -u root -p < database/database.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS holando_sejahtera
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE holando_sejahtera;

-- ---------------------------------------------------------
-- Akun admin (login.html, dashboard.html, admin.html)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nama          VARCHAR(100) NOT NULL DEFAULT 'Admin',
    last_login_at DATETIME     NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Jenis/varietas bibit kentang (kategori katalog). Dikelola admin
-- (dashboard.html → Bibit → Jenis Bibit). Generasi & ukuran bukan kategori.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS kategori_bibit (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(50) NOT NULL,
    aktif      TINYINT(1)  NOT NULL DEFAULT 1,
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_kategori_bibit_nama (nama)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Katalog bibit kentang (index.html #katalog)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS produk (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL,
    kategori_id INT UNSIGNED NULL,
    deskripsi  VARCHAR(255) NOT NULL DEFAULT '',
    harga      INT UNSIGNED NOT NULL,
    satuan     VARCHAR(20)  NOT NULL DEFAULT 'kg',
    gambar     VARCHAR(255) NOT NULL DEFAULT '',
    badge      VARCHAR(30)  NULL,
    stok       INT UNSIGNED NOT NULL DEFAULT 0,
    aktif      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_produk_aktif (aktif),
    KEY idx_produk_kategori (kategori_id),
    CONSTRAINT fk_produk_kategori FOREIGN KEY (kategori_id) REFERENCES kategori_bibit(id)
        ON UPDATE CASCADE,
    CONSTRAINT chk_produk_harga CHECK (harga > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Pesanan dari keranjang (index.html #cartModal)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS pesanan (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan  VARCHAR(20)  NOT NULL,
    nama_penerima VARCHAR(100) NOT NULL,
    alamat        VARCHAR(255) NOT NULL,
    no_wa         VARCHAR(20)  NOT NULL,
    metode_bayar  ENUM('Transfer Bank','COD (Bayar di Tempat)','Bayar via WhatsApp') NOT NULL,
    total         BIGINT UNSIGNED NOT NULL DEFAULT 0,
    status        ENUM('baru','diproses','selesai','batal') NOT NULL DEFAULT 'baru',
    -- 1 = stok produk sudah dipotong untuk pesanan ini dan belum dikembalikan
    stok_dipotong TINYINT(1) NOT NULL DEFAULT 0,
    -- Token acak per percobaan checkout; mencegah pesanan ganda saat request diulang
    checkout_token CHAR(32) NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pesanan_kode (kode_pesanan),
    UNIQUE KEY uq_pesanan_checkout_token (checkout_token),
    KEY idx_pesanan_status (status),
    KEY idx_pesanan_created (created_at)
) ENGINE=InnoDB;

-- Nama & harga disalin saat pesanan dibuat agar riwayat tetap utuh
-- walaupun produk kemudian diubah atau dihapus.
CREATE TABLE IF NOT EXISTS pesanan_item (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pesanan_id  INT UNSIGNED NOT NULL,
    produk_id   INT UNSIGNED NULL,
    nama_produk VARCHAR(100) NOT NULL,
    harga       INT UNSIGNED NOT NULL,
    jumlah      SMALLINT UNSIGNED NOT NULL,
    subtotal    BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_item_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_item_produk FOREIGN KEY (produk_id) REFERENCES produk(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_item_jumlah CHECK (jumlah > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Kode verifikasi produk (index.html #alat - Cek Kode Manual)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS kode_produk (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode              VARCHAR(50)  NOT NULL,
    produk_id         INT UNSIGNED NULL,
    asal              VARCHAR(100) NOT NULL DEFAULT '',
    tanggal_terdaftar DATE         NOT NULL,
    status            ENUM('aktif','dicabut') NOT NULL DEFAULT 'aktif',
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_kode_produk (kode),
    CONSTRAINT fk_kode_produk FOREIGN KEY (produk_id) REFERENCES produk(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Riwayat setiap pengecekan kode (dashboard.html - Aktivitas Verifikasi)
CREATE TABLE IF NOT EXISTS log_verifikasi (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode           VARCHAR(50) NOT NULL,
    valid          TINYINT(1)  NOT NULL,
    kode_produk_id INT UNSIGNED NULL,
    created_at     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_log_created (created_at),
    CONSTRAINT fk_log_kode FOREIGN KEY (kode_produk_id) REFERENCES kode_produk(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Testimoni / ulasan (index.html #ulasanForm, admin.html)
-- Ulasan dari form publik langsung tampil (tampil = 1); admin dapat menyembunyikan.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS testimoni (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)  NOT NULL,
    pesan      VARCHAR(1000) NOT NULL,
    -- NULL = ulasan lama tanpa rating (jangan dianggap 5)
    rating     DECIMAL(2,1)  NULL DEFAULT NULL,
    tampil     TINYINT(1)    NOT NULL DEFAULT 0,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_testimoni_tampil (tampil),
    CONSTRAINT chk_testimoni_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Pesan dari form kontak (index.html #kontakForm)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS pesan_kontak (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)  NOT NULL,
    no_wa      VARCHAR(20)   NOT NULL,
    pesan      VARCHAR(1000) NOT NULL,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_kontak_created (created_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Mitra pembudidaya (admin.html tab Data Mitra)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS mitra (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)  NOT NULL,
    no_wa      VARCHAR(20)   NOT NULL,
    lokasi     VARCHAR(150)  NOT NULL,
    luas_lahan DECIMAL(10,2) NOT NULL,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_mitra_lahan CHECK (luas_lahan > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Pencatatan akses panduan (index.html #panduan, panduan-kentang.html)
-- Satu baris per verifikasi pesanan yang berhasil membuka panduan.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS akses_panduan (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sumber     ENUM('beranda','panduan-kentang') NOT NULL,
    pesanan_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_panduan_created (created_at),
    CONSTRAINT fk_akses_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
--  DATA AWAL (diambil dari data yang sebelumnya hardcoded)
-- =========================================================

-- Admin default: username "admin", password "beken2026".
-- SEGERA GANTI setelah instalasi:
--   php database/set-admin-password.php admin <password-baru>
INSERT IGNORE INTO admin (username, password_hash, nama) VALUES
('admin', '$2y$10$wgbhz9SXSLaK3ZvvtMGrgOVMAjc2h1uPTuU6dNCqedOgAxCQa75Gq', 'Admin');

-- Data awal = bibit yang dijual saat ini (varietas Granola). Deskripsi dikosongkan:
-- isi dari admin, jangan dikarang.
INSERT IGNORE INTO kategori_bibit (id, nama) VALUES (1, 'Granola');

INSERT IGNORE INTO produk (id, nama, kategori_id, deskripsi, harga, satuan, gambar, badge, stok) VALUES
(1, 'SM Benih Generasi 2, Granola L, Ukuran Sedang', 1, '', 15000, 'kg', 'sml.jpg', 'Baru', 1200),
(2, 'Ss/SSS Benih Generasi 2, Granola L, Ukuran Sangat Kecil / Bibit Kecil Premium', 1, '', 12000, 'kg', 'ss.jpg', NULL, 800),
(5, 'L Benih Generasi 2, Granola L, Ukuran Besar', 1, '', 25000, 'kg', 'l.jpg', 'Diskon', 490),
(6, 'SM Benih Generasi 3, Granola L, Ukuran Sedang', 1, '', 10000, 'kg', 'xl.jpg', NULL, 890);

INSERT IGNORE INTO kode_produk (kode, produk_id, asal, tanggal_terdaftar) VALUES
('TANI-001', 1, 'Bandung', '2026-09-20'),
('TANI-002', 2, 'Bandung', '2026-09-20'),
('TANI-003', NULL, 'Bandung', '2026-09-20'),
('TANI-004', NULL, 'Bandung', '2026-09-20'),
('TANI-005', 6, 'Bandung', '2026-09-20');

-- Tidak ada seed ulasan: ulasan hanya berasal dari pelanggan (form di index.html).
