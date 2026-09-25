<?php
/* =========================================================
   API JENIS BIBIT (tabel kategori_bibit)
   Kategori katalog = jenis/varietas bibit kentang (mis. Granola).
   GET              → jenis bibit aktif (publik, untuk filter katalog)
   GET ?semua=1     → semua jenis + jumlah bibit (admin)
   POST             → tambah jenis { nama } (admin)
   PUT ?id=         → ubah jenis { nama, aktif } (admin)
   Tidak ada DELETE: jenis yang tidak dipakai cukup dinonaktifkan agar
   bibit yang sudah memakainya tetap utuh.
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

function format_kategori(array $row, bool $admin = false): array
{
    $data = ['id' => (int) $row['id'], 'nama' => $row['nama']];
    if ($admin) {
        $data['aktif'] = (bool) $row['aktif'];
        $data['jumlah_bibit'] = (int) $row['jumlah_bibit'];
    }
    return $data;
}

/** Nama jenis bibit; duplikat dicek oleh UNIQUE KEY (tidak peka huruf besar/kecil). */
function validate_kategori(array $input, bool $denganAktif): array
{
    $v = new Validator($input);
    $data = ['nama' => $v->string('nama', 'Nama jenis bibit', 50, true, 2)];
    if ($denganAktif) {
        $data['aktif'] = $v->bool('aktif', 'Status aktif');
    }
    $v->check();
    return $data;
}

function simpan_kategori(PDOStatement $stmt, array $params): void
{
    try {
        $stmt->execute($params);
    } catch (PDOException $e) {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            fail('Jenis bibit dengan nama tersebut sudah ada.', 409, ['nama' => 'Nama jenis bibit sudah dipakai.']);
        }
        throw $e;
    }
}

$method = allow_methods('GET', 'POST', 'PUT');
$pdo = db();

if ($method === 'GET') {
    if (isset($_GET['semua'])) {
        require_admin();
        $rows = $pdo->query(
            'SELECT k.id, k.nama, k.aktif, COUNT(p.id) AS jumlah_bibit
             FROM kategori_bibit k LEFT JOIN produk p ON p.kategori_id = k.id
             GROUP BY k.id, k.nama, k.aktif ORDER BY k.nama'
        )->fetchAll();
        ok('Data jenis bibit berhasil dimuat.', array_map(fn($r) => format_kategori($r, true), $rows));
    }
    $rows = $pdo->query('SELECT id, nama FROM kategori_bibit WHERE aktif = 1 ORDER BY nama')->fetchAll();
    ok('Data jenis bibit berhasil dimuat.', array_map('format_kategori', $rows));
}

require_admin();

if ($method === 'POST') {
    $data = validate_kategori(read_json(), false);
    simpan_kategori($pdo->prepare('INSERT INTO kategori_bibit (nama) VALUES (?)'), [$data['nama']]);
    ok('Jenis bibit berhasil ditambahkan.', ['id' => (int) $pdo->lastInsertId(), 'nama' => $data['nama'], 'aktif' => true], 201);
}

if ($method === 'PUT') {
    $id = query_id();
    $data = validate_kategori(read_json(), true);

    $exists = $pdo->prepare('SELECT id FROM kategori_bibit WHERE id = ?');
    $exists->execute([$id]);
    if (!$exists->fetch()) {
        fail('Jenis bibit tidak ditemukan.', 404);
    }

    simpan_kategori(
        $pdo->prepare('UPDATE kategori_bibit SET nama = ?, aktif = ? WHERE id = ?'),
        [$data['nama'], $data['aktif'] ? 1 : 0, $id]
    );
    ok($data['aktif'] ? 'Jenis bibit berhasil diperbarui.' : 'Jenis bibit dinonaktifkan.', ['id' => $id] + $data);
}
