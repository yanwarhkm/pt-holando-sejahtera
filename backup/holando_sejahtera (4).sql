-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026 at 07:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `holando_sejahtera`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL DEFAULT 'Admin',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password_hash`, `nama`, `last_login_at`, `created_at`) VALUES
(1, 'admin', '$2y$10$F2EAvL0qfVZCmxxeqM3USOuuu5EyJhnr7NwtVJYm/4iDhCqgqrmhK', 'Admin', '2026-09-28 15:36:03', '2026-09-25 08:37:29');

-- --------------------------------------------------------

--
-- Table structure for table `akses_panduan`
--

CREATE TABLE `akses_panduan` (
  `id` int(10) UNSIGNED NOT NULL,
  `sumber` enum('beranda','panduan-kentang') NOT NULL,
  `pesanan_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `akses_panduan`
--

INSERT INTO `akses_panduan` (`id`, `sumber`, `pesanan_id`, `created_at`) VALUES
(15, 'panduan-kentang', 10, '2026-09-25 10:04:02'),
(16, 'beranda', 13, '2026-09-25 10:15:54'),
(20, 'beranda', 18, '2026-09-25 13:43:27'),
(21, 'beranda', 19, '2026-09-25 22:01:01');

-- --------------------------------------------------------

--
-- Table structure for table `dokumentasi`
--

CREATE TABLE `dokumentasi` (
  `id` int(10) UNSIGNED NOT NULL,
  `judul` varchar(100) NOT NULL,
  `keterangan` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dokumentasi`
--

INSERT INTO `dokumentasi` (`id`, `judul`, `keterangan`, `tanggal`, `gambar`, `aktif`, `created_at`, `updated_at`) VALUES
(1, 'Kebun Kentang', 'Desa Cihideung', '2026-09-15', 'WhatsApp Image 2026-09-09 at 09.08.51 (1).jpeg', 1, '2026-09-28 15:22:57', '2026-09-28 15:22:57'),
(2, 'Panen Kentang', 'Kebun Pak Asep', '2026-09-12', 'WhatsApp Image 2026-09-09 at 09.08.51.jpeg', 1, '2026-09-28 15:22:57', '2026-09-28 15:22:57'),
(3, 'Kunjungan Industri dan pengecekan tanaman bersama', 'Musim Kemarau', '2026-09-10', 'FOTO.jpeg', 1, '2026-09-28 15:22:57', '2026-09-28 15:37:42'),
(4, 'Dokumentasi Tanaman', 'Rumah Bibit', '2026-09-08', 'DAUN.jpeg', 1, '2026-09-28 15:22:57', '2026-09-28 15:37:08');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_bibit`
--

CREATE TABLE `kategori_bibit` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(50) NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_bibit`
--

INSERT INTO `kategori_bibit` (`id`, `nama`, `aktif`, `created_at`, `updated_at`) VALUES
(1, 'Granola', 1, '2026-09-25 15:03:08', '2026-09-25 15:03:08'),
(2, 'Atlantic', 1, '2026-09-25 22:13:56', '2026-09-25 22:13:56');

-- --------------------------------------------------------

--
-- Table structure for table `kode_produk`
--

CREATE TABLE `kode_produk` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode` varchar(50) NOT NULL,
  `produk_id` int(10) UNSIGNED DEFAULT NULL,
  `asal` varchar(100) NOT NULL DEFAULT '',
  `tanggal_terdaftar` date NOT NULL,
  `status` enum('aktif','dicabut') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kode_produk`
--

INSERT INTO `kode_produk` (`id`, `kode`, `produk_id`, `asal`, `tanggal_terdaftar`, `status`, `created_at`) VALUES
(1, 'TANI-001', 1, 'Bandung', '2026-09-20', 'aktif', '2026-09-25 08:37:29'),
(2, 'TANI-002', 2, 'Bandung', '2026-09-20', 'aktif', '2026-09-25 08:37:29'),
(5, 'TANI-005', 6, 'Bandung', '2026-09-20', 'aktif', '2026-09-25 08:37:29');

-- --------------------------------------------------------

--
-- Table structure for table `log_verifikasi`
--

CREATE TABLE `log_verifikasi` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode` varchar(50) NOT NULL,
  `valid` tinyint(1) NOT NULL,
  `kode_produk_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `log_verifikasi`
--

INSERT INTO `log_verifikasi` (`id`, `kode`, `valid`, `kode_produk_id`, `created_at`) VALUES
(10, 'BPSB-TEST-9001', 1, NULL, '2026-09-25 22:37:07'),
(11, 'BPSB-TEST-0000', 0, NULL, '2026-09-25 22:37:08'),
(12, 'BPSB-TEST-9001', 0, NULL, '2026-09-25 22:37:08'),
(13, 'TANI-001', 1, 1, '2026-09-25 22:37:08'),
(14, 'SCAN-TEST-77', 1, NULL, '2026-09-25 22:49:56');

-- --------------------------------------------------------

--
-- Table structure for table `mitra`
--

CREATE TABLE `mitra` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `luas_lahan` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_pesanan` varchar(20) NOT NULL,
  `nama_penerima` varchar(100) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `metode_bayar` enum('Transfer Bank','COD (Bayar di Tempat)','Bayar via WhatsApp') NOT NULL,
  `total` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('baru','diproses','selesai','batal') NOT NULL DEFAULT 'baru',
  `stok_dipotong` tinyint(1) NOT NULL DEFAULT 0,
  `checkout_token` char(32) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `kode_pesanan`, `nama_penerima`, `alamat`, `no_wa`, `metode_bayar`, `total`, `status`, `stok_dipotong`, `checkout_token`, `created_at`, `updated_at`) VALUES
