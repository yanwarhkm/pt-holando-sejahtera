<?php
/* =========================================================
   API TESTIMONI
   GET              → testimoni yang ditampilkan, terbaru dulu (publik)
   GET ?sebelum=ID  → halaman berikutnya, lebih lama dari testimoni ID (publik)
   GET ?semua=1     → semua testimoni (admin)
   POST             → kirim ulasan + rating 1–5, langsung tampil (publik)
   PUT ?id=         → tampilkan / sembunyikan (admin)
   DELETE ?id=      → hapus (admin)
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

const MAKS_TESTIMONI_PUBLIK = 9; // per halaman

function format_testimoni(array $row, bool $admin = false): array
{
    $data = [
        'id'         => (int) $row['id'],
        'nama'       => $row['nama'],
        'pesan'      => $row['pesan'],
        // NULL = ulasan lama tanpa rating; jangan diubah jadi angka
        'rating'     => $row['rating'] === null ? null : (float) $row['rating'],
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

    // Terbaru dulu. Halaman berikutnya memakai kursor (created_at, id) dari testimoni
    // terakhir yang sudah tampil, agar ulasan baru tidak membuat data bergeser/dobel.
    $sebelum = isset($_GET['sebelum']) ? query_id('sebelum') : null;
    $sql = 'SELECT id, nama, pesan, rating, created_at FROM testimoni WHERE tampil = 1';
    if ($sebelum !== null) {
        $sql .= ' AND (created_at, id) < (SELECT created_at, id FROM testimoni WHERE id = :sebelum)';
    }
    $stmt = $pdo->prepare($sql . ' ORDER BY created_at DESC, id DESC LIMIT :batas');
    if ($sebelum !== null) {
        $stmt->bindValue(':sebelum', $sebelum, PDO::PARAM_INT);
    }
    $stmt->bindValue(':batas', MAKS_TESTIMONI_PUBLIK, PDO::PARAM_INT);
    $stmt->execute();
    ok('Data testimoni berhasil dimuat.', array_map('format_testimoni', $stmt->fetchAll()));
}

if ($method === 'POST') {
    $input = read_json();
    $v = new Validator($input);
    $nama  = $v->string('nama', 'Nama', 100, true, 2);
    $pesan = $v->string('pesan', 'Ulasan', 1000, true, 5);
    // Rating wajib bilangan bulat JSON 1–5. String ("5"), desimal (4.5 / 5.0),
    // boolean, dan kosong ditolak — tidak dikonversi diam-diam.
    $rating = $input['rating'] ?? null;
    if ($rating === null || $rating === '') {
        $v->addError('rating', 'Silakan pilih rating terlebih dahulu.');
    } elseif (!is_int($rating) || $rating < 1 || $rating > 5) {
        $v->addError('rating', 'Rating harus berupa angka bulat 1 sampai 5.');
    }
    $v->check();

    // Ulasan langsung tampil untuk semua pengunjung; admin tetap bisa menyembunyikan/menghapus
    $pdo->prepare('INSERT INTO testimoni (nama, pesan, rating, tampil) VALUES (?, ?, ?, 1)')
        ->execute([$nama, $pesan, $rating]);
    ok('Terima kasih! Ulasan Anda sudah tampil.', ['id' => (int) $pdo->lastInsertId()], 201);
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
