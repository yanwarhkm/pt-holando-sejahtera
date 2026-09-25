<?php
/* =========================================================
   API MITRA PEMBUDIDAYA
   POST         → daftar sebagai mitra (publik)
   GET          → daftar mitra (admin)
   DELETE ?id=  → hapus mitra (admin)
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

$method = allow_methods('GET', 'POST', 'DELETE');
$pdo = db();

if ($method === 'POST') {
    $v = new Validator(read_json());
    $nama   = $v->string('nama', 'Nama', 100, true, 3);
    $wa     = $v->phone('wa');
    $lokasi = $v->string('lokasi', 'Lokasi', 150, true, 3);
    $lahan  = $v->number('lahan', 'Luas lahan', 0, 100000000);
    $v->check();

    $pdo->prepare('INSERT INTO mitra (nama, no_wa, lokasi, luas_lahan) VALUES (?, ?, ?, ?)')
        ->execute([$nama, $wa, $lokasi, $lahan]);
    ok('Pendaftaran mitra berhasil.', ['id' => (int) $pdo->lastInsertId()], 201);
}

require_admin();

if ($method === 'GET') {
    $rows = $pdo->query('SELECT id, nama, no_wa, lokasi, luas_lahan, created_at FROM mitra ORDER BY id DESC')->fetchAll();
    ok('Data mitra berhasil dimuat.', array_map(fn($r) => [
        'id'         => (int) $r['id'],
        'nama'       => $r['nama'],
        'wa'         => $r['no_wa'],
        'lokasi'     => $r['lokasi'],
        'lahan'      => (float) $r['luas_lahan'],
        'created_at' => $r['created_at'],
    ], $rows));
}

if ($method === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM mitra WHERE id = ?');
    $stmt->execute([query_id()]);
    if ($stmt->rowCount() === 0) {
        fail('Mitra tidak ditemukan.', 404);
    }
    ok('Mitra berhasil dihapus.');
}
