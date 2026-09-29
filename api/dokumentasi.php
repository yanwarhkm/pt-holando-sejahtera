<?php
/* =========================================================
   API DOKUMENTASI (galeri "Dokumentasi Budidaya & Pembibitan")
   GET              → dokumentasi aktif yang berkas gambarnya ada (publik),
                      terbaru dulu (tanggal DESC, lalu id DESC)
   GET ?semua=1     → semua dokumentasi termasuk nonaktif (admin)
   POST             → tambah dokumentasi (admin)
   PUT ?id=         → ubah dokumentasi (admin)
   DELETE ?id=      → hapus dokumentasi (admin)

   Gambar diunggah lewat api/upload.php (jenis=dokumentasi); kolom gambar
   hanya menyimpan path relatifnya. Berkas lama hanya dihapus bila berkas itu
   hasil upload dokumentasi DAN tidak dipakai data lain (dokumentasi/produk).
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

const SELECT_DOKUMENTASI = 'SELECT id, judul, keterangan, tanggal, gambar, aktif FROM dokumentasi';
const URUTAN_DOKUMENTASI = ' ORDER BY tanggal DESC, id DESC';
// Hanya berkas yang dibuat upload.php untuk dokumentasi yang boleh dihapus server
const POLA_UPLOAD_DOKUMENTASI = '#^uploads/dokumentasi_[A-Za-z0-9_]+\.(jpg|png|webp)$#';

function format_dokumentasi(array $row, bool $admin = false): array
{
    $dok = [
        'id'         => (int) $row['id'],
        'judul'      => $row['judul'],
        'keterangan' => $row['keterangan'],
        'tanggal'    => $row['tanggal'],
        'gambar'     => $row['gambar'],
    ];
    if ($admin) {
        $dok['aktif']      = (bool) $row['aktif'];
        $dok['gambar_ada'] = gambar_ada($row['gambar']);
    }
    return $dok;
}

/** Path gambar menunjuk berkas yang benar-benar ada di dalam root project. */
function gambar_ada(string $gambar): bool
{
    if ($gambar === '' || str_contains($gambar, '..') || str_starts_with($gambar, '/')) {
        return false;
    }
    $root = realpath(dirname(__DIR__));
    $full = realpath(dirname(__DIR__) . '/' . $gambar);
    return $root !== false && $full !== false
        && str_starts_with($full, $root . DIRECTORY_SEPARATOR)
        && is_file($full);
}

function validate_dokumentasi(array $input): array
{
    $v = new Validator($input);
    $data = [
        'judul'      => $v->string('judul', 'Judul', 100, true, 3),
        'keterangan' => $v->string('keterangan', 'Lokasi / keterangan', 100, true, 2),
        'gambar'     => $v->string('gambar', 'Gambar', 255),
        'aktif'      => array_key_exists('aktif', $input) ? $v->bool('aktif', 'Status aktif') : true,
    ];

    $tgl = $input['tanggal'] ?? '';
    if (!is_string($tgl) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tgl, $m)
        || !checkdate((int) $m[2], (int) $m[3], (int) $m[1]) || (int) $m[1] < 2000) {
        $v->addError('tanggal', 'Tanggal dokumentasi tidak valid (format YYYY-MM-DD).');
    } elseif ($tgl > date('Y-m-d')) {
        $v->addError('tanggal', 'Tanggal dokumentasi tidak boleh melewati hari ini.');
    }
    $data['tanggal'] = $tgl;

    // Hanya nama berkas gambar lokal yang benar-benar ada — mencegah URL
    // javascript:/eksternal, path traversal, dan gambar rusak di galeri publik.
    if ($data['gambar'] !== null) {
        if (!preg_match('/^[A-Za-z0-9 _().\-\/]+\.(jpe?g|png|webp)$/i', $data['gambar'])
            || !gambar_ada($data['gambar'])) {
            $v->addError('gambar', 'Gambar tidak ditemukan. Silakan pilih ulang gambar.');
        }
    }

    $v->check();
    $data['aktif'] = $data['aktif'] ? 1 : 0;
    return $data;
}

