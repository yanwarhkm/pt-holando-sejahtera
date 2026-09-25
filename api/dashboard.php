<?php
/* =========================================================
   API STATISTIK DASHBOARD (admin)
   GET → ringkasan untuk dashboard.html
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

const BATAS_STOK_MENIPIS = 50;

allow_methods('GET');
require_admin();
$pdo = db();

$kode = $pdo->query(
    "SELECT COUNT(*) AS total, COALESCE(SUM(status = 'aktif'), 0) AS aktif FROM kode_produk"
)->fetch();

$verifikasiHariIni = (int) $pdo->query(
    'SELECT COUNT(*) FROM log_verifikasi WHERE created_at >= CURDATE()'
)->fetchColumn();

$totalStok = (int) $pdo->query('SELECT COALESCE(SUM(stok), 0) FROM produk WHERE aktif = 1')->fetchColumn();

$aktivitas = $pdo->query(
    'SELECT kode, valid, created_at FROM log_verifikasi ORDER BY id DESC LIMIT 8'
)->fetchAll();

$stmt = $pdo->prepare('SELECT id, nama, stok, satuan FROM produk WHERE aktif = 1 AND stok < ? ORDER BY stok');
$stmt->execute([BATAS_STOK_MENIPIS]);
$stokMenipis = $stmt->fetchAll();

ok('Statistik berhasil dimuat.', [
    'total_kode'          => (int) $kode['total'],
    'kode_aktif'          => (int) $kode['aktif'],
    'verifikasi_hari_ini' => $verifikasiHariIni,
    'total_stok'          => $totalStok,
    'aktivitas'           => array_map(fn($r) => [
        'kode'       => $r['kode'],
        'valid'      => (bool) $r['valid'],
        'created_at' => $r['created_at'],
    ], $aktivitas),
    'stok_menipis'        => array_map(fn($r) => [
        'id'     => (int) $r['id'],
        'nama'   => $r['nama'],
        'stok'   => (int) $r['stok'],
        'satuan' => $r['satuan'],
    ], $stokMenipis),
]);
