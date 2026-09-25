<?php
/* =========================================================
   API PRODUK
   GET              → daftar produk aktif (publik)
   GET ?id=         → detail satu produk (publik)
   GET ?semua=1     → semua produk termasuk nonaktif (admin)
   POST             → tambah produk (admin)
   PUT ?id=         → ubah produk (admin)
   DELETE ?id=      → hapus produk (admin)
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

const KATEGORI_PRODUK = ['Sayuran', 'Buah', 'Umbi'];

function format_produk(array $row, bool $admin = false): array
{
    $produk = [
        'id'        => (int) $row['id'],
        'nama'      => $row['nama'],
        'kategori'  => $row['kategori'],
        'deskripsi' => $row['deskripsi'],
        'harga'     => (int) $row['harga'],
        'satuan'    => $row['satuan'],
        'gambar'    => $row['gambar'],
        'badge'     => $row['badge'],
        'stok'      => (int) $row['stok'],
    ];
    if ($admin) {
        $produk['aktif'] = (bool) $row['aktif'];
    }
    return $produk;
}

function validate_produk(array $input): array
{
    $v = new Validator($input);
    $data = [
        'nama'      => $v->string('nama', 'Nama produk', 100, true, 3),
        'kategori'  => $v->in('kategori', 'Kategori', KATEGORI_PRODUK),
        'deskripsi' => $v->string('deskripsi', 'Deskripsi', 255, false) ?? '',
        'harga'     => $v->int('harga', 'Harga', 1, 100000000),
        'satuan'    => $v->string('satuan', 'Satuan', 20),
        'gambar'    => $v->string('gambar', 'Gambar', 255, false) ?? '',
        'badge'     => $v->string('badge', 'Label', 30, false),
        'stok'      => $v->int('stok', 'Stok', 0, 10000000, false) ?? 0,
        'aktif'     => array_key_exists('aktif', $input) ? $v->bool('aktif', 'Status aktif') : true,
    ];
    // Hanya nama file gambar lokal — mencegah URL javascript:/eksternal
    if ($data['gambar'] !== '' && (
        str_contains($data['gambar'], '..') ||
        !preg_match('/^[A-Za-z0-9 _().\-\/]+\.(jpe?g|png|webp|gif)$/i', $data['gambar'])
    )) {
        $v->addError('gambar', 'Gambar harus berupa nama file gambar lokal (jpg, png, webp, gif).');
    }
    $v->check();
    $data['aktif'] = $data['aktif'] ? 1 : 0;
    return $data;
}

$method = allow_methods('GET', 'POST', 'PUT', 'DELETE');
$pdo = db();

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare('SELECT * FROM produk WHERE id = ? AND aktif = 1');
        $stmt->execute([query_id()]);
        $row = $stmt->fetch();
        if (!$row) {
            fail('Produk tidak ditemukan.', 404);
        }
        ok('Detail produk berhasil dimuat.', format_produk($row));
    }

    if (isset($_GET['semua'])) {
        require_admin();
        $rows = $pdo->query('SELECT * FROM produk ORDER BY id')->fetchAll();
        ok('Data produk berhasil dimuat.', array_map(fn($r) => format_produk($r, true), $rows));
    }

    $rows = $pdo->query('SELECT * FROM produk WHERE aktif = 1 ORDER BY id')->fetchAll();
    ok('Data produk berhasil dimuat.', array_map('format_produk', $rows));
}

require_admin();

if ($method === 'POST') {
    $data = validate_produk(read_json());
    $stmt = $pdo->prepare(
        'INSERT INTO produk (nama, kategori, deskripsi, harga, satuan, gambar, badge, stok, aktif)
         VALUES (:nama, :kategori, :deskripsi, :harga, :satuan, :gambar, :badge, :stok, :aktif)'
    );
    $stmt->execute($data);
    $id = (int) $pdo->lastInsertId();

    $row = $pdo->prepare('SELECT * FROM produk WHERE id = ?');
    $row->execute([$id]);
    ok('Produk berhasil ditambahkan.', format_produk($row->fetch(), true), 201);
}

if ($method === 'PUT') {
    $id = query_id();
    $data = validate_produk(read_json());

    $exists = $pdo->prepare('SELECT id FROM produk WHERE id = ?');
    $exists->execute([$id]);
    if (!$exists->fetch()) {
        fail('Produk tidak ditemukan.', 404);
    }

    $stmt = $pdo->prepare(
        'UPDATE produk SET nama = :nama, kategori = :kategori, deskripsi = :deskripsi, harga = :harga,
                satuan = :satuan, gambar = :gambar, badge = :badge, stok = :stok, aktif = :aktif
         WHERE id = :id'
    );
    $stmt->execute($data + ['id' => $id]);

    $row = $pdo->prepare('SELECT * FROM produk WHERE id = ?');
    $row->execute([$id]);
    ok('Produk berhasil diperbarui.', format_produk($row->fetch(), true));
}

if ($method === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM produk WHERE id = ?');
    $stmt->execute([query_id()]);
    if ($stmt->rowCount() === 0) {
        fail('Produk tidak ditemukan.', 404);
    }
    ok('Produk berhasil dihapus.');
}
