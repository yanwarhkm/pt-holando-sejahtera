<?php
/* =========================================================
   API UPLOAD GAMBAR BIBIT (admin)
   POST multipart/form-data, field "gambar" (satu berkas)
   → validasi ketat, simpan ke folder uploads/, kembalikan
     { path: "uploads/namafile" } untuk disimpan ke kolom produk.gambar.

   Prinsip:
   - Hanya nama/path hasil upload yang masuk DB — binary TIDAK disimpan di DB.
   - Nama berkas dari client TIDAK dipercaya; nama baru di-generate aman & unik.
   - Berkas dipastikan benar-benar gambar (getimagesize + MIME), bukan sekadar
     berganti ekstensi menjadi .jpg/.png.
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

allow_methods('POST');
require_admin();

const UPLOAD_DIR_REL   = 'uploads';            // folder gambar katalog (dalam root project)
const MAX_UPLOAD_BYTES = 3 * 1024 * 1024;      // batas wajar: 3 MB

// Ekstensi kanonik yang diizinkan → daftar MIME yang sah untuknya
const TIPE_GAMBAR = [
    'jpg'  => ['image/jpeg'],
    'png'  => ['image/png'],
    'webp' => ['image/webp'],
];

// Hasil getimagesize (IMAGETYPE_*) → ekstensi kanonik yang kita pakai
const IMAGETYPE_KE_EXT = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG  => 'png',
    IMAGETYPE_WEBP => 'webp',
];

$file = $_FILES['gambar'] ?? null;
if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    fail('Tidak ada berkas gambar yang dikirim.', 400);
}

switch ((int) $file['error']) {
    case UPLOAD_ERR_OK:
        break;
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
        fail('Ukuran gambar melebihi batas yang diizinkan.', 413);
        // no break — fail() menghentikan eksekusi
    default:
        fail('Gagal mengunggah berkas. Silakan coba lagi.', 400);
}

if (!is_uploaded_file($file['tmp_name'] ?? '')) {
    fail('Berkas tidak valid.', 400);
}
$size = (int) ($file['size'] ?? 0);
if ($size <= 0 || $size > MAX_UPLOAD_BYTES) {
    fail('Ukuran gambar maksimal 3 MB.', 413);
}

// 1) Pastikan benar-benar gambar (mencegah file non-gambar yang di-rename)
$info = @getimagesize($file['tmp_name']);
if ($info === false || !isset($info[2]) || !isset(IMAGETYPE_KE_EXT[$info[2]])) {
    fail('Berkas bukan gambar yang valid. Hanya JPG, PNG, atau WEBP.', 422);
}
$ext = IMAGETYPE_KE_EXT[$info[2]];

// 2) Cocokkan MIME asli isi berkas (bukan yang dilaporkan browser)
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
if (!in_array($mime, TIPE_GAMBAR[$ext], true)) {
    fail('Tipe gambar tidak didukung. Hanya JPG, PNG, atau WEBP.', 422);
}

// 3) Siapkan folder tujuan di dalam root katalog (bukan lokasi lain)
$dir = dirname(__DIR__) . '/' . UPLOAD_DIR_REL;
if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
    fail('Folder unggahan tidak dapat disiapkan.', 500);
}

// 4) Nama berkas aman & unik — TIDAK memakai nama asli dari client
try {
    $acak = bin2hex(random_bytes(8));
} catch (Throwable) {
    $acak = bin2hex(pack('N*', random_int(0, PHP_INT_MAX), random_int(0, PHP_INT_MAX)));
}
$namaFile = 'bibit_' . date('Ymd_His') . '_' . $acak . '.' . $ext;
$tujuan   = $dir . '/' . $namaFile;

if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
    fail('Gagal menyimpan gambar.', 500);
}
@chmod($tujuan, 0644);

// Path relatif inilah yang dipakai katalog (<img src>) dan disimpan ke DB.
ok('Gambar berhasil diunggah.', ['path' => UPLOAD_DIR_REL . '/' . $namaFile], 201);
