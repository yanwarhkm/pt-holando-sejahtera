<?php
/* =========================================================
   API AKSES PANDUAN (berbasis pesanan)
   GET  ?halaman=beranda|panduan-kentang
        → cek akses sesi ini; jika valid, kirim langkah panduan terkunci
   POST { halaman, kode, wa }
        → verifikasi pesanan (nomor pesanan + WhatsApp saat checkout),
          lalu simpan akses di sesi server

   Sumber kebenaran: tabel `pesanan`. Akses hanya untuk pesanan yang sudah
   dikonfirmasi admin (status diproses/selesai) dan dicek ulang di setiap
   request, sehingga pembatalan oleh admin langsung mencabut akses.
   Response tidak pernah memuat data pesanan.
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

const STATUS_BERI_AKSES = ['diproses', 'selesai'];
const HALAMAN_PANDUAN = ['beranda', 'panduan-kentang'];
const SESI_AKSES = 'panduan_pesanan_id';
const SESI_PERCOBAAN = 'panduan_percobaan';
const MAKS_PERCOBAAN = 10;
const JENDELA_PERCOBAAN = 900; // detik

const PESAN_TERKUNCI = 'Panduan lengkap tersedia untuk konsumen yang sudah melakukan pembelian.';

function jawab_akses(bool $akses, string $pesan, ?string $halaman = null): never
{
    $body = ['success' => true, 'hasAccess' => $akses, 'message' => $pesan];
    if ($akses && $halaman !== null) {
        $konten = require dirname(__DIR__) . '/konten/panduan.php';
        $body['data'] = ['langkah' => $konten[$halaman]];
    }
    http_response_code(200);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

/** 08xx / +628xx / 628xx → 628xx agar format penulisan tidak memengaruhi pencocokan */
function wa_kanonik(string $wa): string
{
    $wa = ltrim(preg_replace('/[^\d+]/', '', $wa) ?? '', '+');
    return str_starts_with($wa, '0') ? '62' . substr($wa, 1) : $wa;
}

$method = allow_methods('GET', 'POST');
start_session();
$pdo = db();

if ($method === 'GET') {
    $halaman = $_GET['halaman'] ?? '';
    if (!is_string($halaman) || !in_array($halaman, HALAMAN_PANDUAN, true)) {
        fail('Parameter halaman tidak valid.', 422);
    }

    $pesananId = $_SESSION[SESI_AKSES] ?? null;
    if (!is_int($pesananId)) {
        jawab_akses(false, PESAN_TERKUNCI);
    }

    $stmt = $pdo->prepare('SELECT status FROM pesanan WHERE id = ?');
    $stmt->execute([$pesananId]);
    $status = $stmt->fetchColumn();
    if (!in_array($status, STATUS_BERI_AKSES, true)) {
        unset($_SESSION[SESI_AKSES]);
        jawab_akses(false, 'Pesanan yang sebelumnya diverifikasi tidak lagi memenuhi syarat akses panduan.');
    }
    jawab_akses(true, 'Akses panduan aktif.', $halaman);
}

// POST: verifikasi pesanan
$input = read_json();
$v = new Validator($input);
$halaman = $v->in('halaman', 'Halaman', HALAMAN_PANDUAN);
$kode = $v->string('kode', 'Nomor pesanan', 30);
$wa = $v->phone('wa');
if ($kode !== null) {
    $kode = strtoupper($kode);
    if (!preg_match('/^PSN-\d{6}-[0-9A-F]{6}$/', $kode)) {
        $v->addError('kode', 'Format nomor pesanan tidak valid. Contoh: PSN-260925-A1B2C3.');
    }
}
$v->check();

// Batasi percobaan per sesi untuk memperlambat tebak-tebakan nomor pesanan
$sekarang = time();
$percobaan = array_values(array_filter(
    (array) ($_SESSION[SESI_PERCOBAAN] ?? []),
    fn($t) => is_int($t) && $t > $sekarang - JENDELA_PERCOBAAN
));
if (count($percobaan) >= MAKS_PERCOBAAN) {
    fail('Terlalu banyak percobaan. Silakan coba lagi dalam 15 menit.', 429);
}
$percobaan[] = $sekarang;
$_SESSION[SESI_PERCOBAAN] = $percobaan;

$stmt = $pdo->prepare('SELECT id, no_wa, status FROM pesanan WHERE kode_pesanan = ?');
$stmt->execute([$kode]);
$pesanan = $stmt->fetch();

// Pesanan tidak ada & WA tidak cocok diberi pesan yang sama (tidak membocorkan keberadaan pesanan)
if (!$pesanan || !hash_equals(wa_kanonik($pesanan['no_wa']), wa_kanonik($wa))) {
    usleep(300000);
    jawab_akses(false, 'Nomor pesanan atau nomor WhatsApp tidak cocok dengan data pesanan kami.');
}

if ($pesanan['status'] === 'baru') {
    jawab_akses(false, 'Pesanan Anda sudah kami terima dan sedang menunggu konfirmasi admin. Panduan lengkap akan terbuka setelah pesanan dikonfirmasi.');
}
if (!in_array($pesanan['status'], STATUS_BERI_AKSES, true)) {
    jawab_akses(false, 'Pesanan ini dibatalkan sehingga tidak dapat membuka panduan lengkap.');
}

session_regenerate_id(true);
$_SESSION[SESI_AKSES] = (int) $pesanan['id'];
unset($_SESSION[SESI_PERCOBAAN]);

$pdo->prepare('INSERT INTO akses_panduan (sumber, pesanan_id) VALUES (?, ?)')
    ->execute([$halaman, (int) $pesanan['id']]);

jawab_akses(true, 'Pesanan terverifikasi. Panduan lengkap sudah terbuka.', $halaman);