(9, 'PSN-260925-0F4C53', 'xample', 'KP Cigaok xample', '085880802425', 'COD (Bayar di Tempat)', 15000, 'selesai', 0, NULL, '2026-09-25 09:49:17', '2026-09-25 10:15:26'),
(10, 'PSN-260925-9C6DF0', 'xample', 'kp.xample 0000', '0858808090', 'Bayar via WhatsApp', 30000, 'batal', 0, NULL, '2026-09-25 09:51:15', '2026-09-25 10:04:06'),
(13, 'PSN-260925-DB4DAD', 'xamplle', 'kp.xamplee222', '089090900909', 'Bayar via WhatsApp', 27000, 'selesai', 0, NULL, '2026-09-25 10:15:12', '2026-09-25 10:15:30'),
(18, 'PSN-260925-895236', 'Asep', 'Kp,example001', '0878637838', 'Bayar via WhatsApp', 350000, 'selesai', 1, 'd106345beb811007eab1ca446fcf38d6', '2026-09-25 13:42:27', '2026-09-25 13:43:20'),
(19, 'PSN-260925-4D6D8E', 'Jaweng', 'KP.example002', '089078565690', 'COD (Bayar di Tempat)', 35000, 'selesai', 1, '0c972a125dc43592c323b479e54ce75c', '2026-09-25 21:58:11', '2026-09-25 22:00:26');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan_item`
--

CREATE TABLE `pesanan_item` (
  `id` int(10) UNSIGNED NOT NULL,
  `pesanan_id` int(10) UNSIGNED NOT NULL,
  `produk_id` int(10) UNSIGNED DEFAULT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` int(10) UNSIGNED NOT NULL,
  `jumlah` smallint(5) UNSIGNED NOT NULL,
  `subtotal` bigint(20) UNSIGNED NOT NULL
) ;

--
-- Dumping data for table `pesanan_item`
--

INSERT INTO `pesanan_item` (`id`, `pesanan_id`, `produk_id`, `nama_produk`, `harga`, `jumlah`, `subtotal`) VALUES
(11, 9, 1, 'Kentang Pilihan Premium', 15000, 1, 15000),
(12, 10, 1, 'Kentang Pilihan Premium', 15000, 2, 30000),
(15, 13, 1, 'Kentang Pilihan Premium', 15000, 1, 15000),
(16, 13, 2, 'Wortel Segar', 12000, 1, 12000),
(22, 18, 5, 'L Benih Generasi 2, Granola L, Ukuran Besar', 25000, 10, 250000),
(23, 18, 6, 'SM Benih Generasi 3, Granola L, Ukuran Sedang', 10000, 10, 100000),
(24, 19, 5, 'L Benih Generasi 2, Granola L, Ukuran Besar', 25000, 1, 25000),
(25, 19, 6, 'SM Benih Generasi 3, Granola L, Ukuran Sedang', 10000, 1, 10000);

-- --------------------------------------------------------

--
-- Table structure for table `pesan_kontak`
--