/** Hapus berkas upload dokumentasi yang sudah tidak dipakai data mana pun. */
function hapus_gambar_tak_terpakai(PDO $pdo, string $gambar): void
{
    if (!preg_match(POLA_UPLOAD_DOKUMENTASI, $gambar)) {
        return; // berkas lama/manual (mis. di root project) tidak pernah dihapus
    }
    $stmt = $pdo->prepare(
        'SELECT (SELECT COUNT(*) FROM dokumentasi WHERE gambar = :g1)
              + (SELECT COUNT(*) FROM produk WHERE gambar = :g2)'
    );
    $stmt->execute(['g1' => $gambar, 'g2' => $gambar]);
    if ((int) $stmt->fetchColumn() > 0 || !gambar_ada($gambar)) {
        return;
    }
    try {
        unlink(dirname(__DIR__) . '/' . $gambar);
    } catch (Throwable $e) {
        // Data sudah tersimpan; berkas yatim cukup dicatat, jangan gagalkan request
        error_log('[Dokumentasi] Gagal menghapus berkas ' . $gambar . ': ' . $e->getMessage());
    }
}

function ambil_dokumentasi(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(SELECT_DOKUMENTASI . ' WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

$method = allow_methods('GET', 'POST', 'PUT', 'DELETE');
$pdo = db();

if ($method === 'GET') {
    if (isset($_GET['semua'])) {
        require_admin();
        $rows = $pdo->query(SELECT_DOKUMENTASI . URUTAN_DOKUMENTASI)->fetchAll();
        ok('Data dokumentasi berhasil dimuat.', array_map(fn($r) => format_dokumentasi($r, true), $rows));
    }

    // Publik: hanya yang aktif dan berkas gambarnya tersedia (tidak ada gambar rusak)
    $rows = $pdo->query(SELECT_DOKUMENTASI . ' WHERE aktif = 1' . URUTAN_DOKUMENTASI)->fetchAll();
    $rows = array_values(array_filter($rows, fn($r) => gambar_ada($r['gambar'])));
    ok('Data dokumentasi berhasil dimuat.', array_map('format_dokumentasi', $rows));
}

require_admin();

if ($method === 'POST') {
    $data = validate_dokumentasi(read_json());
    $stmt = $pdo->prepare(
        'INSERT INTO dokumentasi (judul, keterangan, tanggal, gambar, aktif)
         VALUES (:judul, :keterangan, :tanggal, :gambar, :aktif)'
    );
    $stmt->execute($data);
    $row = ambil_dokumentasi($pdo, (int) $pdo->lastInsertId());
    ok('Dokumentasi berhasil ditambahkan.', format_dokumentasi($row, true), 201);
}

if ($method === 'PUT') {
    $id = query_id();
    $lama = ambil_dokumentasi($pdo, $id);
    if ($lama === null) {
        fail('Dokumentasi tidak ditemukan.', 404);
    }
    $data = validate_dokumentasi(read_json());

    $stmt = $pdo->prepare(
        'UPDATE dokumentasi SET judul = :judul, keterangan = :keterangan, tanggal = :tanggal,
                gambar = :gambar, aktif = :aktif
         WHERE id = :id'
    );
    $stmt->execute($data + ['id' => $id]);

    // Gambar baru sudah tersimpan & dipakai → baru gambar lama boleh dibersihkan
    if ($lama['gambar'] !== $data['gambar']) {
        hapus_gambar_tak_terpakai($pdo, $lama['gambar']);
    }
    ok('Dokumentasi berhasil diperbarui.', format_dokumentasi(ambil_dokumentasi($pdo, $id), true));
}

if ($method === 'DELETE') {
    $id = query_id();
    $lama = ambil_dokumentasi($pdo, $id);
    if ($lama === null) {
        fail('Dokumentasi tidak ditemukan.', 404);
    }
    $stmt = $pdo->prepare('DELETE FROM dokumentasi WHERE id = ?');
    $stmt->execute([$id]);
    hapus_gambar_tak_terpakai($pdo, $lama['gambar']);
    ok('Dokumentasi berhasil dihapus.');
}
