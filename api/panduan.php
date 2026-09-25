<?php
/* =========================================================
   API RIWAYAT AKSES PANDUAN (admin)
   GET → daftar verifikasi pesanan yang membuka panduan

   Pencatatan dilakukan server-side oleh api/akses_panduan.php saat
   verifikasi pesanan berhasil; endpoint ini tidak menerima tulisan publik.
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

allow_methods('GET');
require_admin();

$rows = db()->query(
    'SELECT a.id, a.sumber, a.created_at, p.kode_pesanan
     FROM akses_panduan a
     LEFT JOIN pesanan p ON p.id = a.pesanan_id
     ORDER BY a.id DESC LIMIT 500'
)->fetchAll();
ok('Data akses panduan berhasil dimuat.', array_map(fn($r) => [
    'id'           => (int) $r['id'],
    'sumber'       => $r['sumber'],
    'kode_pesanan' => $r['kode_pesanan'],
    'created_at'   => $r['created_at'],
], $rows));