CREATE TABLE `pesan_kontak` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `pesan` varchar(1000) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori_id` int(10) UNSIGNED DEFAULT NULL,
  `deskripsi` varchar(255) NOT NULL DEFAULT '',
  `harga` int(10) UNSIGNED NOT NULL,
  `satuan` varchar(20) NOT NULL DEFAULT 'kg',
  `gambar` varchar(255) NOT NULL DEFAULT '',
  `badge` varchar(30) DEFAULT NULL,
  `stok` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `nama`, `kategori_id`, `deskripsi`, `harga`, `satuan`, `gambar`, `badge`, `stok`, `aktif`, `created_at`, `updated_at`) VALUES
(1, 'SM Benih Generasi 2, Granola L, Ukuran Sedang', 1, '', 15000, 'kg', 'sml.jpg', 'Baru', 1200, 1, '2026-09-25 08:37:29', '2026-09-25 15:25:18'),
(2, 'Ss/SSS Benih Generasi 2, Granola L, Ukuran Sangat Kecil / Bibit Kecil Premium', 1, '', 12000, 'kg', 'ss.jpg', NULL, 800, 1, '2026-09-25 08:37:29', '2026-09-25 15:25:18'),
(5, 'L Benih Generasi 2, Granola L, Ukuran Besar', 1, '', 25000, 'kg', 'l.jpg', 'Diskon', 489, 1, '2026-09-25 08:37:29', '2026-09-25 21:58:11'),
(6, 'SM Benih Generasi 3, Granola L, Ukuran Sedang', 1, '', 10000, 'kg', 'xl.jpg', NULL, 889, 1, '2026-09-25 08:37:29', '2026-09-25 21:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `testimoni`
--

CREATE TABLE `testimoni` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `pesan` varchar(1000) NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `tampil` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `testimoni`
--

INSERT INTO `testimoni` (`id`, `nama`, `pesan`, `rating`, `tampil`, `created_at`) VALUES
(6, 'Julpian', 'luar biasa', NULL, 0, '2026-09-25 13:44:43'),
(7, 'Yanwar, Petani bandung', 'Bibit yang berkualitas', 4.0, 1, '2026-09-25 14:00:41'),
(8, 'Julpian', 'wow hasilnya luar biasa', 5.0, 1, '2026-09-25 21:33:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admin_username` (`username`);

--
-- Indexes for table `akses_panduan`
--
ALTER TABLE `akses_panduan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_panduan_created` (`created_at`),
  ADD KEY `fk_akses_pesanan` (`pesanan_id`);

--
-- Indexes for table `dokumentasi`
--
ALTER TABLE `dokumentasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dokumentasi_tampil` (`aktif`,`tanggal`);

--
-- Indexes for table `kategori_bibit`
--
ALTER TABLE `kategori_bibit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_kategori_bibit_nama` (`nama`);

--
-- Indexes for table `kode_produk`
--
ALTER TABLE `kode_produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_kode_produk` (`kode`),
  ADD KEY `fk_kode_produk` (`produk_id`);

--
-- Indexes for table `log_verifikasi`
--
ALTER TABLE `log_verifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_created` (`created_at`),
  ADD KEY `fk_log_kode` (`kode_produk_id`);

--
-- Indexes for table `mitra`
--
ALTER TABLE `mitra`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pesanan_kode` (`kode_pesanan`),
  ADD UNIQUE KEY `uq_pesanan_checkout_token` (`checkout_token`),
  ADD KEY `idx_pesanan_status` (`status`),
  ADD KEY `idx_pesanan_created` (`created_at`);

--
-- Indexes for table `pesanan_item`
--
ALTER TABLE `pesanan_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_item_pesanan` (`pesanan_id`),
  ADD KEY `fk_item_produk` (`produk_id`);

--
-- Indexes for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kontak_created` (`created_at`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produk_aktif` (`aktif`),
  ADD KEY `idx_produk_kategori` (`kategori_id`);

--
-- Indexes for table `testimoni`
--
ALTER TABLE `testimoni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_testimoni_tampil` (`tampil`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `akses_panduan`
--
ALTER TABLE `akses_panduan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `dokumentasi`
--
ALTER TABLE `dokumentasi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `kategori_bibit`
--
ALTER TABLE `kategori_bibit`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kode_produk`
--
ALTER TABLE `kode_produk`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `log_verifikasi`
--
ALTER TABLE `log_verifikasi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `mitra`
--
ALTER TABLE `mitra`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `pesanan_item`
--
ALTER TABLE `pesanan_item`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimoni`
--
ALTER TABLE `testimoni`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `akses_panduan`
--
ALTER TABLE `akses_panduan`
  ADD CONSTRAINT `fk_akses_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `kode_produk`
--
ALTER TABLE `kode_produk`
  ADD CONSTRAINT `fk_kode_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `log_verifikasi`
--
ALTER TABLE `log_verifikasi`
  ADD CONSTRAINT `fk_log_kode` FOREIGN KEY (`kode_produk_id`) REFERENCES `kode_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pesanan_item`
--
ALTER TABLE `pesanan_item`
  ADD CONSTRAINT `fk_item_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_item_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_bibit` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
