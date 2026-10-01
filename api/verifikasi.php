<?php
/* =========================================================
   API VERIFIKASI KODE / URL QR RESMI PADA LABEL BIBIT

   PUBLIK
   GET ?kode=BPSB-001234 → cocokkan kode/nomor seri dengan database internal.
   GET ?url=https://benih.pertanian.go.id/...
                         → cari data internal yang terhubung dengan URL QR
                           resmi (pencocokan persis, URL tidak diubah).
   Keduanya mengembalikan status: 'aktif' | 'dicabut' | 'tidak_ditemukan'
   dan dicatat ke log_verifikasi.
   Ini BUKAN verifikasi resmi BPSB/Kementan — hanya data internal. Isi halaman
   resmi tidak diambil/di-proxy; pengguna membukanya sendiri di situs resmi.

   ADMIN (butuh sesi admin)
   GET ?semua=1          → daftar seluruh data verifikasi
   POST                  → tambah verifikasi
   PUT ?id=              → ubah verifikasi
   DELETE ?id=           → hapus verifikasi

   Kode disimpan huruf besar & unik (UNIQUE KEY uq_kode_produk) agar hasil
   scan dapat dicocokkan konsisten dan tidak ada duplikat.
   URL resmi disimpan apa adanya (tanpa ubah huruf) dan TIDAK unik.
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

const SELECT_KODE = 'SELECT k.id, k.kode, k.url_resmi, k.produk_id, k.asal, k.tanggal_terdaftar, k.status, k.created_at,
        p.nama AS nama_produk, kb.nama AS kategori_nama
    FROM kode_produk k
    LEFT JOIN produk p ON p.id = k.produk_id
    LEFT JOIN kategori_bibit kb ON kb.id = p.kategori_id';

const POLA_KODE = '/^[A-Z0-9][A-Z0-9\-\/]{1,49}$/';

/** Host tunggal yang diakui sebagai sumber verifikasi resmi. */
const HOST_URL_RESMI = 'benih.pertanian.go.id';

/**
 * URL QR resmi: hanya https:// dengan host TEPAT benih.pertanian.go.id,
 * tanpa userinfo/port, hanya karakter URL ASCII yang sah (tanpa spasi, "\",
 * tanda kutip, <>). Tidak mengubah URL — hanya menilai.
 */
function url_resmi_valid(string $url): bool
{
    if (strlen($url) > 255) {
        return false;
    }
    if (!preg_match('~^https://benih\.pertanian\.go\.id(?:[/?#][A-Za-z0-9\-._\~:/?#\[\]@!$&\'()*+,;=%]*)?$~i', $url)) {
        return false;
    }
    $p = parse_url($url);
    return is_array($p)
        && strtolower($p['scheme'] ?? '') === 'https'
        && strtolower($p['host'] ?? '') === HOST_URL_RESMI
        && !isset($p['user']) && !isset($p['pass']) && !isset($p['port']);
}

/** Bentuk data untuk admin (dashboard). */
function format_kode(array $r): array
{
    return [
        'id'                => (int) $r['id'],
        'kode'              => $r['kode'],
        'url_resmi'         => $r['url_resmi'],
        'produk_id'         => $r['produk_id'] === null ? null : (int) $r['produk_id'],
        'produk'            => $r['nama_produk'],   // nama bibit terkait (opsional)
        'kategori'          => $r['kategori_nama'], // jenis/varietas bibit (opsional)
        'asal'              => $r['asal'],
        'tanggal_terdaftar' => $r['tanggal_terdaftar'],
        'status'            => $r['status'],
    ];
}

/** Hanya field yang aman ditampilkan ke publik (tanpa id/status mentah). */
function format_publik(array $r): array
{
    return [
        'kode'              => $r['kode'],
        'url_resmi'         => $r['url_resmi'],
        'produk'            => $r['nama_produk'],
        'kategori'          => $r['kategori_nama'],
        'asal'              => $r['asal'],
        'tanggal_terdaftar' => $r['tanggal_terdaftar'],
    ];
}

/** Validasi & normalisasi input admin. Tidak mempercayai data frontend. */
function validate_kode(PDO $pdo, array $input): array
{
    $v = new Validator($input);

    // Kode: opsional, dinormalisasi huruf besar (bukan URL)
    $kodeRaw = $input['kode'] ?? null;
    $kode = null;
    $kodeKosong = $kodeRaw === null || (is_string($kodeRaw) && trim($kodeRaw) === '');
    if ($kodeRaw !== null && !is_string($kodeRaw)) {
        $v->addError('kode', 'Kode / nomor seri tidak valid.');
    } elseif (!$kodeKosong) {
        $kode = strtoupper(trim($kodeRaw));
        if (!preg_match(POLA_KODE, $kode)) {
            $v->addError('kode', 'Format kode tidak valid. Gunakan huruf, angka, "-" atau "/". Contoh: BPSB-001234.');
        }
    }

    // URL resmi: opsional, disimpan APA ADANYA (hanya spasi di tepi yang dibuang)
    $urlRaw = $input['url_resmi'] ?? null;
    $url = null;
    $urlKosong = $urlRaw === null || (is_string($urlRaw) && trim($urlRaw) === '');
    if ($urlRaw !== null && !is_string($urlRaw)) {
        $v->addError('url_resmi', 'URL QR resmi tidak valid.');
    } elseif (!$urlKosong) {
        $url = trim($urlRaw);
        if (!url_resmi_valid($url)) {
            $v->addError('url_resmi', 'URL QR resmi harus diawali https://' . HOST_URL_RESMI . '/ (maksimal 255 karakter).');
        }
    }

    if ($kodeKosong && $urlKosong) {
        $v->addError('kode', 'Isi Kode / Nomor Seri atau URL QR resmi (minimal salah satu).');
    }

    $produkId = $v->int('produk_id', 'Benih terkait', 1, PHP_INT_MAX, false);
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
            $v->addError('produk_id', 'Benih terkait tidak ditemukan.');
        }
    }

    $v->check();

    return [
        'kode'              => $kode,
        'url_resmi'         => $url,
        'produk_id'         => $produkId,
        'asal'              => $asal,
        'tanggal_terdaftar' => $tgl,
        'status'            => $status,
    ];
}

