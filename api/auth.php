<?php
/* =========================================================
   API AUTENTIKASI ADMIN
   GET                          → cek sesi aktif
   POST { action: "login", username, password }
   POST { action: "logout" }
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

$method = allow_methods('GET', 'POST');

if ($method === 'GET') {
    $admin = current_admin();
    if ($admin === null) {
        fail('Belum login.', 401);
    }
    ok('Sesi aktif.', $admin);
}

$input = read_json();
$action = $input['action'] ?? '';

if ($action === 'logout') {
    start_session();
    $_SESSION = [];
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $params['path'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'],
    ]);
    session_destroy();
    ok('Berhasil keluar.');
}

if ($action !== 'login') {
    fail('Aksi tidak dikenali.', 400);
}

$v = new Validator($input);
$username = $v->string('username', 'Nama pengguna', 50);
$password = $input['password'] ?? null;
if (!is_string($password) || $password === '') {
    $v->addError('password', 'Kata sandi wajib diisi.');
} elseif (strlen($password) > 200) {
    $v->addError('password', 'Kata sandi terlalu panjang.');
}
$v->check();

$pdo = db();
$stmt = $pdo->prepare('SELECT id, username, nama, password_hash FROM admin WHERE username = ?');
$stmt->execute([$username]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password_hash'])) {
    usleep(400000); // perlambat percobaan tebak sandi
    fail('Nama pengguna atau kata sandi salah.', 401);
}

if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
    $pdo->prepare('UPDATE admin SET password_hash = ? WHERE id = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
}
$pdo->prepare('UPDATE admin SET last_login_at = NOW() WHERE id = ?')->execute([$admin['id']]);

start_session();
session_regenerate_id(true);
$_SESSION['admin_id'] = (int) $admin['id'];
$_SESSION['admin_username'] = $admin['username'];
$_SESSION['admin_nama'] = $admin['nama'];
$_SESSION['last_activity'] = time();

ok('Login berhasil.', ['id' => (int) $admin['id'], 'username' => $admin['username'], 'nama' => $admin['nama']]);
