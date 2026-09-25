<?php
/* =========================================================
   API TESTIMONI
   GET              → testimoni yang sudah disetujui (publik)
   GET ?semua=1     → semua testimoni (admin)
   POST             → kirim ulasan baru, menunggu persetujuan (publik)
   PUT ?id=         → tampilkan / sembunyikan (admin)
   DELETE ?id=      → hapus (admin)
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

const MAKS_TESTIMONI_PUBLIK = 9;

function format_testimoni(array $row, bool $admin = false): array
{
    $data = [
        'id'         => (int) $row['id'],
        'nama'       => $row['nama'],
        'pesan'      => $row['pesan'],
        'rating'     => (float) $row['rating'],
        'created_at' => $row['created_at'],
    ];
    if ($admin) {
        $data['tampil'] = (bool) $row['tampil'];
    }
    return $data;
}

$method = allow_methods('GET', 'POST', 'PUT', 'DELETE');
$pdo = db();

if ($method === 'GET') {
    if (isset($_GET['semua'])) {
        require_admin();
        $rows = $pdo->query('SELECT * FROM testimoni ORDER BY created_at DESC, id DESC')->fetchAll();
        ok('Data testimoni berhasil dimuat.', array_map(fn($r) => format_testimoni($r, true), $rows));
    }

    $stmt = $pdo->prepare(
        'SELECT * FROM (SELECT * FROM testimoni WHERE tampil = 1 ORDER BY id DESC LIMIT ?) t ORDER BY id'
    );
    $stmt->bindValue(1, MAKS_TESTIMONI_PUBLIK, PDO::PARAM_INT);
    $stmt->execute();
    ok('Data testimoni berhasil dimuat.', array_map('format_testimoni', $stmt->fetchAll()));
}

if ($method === 'POST') {
    $v = new Validator(read_json());
    $nama  = $v->string('nama', 'Nama', 100, true, 2);
    $pesan = $v->string('pesan', 'Ulasan', 1000, true, 5);
    $v->check();

    $pdo->prepare('INSERT INTO testimoni (nama, pesan) VALUES (?, ?)')->execute([$nama, $pesan]);
    ok('Terima kasih! Ulasan Anda akan tampil setelah ditinjau admin.', ['id' => (int) $pdo->lastInsertId()], 201);
}

require_admin();

if ($method === 'PUT') {
    $id = query_id();
    $v = new Validator(read_json());
    $tampil = $v->bool('tampil', 'Status tampil');
    $v->check();

    $exists = $pdo->prepare('SELECT id FROM testimoni WHERE id = ?');
    $exists->execute([$id]);
    if (!$exists->fetch()) {
        fail('Testimoni tidak ditemukan.', 404);
    }

    $pdo->prepare('UPDATE testimoni SET tampil = ? WHERE id = ?')->execute([$tampil ? 1 : 0, $id]);
    ok($tampil ? 'Testimoni ditampilkan di beranda.' : 'Testimoni disembunyikan.', ['id' => $id, 'tampil' => $tampil]);
}

if ($method === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM testimoni WHERE id = ?');
    $stmt->execute([query_id()]);
    if ($stmt->rowCount() === 0) {
        fail('Testimoni tidak ditemukan.', 404);
    }
    ok('Testimoni berhasil dihapus.');
}
