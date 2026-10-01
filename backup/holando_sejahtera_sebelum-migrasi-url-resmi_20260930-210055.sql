-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: holando_sejahtera
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `holando_sejahtera`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `holando_sejahtera` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `holando_sejahtera`;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL DEFAULT 'Admin',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'admin','$2y$10$F2EAvL0qfVZCmxxeqM3USOuuu5EyJhnr7NwtVJYm/4iDhCqgqrmhK','Admin','2026-09-30 20:41:06','2026-09-25 08:37:29');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akses_panduan`
--

DROP TABLE IF EXISTS `akses_panduan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akses_panduan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sumber` enum('beranda','panduan-kentang') NOT NULL,
  `pesanan_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_panduan_created` (`created_at`),
  KEY `fk_akses_pesanan` (`pesanan_id`),
  CONSTRAINT `fk_akses_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akses_panduan`
--

LOCK TABLES `akses_panduan` WRITE;
/*!40000 ALTER TABLE `akses_panduan` DISABLE KEYS */;
INSERT INTO `akses_panduan` VALUES (15,'panduan-kentang',10,'2026-09-25 10:04:02'),(16,'beranda',13,'2026-09-25 10:15:54'),(20,'beranda',18,'2026-09-25 13:43:27'),(21,'beranda',19,'2026-09-25 22:01:01');
/*!40000 ALTER TABLE `akses_panduan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dokumentasi`
--

DROP TABLE IF EXISTS `dokumentasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dokumentasi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(100) NOT NULL,
  `keterangan` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_dokumentasi_tampil` (`aktif`,`tanggal`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dokumentasi`
--

