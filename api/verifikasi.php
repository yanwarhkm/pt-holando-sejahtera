<?php
/* =========================================================
   API VERIFIKASI KODE PRODUK / BIBIT

   PUBLIK
   GET ?kode=BPSB-001234 → cocokkan kode hasil scan dengan database
                           internal, catat ke log_verifikasi.
                           (Bukan verifikasi BPSB resmi — hanya data internal.)

   ADMIN (butuh sesi admin)
   GET ?semua=1          → daftar seluruh kode verifikasi
   POST                  → tambah verifikasi
   PUT ?id=              → ubah verifikasi
   DELETE ?id=           → hapus verifikasi

   Kode disimpan huruf besar & unik (UNIQUE KEY uq_kode_produk) agar hasil
   scan dapat dicocokkan konsisten dan tidak ada duplikat.
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

const SELECT_KODE = 'SELECT k.id, k.kode, k.produk_id, k.asal, k.tanggal_terdaftar, k.status, k.created_at,
        p.nama AS nama_produk, kb.nama AS kategori_nama
    FROM kode_produk k
    LEFT JOIN produk p ON p.id = k.produk_id
    LEFT JOIN kategori_bibit kb ON kb.id = p.kategori_id';

/** Bentuk data untuk admin (dashboard). */
function format_kode(array $r): array
{
    return [
        'id'                => (int) $r['id'],
        'kode'              => $r['kode'],
        'produk_id'         => $r['produk_id'] === null ? null : (int) $r['produk_id'],
        'produk'            => $r['nama_produk'],   // nama bibit terkait (opsional)
        'kategori'          => $r['kategori_nama'], // jenis/varietas bibit (opsional)
        'asal'              => $r['asal'],
        'tanggal_terdaftar' => $r['tanggal_terdaftar'],
        'status'            => $r['status'],
    ];
}

/** Validasi & normalisasi input admin. Tidak mempercayai data frontend. */
function validate_kode(PDO $pdo, array $input): array
{
    $v = new Validator($input);

    $kodeRaw = $input['kode'] ?? '';
    $kode = is_string($kodeRaw) ? strtoupper(trim($kodeRaw)) : '';
    if ($kode === '') {
        $v->addError('kode', 'Kode / nomor seri wajib diisi.');
    } elseif (!preg_match('/^[A-Z0-9][A-Z0-9\-\/]{1,49}$/', $kode)) {
        $v->addError('kode', 'Format kode tidak valid. Gunakan huruf, angka, "-" atau "/". Contoh: BPSB-001234.');
    }

    $produkId = $v->int('produk_id', 'Bibit terkait', 1, PHP_INT_MAX, false);
    $asal     = $v->string('asal', 'Asal', 100, false) ?? '';
    $status   = $v->in('status', 'Status', ['aktif', 'dicabut']);

    $tgl = $input['tanggal_terdaftar'] ?? '';
    if (!is_string($tgl) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tgl, $m)
        || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
        $v->addError('tanggal_terdaftar', 'Tanggal terdaftar tidak valid (format YYYY-MM-DD).');
        $tgl = null;
    }

    if ($produkId !== null) {
        $stmt = $pdo->prepare('SELECT 1 FROM produk WHERE id = ?');
        $stmt->execute([$produkId]);
        if ($stmt->fetchColumn() === false) {
            $v->addError('produk_id', 'Bibit terkait tidak ditemukan.');
        }
    }

    $v->check();

    return [
        'kode'              => $kode,
        'produk_id'         => $produkId,
        'asal'              => $asal,
        'tanggal_terdaftar' => $tgl,
        'status'            => $status,
    ];
}

$method = allow_methods('GET', 'POST', 'PUT', 'DELETE');
$pdo = db();

if ($method === 'GET') {
    // ---- ADMIN: daftar semua kode verifikasi ----
    if (isset($_GET['semua'])) {
        require_admin();
        $rows = $pdo->query(SELECT_KODE . ' ORDER BY k.id DESC')->fetchAll();
        ok('Data verifikasi berhasil dimuat.', array_map('format_kode', $rows));
    }

    // ---- PUBLIK: lookup kode hasil scan ----
    $kode = $_GET['kode'] ?? '';
    $kode = is_string($kode) ? strtoupper(trim($kode)) : '';
    if ($kode === '') {
        fail('Kode produk wajib diisi.', 422);
    }
    if (!preg_match('/^[A-Z0-9][A-Z0-9\-\/]{1,49}$/', $kode)) {
        fail('Format kode produk tidak valid.', 422);
    }

    $stmt = $pdo->prepare(SELECT_KODE . ' WHERE k.kode = ?');
    $stmt->execute([$kode]);
    $row = $stmt->fetch();
    $valid = $row !== false && $row['status'] === 'aktif';

    // Catat setiap pengecekan (membedakan ditemukan / tidak ditemukan)
    $pdo->prepare('INSERT INTO log_verifikasi (kode, valid, kode_produk_id) VALUES (?, ?, ?)')
        ->execute([$kode, $valid ? 1 : 0, $row ? (int) $row['id'] : null]);

    if (!$valid) {
        ok('Data verifikasi tidak ditemukan.', ['valid' => false, 'kode' => $kode]);
    }

    // Hanya kembalikan field yang aman ditampilkan ke publik
    ok('Data verifikasi ditemukan.', [
        'valid'             => true,
        'kode'              => $row['kode'],
        'produk'            => $row['nama_produk'],
        'kategori'          => $row['kategori_nama'],
        'asal'              => $row['asal'],
        'tanggal_terdaftar' => $row['tanggal_terdaftar'],
    ]);
}

/* ---- Mulai sini: hanya admin ---- */
require_admin();

if ($method === 'POST') {
    $data = validate_kode($pdo, read_json());
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO kode_produk (kode, produk_id, asal, tanggal_terdaftar, status)
             VALUES (:kode, :produk_id, :asal, :tanggal_terdaftar, :status)'
        );
        $stmt->execute($data);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            fail('Kode tersebut sudah terdaftar. Gunakan kode/nomor seri lain.', 409);
        }
        throw $e;
    }
    $id = (int) $pdo->lastInsertId();
    $row = $pdo->prepare(SELECT_KODE . ' WHERE k.id = ?');
    $row->execute([$id]);
    ok('Verifikasi berhasil ditambahkan.', format_kode($row->fetch()), 201);
}

if ($method === 'PUT') {
    $id = query_id();
    $cek = $pdo->prepare('SELECT id FROM kode_produk WHERE id = ?');
    $cek->execute([$id]);
    if ($cek->fetchColumn() === false) {
        fail('Data verifikasi tidak ditemukan.', 404);
    }
    $data = validate_kode($pdo, read_json());
    try {
        $stmt = $pdo->prepare(
            'UPDATE kode_produk SET kode = :kode, produk_id = :produk_id, asal = :asal,
                    tanggal_terdaftar = :tanggal_terdaftar, status = :status
             WHERE id = :id'
        );
        $stmt->execute($data + ['id' => $id]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            fail('Kode tersebut sudah terdaftar. Gunakan kode/nomor seri lain.', 409);
        }
        throw $e;
    }
    $row = $pdo->prepare(SELECT_KODE . ' WHERE k.id = ?');
    $row->execute([$id]);
    ok('Verifikasi berhasil diperbarui.', format_kode($row->fetch()));
}

if ($method === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM kode_produk WHERE id = ?');
    $stmt->execute([query_id()]);
    if ($stmt->rowCount() === 0) {
        fail('Data verifikasi tidak ditemukan.', 404);
    }
    ok('Verifikasi berhasil dihapus.');
}
