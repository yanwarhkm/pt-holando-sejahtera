<?php
/* =========================================================
   API VERIFIKASI KODE PRODUK
   GET ?kode=TANI-001 → cek keaslian kode (publik, dicatat ke log)
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

allow_methods('GET');

$kode = $_GET['kode'] ?? '';
$kode = is_string($kode) ? strtoupper(trim($kode)) : '';
if ($kode === '') {
    fail('Kode produk wajib diisi.', 422);
}
if (!preg_match('/^[A-Z0-9][A-Z0-9\-\/]{1,49}$/', $kode)) {
    fail('Format kode produk tidak valid.', 422);
}

$pdo = db();
$stmt = $pdo->prepare(
    'SELECT k.id, k.kode, k.asal, k.tanggal_terdaftar, k.status, p.nama AS nama_produk
     FROM kode_produk k
     LEFT JOIN produk p ON p.id = k.produk_id
     WHERE k.kode = ?'
);
$stmt->execute([$kode]);
$row = $stmt->fetch();
$valid = $row !== false && $row['status'] === 'aktif';

$pdo->prepare('INSERT INTO log_verifikasi (kode, valid, kode_produk_id) VALUES (?, ?, ?)')
    ->execute([$kode, $valid ? 1 : 0, $row ? (int) $row['id'] : null]);

if (!$valid) {
    ok('Kode tidak terdaftar.', ['valid' => false, 'kode' => $kode]);
}

ok('Produk terverifikasi.', [
    'valid'             => true,
    'kode'              => $row['kode'],
    'asal'              => $row['asal'],
    'tanggal_terdaftar' => $row['tanggal_terdaftar'],
    'produk'            => $row['nama_produk'],
]);