LOCK TABLES `dokumentasi` WRITE;
/*!40000 ALTER TABLE `dokumentasi` DISABLE KEYS */;
INSERT INTO `dokumentasi` VALUES (1,'Kebun Kentang','Desa Cihideung','2026-09-15','WhatsApp Image 2026-09-09 at 09.08.51 (1).jpeg',1,'2026-09-28 15:22:57','2026-09-28 15:22:57'),(2,'Panen Kentang','Kebun Pak Asep','2026-09-12','WhatsApp Image 2026-09-09 at 09.08.51.jpeg',1,'2026-09-28 15:22:57','2026-09-28 15:22:57'),(3,'Kunjungan Industri dan pengecekan tanaman bersama','Musim Kemarau','2026-09-10','FOTO.jpeg',1,'2026-09-28 15:22:57','2026-09-28 15:37:42'),(4,'Dokumentasi Tanaman','Rumah Bibit','2026-09-08','DAUN.jpeg',1,'2026-09-28 15:22:57','2026-09-28 15:37:08');
/*!40000 ALTER TABLE `dokumentasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori_bibit`
--

DROP TABLE IF EXISTS `kategori_bibit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori_bibit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kategori_bibit_nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori_bibit`
--

LOCK TABLES `kategori_bibit` WRITE;
/*!40000 ALTER TABLE `kategori_bibit` DISABLE KEYS */;
INSERT INTO `kategori_bibit` VALUES (1,'Granola',1,'2026-09-25 15:03:08','2026-09-25 15:03:08'),(2,'Atlantic',1,'2026-09-25 22:13:56','2026-09-25 22:13:56');
/*!40000 ALTER TABLE `kategori_bibit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kode_produk`
--

DROP TABLE IF EXISTS `kode_produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kode_produk` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL,
  `produk_id` int(10) unsigned DEFAULT NULL,
  `asal` varchar(100) NOT NULL DEFAULT '',
  `tanggal_terdaftar` date NOT NULL,
  `status` enum('aktif','dicabut') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kode_produk` (`kode`),
  KEY `fk_kode_produk` (`produk_id`),
  CONSTRAINT `fk_kode_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kode_produk`
--

LOCK TABLES `kode_produk` WRITE;
/*!40000 ALTER TABLE `kode_produk` DISABLE KEYS */;
INSERT INTO `kode_produk` VALUES (1,'TANI-001',1,'Bandung','2026-09-20','aktif','2026-09-25 08:37:29'),(2,'TANI-002',2,'Bandung','2026-09-20','aktif','2026-09-25 08:37:29'),(5,'TANI-005',6,'Bandung','2026-09-20','aktif','2026-09-25 08:37:29');
/*!40000 ALTER TABLE `kode_produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_verifikasi`
--

DROP TABLE IF EXISTS `log_verifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_verifikasi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL,
  `valid` tinyint(1) NOT NULL,
  `kode_produk_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_log_created` (`created_at`),
  KEY `fk_log_kode` (`kode_produk_id`),
  CONSTRAINT `fk_log_kode` FOREIGN KEY (`kode_produk_id`) REFERENCES `kode_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_verifikasi`
--

LOCK TABLES `log_verifikasi` WRITE;
/*!40000 ALTER TABLE `log_verifikasi` DISABLE KEYS */;
INSERT INTO `log_verifikasi` VALUES (10,'BPSB-TEST-9001',1,NULL,'2026-09-25 22:37:07'),(11,'BPSB-TEST-0000',0,NULL,'2026-09-25 22:37:08'),(12,'BPSB-TEST-9001',0,NULL,'2026-09-25 22:37:08'),(13,'TANI-001',1,1,'2026-09-25 22:37:08'),(14,'SCAN-TEST-77',1,NULL,'2026-09-25 22:49:56');
/*!40000 ALTER TABLE `log_verifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mitra`
--

DROP TABLE IF EXISTS `mitra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mitra` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `luas_lahan` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `chk_mitra_lahan` CHECK (`luas_lahan` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mitra`
--

LOCK TABLES `mitra` WRITE;
/*!40000 ALTER TABLE `mitra` DISABLE KEYS */;
/*!40000 ALTER TABLE `mitra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesan_kontak`
--

DROP TABLE IF EXISTS `pesan_kontak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pesan_kontak` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `pesan` varchar(1000) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kontak_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesan_kontak`
--

LOCK TABLES `pesan_kontak` WRITE;
/*!40000 ALTER TABLE `pesan_kontak` DISABLE KEYS */;
/*!40000 ALTER TABLE `pesan_kontak` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesanan`
--

DROP TABLE IF EXISTS `pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pesanan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kode_pesanan` varchar(20) NOT NULL,
  `nama_penerima` varchar(100) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `metode_bayar` enum('Transfer Bank','COD (Bayar di Tempat)','Bayar via WhatsApp') NOT NULL,
  `total` bigint(20) unsigned NOT NULL DEFAULT 0,
  `status` enum('baru','diproses','selesai','batal') NOT NULL DEFAULT 'baru',
  `stok_dipotong` tinyint(1) NOT NULL DEFAULT 0,
  `checkout_token` char(32) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pesanan_kode` (`kode_pesanan`),
  UNIQUE KEY `uq_pesanan_checkout_token` (`checkout_token`),
  KEY `idx_pesanan_status` (`status`),
  KEY `idx_pesanan_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesanan`
--

LOCK TABLES `pesanan` WRITE;
/*!40000 ALTER TABLE `pesanan` DISABLE KEYS */;
INSERT INTO `pesanan` VALUES (9,'PSN-260925-0F4C53','xample','KP Cigaok xample','085880802425','COD (Bayar di Tempat)',15000,'selesai',0,NULL,'2026-09-25 09:49:17','2026-09-25 10:15:26'),(10,'PSN-260925-9C6DF0','xample','kp.xample 0000','0858808090','Bayar via WhatsApp',30000,'batal',0,NULL,'2026-09-25 09:51:15','2026-09-25 10:04:06'),(13,'PSN-260925-DB4DAD','xamplle','kp.xamplee222','089090900909','Bayar via WhatsApp',27000,'selesai',0,NULL,'2026-09-25 10:15:12','2026-09-25 10:15:30'),(18,'PSN-260925-895236','Asep','Kp,example001','0878637838','Bayar via WhatsApp',350000,'selesai',1,'d106345beb811007eab1ca446fcf38d6','2026-09-25 13:42:27','2026-09-25 13:43:20'),(19,'PSN-260925-4D6D8E','Jaweng','KP.example002','089078565690','COD (Bayar di Tempat)',35000,'selesai',1,'0c972a125dc43592c323b479e54ce75c','2026-09-25 21:58:11','2026-09-25 22:00:26');
/*!40000 ALTER TABLE `pesanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesanan_item`
--

DROP TABLE IF EXISTS `pesanan_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pesanan_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pesanan_id` int(10) unsigned NOT NULL,
  `produk_id` int(10) unsigned DEFAULT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` int(10) unsigned NOT NULL,
  `jumlah` smallint(5) unsigned NOT NULL,
  `subtotal` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_item_pesanan` (`pesanan_id`),
  KEY `fk_item_produk` (`produk_id`),
  CONSTRAINT `fk_item_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_item_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_item_jumlah` CHECK (`jumlah` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesanan_item`
--

LOCK TABLES `pesanan_item` WRITE;
/*!40000 ALTER TABLE `pesanan_item` DISABLE KEYS */;
INSERT INTO `pesanan_item` VALUES (11,9,1,'Kentang Pilihan Premium',15000,1,15000),(12,10,1,'Kentang Pilihan Premium',15000,2,30000),(15,13,1,'Kentang Pilihan Premium',15000,1,15000),(16,13,2,'Wortel Segar',12000,1,12000),(22,18,5,'L Benih Generasi 2, Granola L, Ukuran Besar',25000,10,250000),(23,18,6,'SM Benih Generasi 3, Granola L, Ukuran Sedang',10000,10,100000),(24,19,5,'L Benih Generasi 2, Granola L, Ukuran Besar',25000,1,25000),(25,19,6,'SM Benih Generasi 3, Granola L, Ukuran Sedang',10000,1,10000);
/*!40000 ALTER TABLE `pesanan_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produk` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `kategori_id` int(10) unsigned DEFAULT NULL,
  `deskripsi` varchar(255) NOT NULL DEFAULT '',
  `harga` int(10) unsigned NOT NULL,
  `satuan` varchar(20) NOT NULL DEFAULT 'kg',
  `gambar` varchar(255) NOT NULL DEFAULT '',
  `badge` varchar(30) DEFAULT NULL,
  `stok` int(10) unsigned NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_produk_aktif` (`aktif`),
  KEY `idx_produk_kategori` (`kategori_id`),
  CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_bibit` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `chk_produk_harga` CHECK (`harga` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk`
--

LOCK TABLES `produk` WRITE;
/*!40000 ALTER TABLE `produk` DISABLE KEYS */;
INSERT INTO `produk` VALUES (1,'SM Benih Generasi 2, Granola L, Ukuran Sedang',1,'',15000,'kg','sml.jpg','Baru',1200,1,'2026-09-25 08:37:29','2026-09-25 15:25:18'),(2,'Ss/SSS Benih Generasi 2, Granola L, Ukuran Sangat Kecil / Bibit Kecil Premium',1,'',12000,'kg','ss.jpg',NULL,800,1,'2026-09-25 08:37:29','2026-09-25 15:25:18'),(5,'L Benih Generasi 2, Granola L, Ukuran Besar',1,'',25000,'kg','l.jpg','Diskon',489,1,'2026-09-25 08:37:29','2026-09-25 21:58:11'),(6,'SM Benih Generasi 3, Granola L, Ukuran Sedang',1,'',10000,'kg','xl.jpg',NULL,889,1,'2026-09-25 08:37:29','2026-09-25 21:58:11');
/*!40000 ALTER TABLE `produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimoni`
--

DROP TABLE IF EXISTS `testimoni`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimoni` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `pesan` varchar(1000) NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `tampil` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_testimoni_tampil` (`tampil`),
  CONSTRAINT `chk_testimoni_rating` CHECK (`rating` between 1 and 5)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimoni`
--

LOCK TABLES `testimoni` WRITE;
/*!40000 ALTER TABLE `testimoni` DISABLE KEYS */;
INSERT INTO `testimoni` VALUES (6,'Julpian','luar biasa',NULL,0,'2026-09-25 13:44:43'),(7,'Yanwar, Petani bandung','Bibit yang berkualitas',4.0,1,'2026-09-25 14:00:41'),(8,'Julpian','wow hasilnya luar biasa',5.0,1,'2026-09-25 21:33:13');
/*!40000 ALTER TABLE `testimoni` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'holando_sejahtera'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-30 21:00:56
