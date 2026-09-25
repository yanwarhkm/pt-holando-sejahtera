<?php
/**
 * Ganti / buat password admin dari command line.
 * Pemakaian: php database/set-admin-password.php <username> <password-baru>
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

[$script, $username, $password] = $argv + [null, null, null];
if (!$username || !$password) {
    fwrite(STDERR, "Pemakaian: php database/set-admin-password.php <username> <password-baru>\n");
    exit(1);
}
if (strlen($password) < 8) {
    fwrite(STDERR, "Password minimal 8 karakter.\n");
    exit(1);
}

require dirname(__DIR__) . '/api/db.php';

$stmt = db()->prepare(
    'INSERT INTO admin (username, password_hash) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
);
$stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
echo "Password untuk admin '$username' berhasil disimpan.\n";
