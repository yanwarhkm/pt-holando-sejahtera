<?php
/* =========================================================
   KONEKSI DATABASE (PDO MySQL)
   Kredensial dibaca dari config/database.php atau environment
   variable — tidak pernah dikirim ke browser.
========================================================= */
declare(strict_types=1);

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

final class DatabaseUnavailable extends RuntimeException {}

function db_config(): array
{
    $file = dirname(__DIR__) . '/config/database.php';
    $config = is_file($file) ? require $file : [];
    if (!is_array($config)) {
        $config = [];
    }

    $env = [
        'host'     => 'DB_HOST',
        'port'     => 'DB_PORT',
        'name'     => 'DB_NAME',
        'user'     => 'DB_USER',
        'password' => 'DB_PASS',
    ];
    foreach ($env as $key => $var) {
        $value = getenv($var);
        if ($value !== false) {
            $config[$key] = $value;
        }
    }

    return $config + [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'holando_sejahtera',
        'user'     => 'root',
        'password' => '',
    ];
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = db_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $config['host'],
        (int) $config['port'],
        $config['name']
    );

    try {
        $pdo = new PDO($dsn, (string) $config['user'], (string) $config['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Detail hanya ke log server, bukan ke user
        error_log('[DB] Koneksi gagal: ' . $e->getMessage());
        throw new DatabaseUnavailable('Koneksi database gagal.', 0, $e);
    }

    return $pdo;
}
