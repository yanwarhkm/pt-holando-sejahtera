<?php
/* =========================================================
   API PESAN KONTAK
   POST → simpan pesan dari form kontak (publik)
   GET  → daftar pesan masuk (admin)
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

$method = allow_methods('GET', 'POST');
$pdo = db();

if ($method === 'POST') {
    $v = new Validator(read_json());
    $nama  = $v->string('nama', 'Nama lengkap', 100, true, 3);
    $wa    = $v->phone('wa');
    $pesan = $v->string('pesan', 'Pesan', 1000, true, 5);
    $v->check();

    $pdo->prepare('INSERT INTO pesan_kontak (nama, no_wa, pesan) VALUES (?, ?, ?)')->execute([$nama, $wa, $pesan]);
    ok('Pesan berhasil disimpan.', ['id' => (int) $pdo->lastInsertId()], 201);
}

require_admin();
$rows = $pdo->query('SELECT id, nama, no_wa AS wa, pesan, created_at FROM pesan_kontak ORDER BY id DESC LIMIT 500')->fetchAll();
ok('Data pesan berhasil dimuat.', array_map(fn($r) => ['id' => (int) $r['id']] + $r, $rows));