/** Ambil parameter query string tunggal (array/selain string dianggap kosong). */
function param_teks(string $name): string
{
    $value = $_GET[$name] ?? '';
    return is_string($value) ? trim($value) : '';
}

function catat_log(PDO $pdo, string $dicek, bool $valid, ?int $kodeProdukId): void
{
    $pdo->prepare('INSERT INTO log_verifikasi (kode, valid, kode_produk_id) VALUES (?, ?, ?)')
        ->execute([$dicek, $valid ? 1 : 0, $kodeProdukId]);
}

$method = allow_methods('GET', 'POST', 'PUT', 'DELETE');
$pdo = db();

if ($method === 'GET') {
    // ---- ADMIN: daftar semua data verifikasi ----
    if (isset($_GET['semua'])) {
        require_admin();
        $rows = $pdo->query(SELECT_KODE . ' ORDER BY k.id DESC')->fetchAll();
        ok('Data verifikasi berhasil dimuat.', array_map('format_kode', $rows));
    }

    // ---- PUBLIK: data internal yang terhubung dengan URL QR resmi ----
    if (isset($_GET['url'])) {
        $url = param_teks('url');
        if ($url === '') {
            fail('URL wajib diisi.', 422);
        }
        if (!url_resmi_valid($url)) {
            fail('URL bukan alamat verifikasi resmi yang dikenali.', 422);
        }

        $stmt = $pdo->prepare(SELECT_KODE . " WHERE k.url_resmi = ? ORDER BY k.status = 'aktif' DESC, k.id");
        $stmt->execute([$url]);
        $rows = $stmt->fetchAll();
        $aktif = array_values(array_filter($rows, fn($r) => $r['status'] === 'aktif'));
        $status = $aktif ? 'aktif' : ($rows ? 'dicabut' : 'tidak_ditemukan');

        catat_log($pdo, $url, $status === 'aktif', $rows ? (int) $rows[0]['id'] : null);

        ok([
            'aktif'           => 'Data internal terhubung ditemukan.',
            'dicabut'         => 'Data internal terhubung sudah dicabut.',
            'tidak_ditemukan' => 'Belum ada data internal yang terhubung.',
        ][$status], [
            'status'  => $status,
            'valid'   => $status === 'aktif',
            'url'     => $url,
            'terkait' => array_map('format_publik', $aktif),
        ]);
    }

    // ---- PUBLIK: lookup kode / nomor seri ----
    $kode = strtoupper(param_teks('kode'));
    if ($kode === '') {
        fail('Kode produk wajib diisi.', 422);
    }
    if (!preg_match(POLA_KODE, $kode)) {
        fail('Format kode produk tidak valid.', 422);
    }

    $stmt = $pdo->prepare(SELECT_KODE . ' WHERE k.kode = ?');
    $stmt->execute([$kode]);
    $row = $stmt->fetch();
    $status = $row === false ? 'tidak_ditemukan' : $row['status'];

    // Catat setiap pengecekan (dicabut = valid 0 tetapi kode_produk_id terisi)
    catat_log($pdo, $kode, $status === 'aktif', $row ? (int) $row['id'] : null);

    if ($status === 'tidak_ditemukan') {
        ok('Data verifikasi tidak ditemukan.', ['status' => $status, 'valid' => false, 'kode' => $kode]);
    }
    if ($status === 'dicabut') {
        ok('Kode terdaftar tetapi sudah dicabut.', ['status' => $status, 'valid' => false, 'kode' => $row['kode']]);
    }
    ok('Data verifikasi ditemukan.', ['status' => $status, 'valid' => true] + format_publik($row));
}

/* ---- Mulai sini: hanya admin ---- */
require_admin();

if ($method === 'POST') {
    $data = validate_kode($pdo, read_json());
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO kode_produk (kode, url_resmi, produk_id, asal, tanggal_terdaftar, status)
             VALUES (:kode, :url_resmi, :produk_id, :asal, :tanggal_terdaftar, :status)'
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
            'UPDATE kode_produk SET kode = :kode, url_resmi = :url_resmi, produk_id = :produk_id, asal = :asal,
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
