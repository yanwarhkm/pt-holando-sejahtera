<?php
/* =========================================================
   API PRODUK (katalog bibit kentang)
   GET              → daftar bibit aktif (publik)
   GET ?id=         → detail satu bibit (publik)
   GET ?semua=1     → semua bibit termasuk nonaktif (admin)
   POST             → tambah bibit (admin)
   PUT ?id=         → ubah bibit (admin)
   DELETE ?id=      → hapus bibit (admin)
   Jenis bibit (kategori) berasal dari tabel kategori_bibit → field kategori_id;
   response juga menyertakan "kategori" = nama jenis bibit.
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

// Nama jenis bibit ikut dibaca agar frontend tidak perlu request tambahan
const SELECT_PRODUK = 'SELECT p.*, k.nama AS kategori_nama
    FROM produk p LEFT JOIN kategori_bibit k ON k.id = p.kategori_id';

function format_produk(array $row, bool $admin = false): array
{
    $produk = [
        'id'        => (int) $row['id'],
        'nama'      => $row['nama'],
        'kategori_id' => $row['kategori_id'] === null ? null : (int) $row['kategori_id'],
        'kategori'  => $row['kategori_nama'], // nama jenis bibit, null jika belum ditentukan
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

/**
 * @param int|null $kategoriLama jenis bibit saat ini (edit) — boleh dipertahankan
 *                               walaupun jenis tersebut sudah dinonaktifkan.
 */
function validate_produk(PDO $pdo, array $input, ?int $kategoriLama = null): array
{
    $v = new Validator($input);
    $data = [
        'nama'      => $v->string('nama', 'Nama bibit', 100, true, 3),
        'kategori_id' => $v->int('kategori_id', 'Jenis bibit', 1, PHP_INT_MAX),
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
    if ($data['kategori_id'] !== null) {
        $stmt = $pdo->prepare('SELECT aktif FROM kategori_bibit WHERE id = ?');
        $stmt->execute([$data['kategori_id']]);
        $aktif = $stmt->fetchColumn();
        if ($aktif === false) {
            $v->addError('kategori_id', 'Jenis bibit tidak ditemukan.');
        } elseif ((int) $aktif !== 1 && $data['kategori_id'] !== $kategoriLama) {
            $v->addError('kategori_id', 'Jenis bibit tersebut sedang nonaktif.');
        }
    }
    $v->check();
    $data['aktif'] = $data['aktif'] ? 1 : 0;
    return $data;
}

$method = allow_methods('GET', 'POST', 'PUT', 'DELETE');
$pdo = db();

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare(SELECT_PRODUK . ' WHERE p.id = ? AND p.aktif = 1');
        $stmt->execute([query_id()]);
        $row = $stmt->fetch();
        if (!$row) {
            fail('Bibit tidak ditemukan.', 404);
        }
        ok('Detail bibit berhasil dimuat.', format_produk($row));
    }

    if (isset($_GET['semua'])) {
        require_admin();
        $rows = $pdo->query(SELECT_PRODUK . ' ORDER BY p.id')->fetchAll();
        ok('Data bibit berhasil dimuat.', array_map(fn($r) => format_produk($r, true), $rows));
    }

    $rows = $pdo->query(SELECT_PRODUK . ' WHERE p.aktif = 1 ORDER BY p.id')->fetchAll();
    ok('Data bibit berhasil dimuat.', array_map('format_produk', $rows));
}

require_admin();

if ($method === 'POST') {
    $data = validate_produk($pdo, read_json());
    $stmt = $pdo->prepare(
        'INSERT INTO produk (nama, kategori_id, deskripsi, harga, satuan, gambar, badge, stok, aktif)
         VALUES (:nama, :kategori_id, :deskripsi, :harga, :satuan, :gambar, :badge, :stok, :aktif)'
    );
    $stmt->execute($data);
    $id = (int) $pdo->lastInsertId();

    $row = $pdo->prepare(SELECT_PRODUK . ' WHERE p.id = ?');
    $row->execute([$id]);
    ok('Bibit berhasil ditambahkan.', format_produk($row->fetch(), true), 201);
}

if ($method === 'PUT') {
    $id = query_id();
    $exists = $pdo->prepare('SELECT kategori_id FROM produk WHERE id = ?');
    $exists->execute([$id]);
    $lama = $exists->fetch();
    if (!$lama) {
        fail('Bibit tidak ditemukan.', 404);
    }
    $data = validate_produk($pdo, read_json(), $lama['kategori_id'] === null ? null : (int) $lama['kategori_id']);

    $stmt = $pdo->prepare(
        'UPDATE produk SET nama = :nama, kategori_id = :kategori_id, deskripsi = :deskripsi, harga = :harga,
                satuan = :satuan, gambar = :gambar, badge = :badge, stok = :stok, aktif = :aktif
         WHERE id = :id'
    );
    $stmt->execute($data + ['id' => $id]);

    $row = $pdo->prepare(SELECT_PRODUK . ' WHERE p.id = ?');
    $row->execute([$id]);
    ok('Bibit berhasil diperbarui.', format_produk($row->fetch(), true));
}

if ($method === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM produk WHERE id = ?');
    $stmt->execute([query_id()]);
    if ($stmt->rowCount() === 0) {
        fail('Bibit tidak ditemukan.', 404);
    }
    ok('Bibit berhasil dihapus.');
}
