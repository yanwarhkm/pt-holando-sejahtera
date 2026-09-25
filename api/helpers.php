<?php
/* =========================================================
   HELPER API
   - Response JSON konsisten: { success, message, data }
   - Pembatasan HTTP method
   - Pembacaan & validasi input
   - Sesi admin
========================================================= */
declare(strict_types=1);

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

ini_set('display_errors', '0');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e): void {
    if ($e instanceof DatabaseUnavailable) {
        $status = 503;
        $message = 'Layanan database sedang tidak tersedia. Silakan coba beberapa saat lagi.';
    } else {
        error_log('[API] ' . $e);
        $status = 500;
        $message = 'Terjadi kesalahan pada server. Silakan coba lagi.';
    }
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
});

require __DIR__ . '/db.php';

/* ---------------- RESPONSE ---------------- */

function respond(bool $success, string $message, $data = null, int $status = 200, ?array $errors = null): never
{
    http_response_code($status);
    $body = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $body['data'] = $data;
    }
    if ($errors) {
        $body['errors'] = $errors;
    }
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function ok(string $message, $data = null, int $status = 200): never
{
    respond(true, $message, $data ?? new stdClass(), $status);
}

function fail(string $message, int $status = 400, ?array $errors = null): never
{
    respond(false, $message, null, $status, $errors);
}

/* ---------------- REQUEST ---------------- */

function allow_methods(string ...$methods): string
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, $methods, true)) {
        header('Allow: ' . implode(', ', $methods));
        fail('Method tidak diizinkan.', 405);
    }
    return $method;
}

/** Body JSON wajib untuk request yang mengubah data (juga menahan CSRF via form biasa). */
function read_json(): array
{
    $type = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($type, 'application/json') !== 0) {
        fail('Content-Type harus application/json.', 415);
    }
    $raw = file_get_contents('php://input', false, null, 0, 65537);
    if (strlen((string) $raw) > 65536) {
        fail('Data yang dikirim terlalu besar.', 413);
    }
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        fail('Format data tidak valid.', 400);
    }
    return $data;
}

function query_id(string $name = 'id'): int
{
    $value = $_GET[$name] ?? '';
    if (!is_string($value) || !ctype_digit($value) || (int) $value < 1) {
        fail('Parameter ' . $name . ' tidak valid.', 400);
    }
    return (int) $value;
}

/* ---------------- VALIDASI ---------------- */

final class Validator
{
    private array $errors = [];

    public function __construct(private array $input) {}

    public function string(string $key, string $label, int $max, bool $required = true, int $min = 1): ?string
    {
        $value = $this->input[$key] ?? null;
        if ($value === null || $value === '') {
            if ($required) {
                $this->errors[$key] = "$label wajib diisi.";
            }
            return null;
        }
        if (!is_string($value)) {
            $this->errors[$key] = "$label tidak valid.";
            return null;
        }
        $value = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '');
        $length = mb_strlen($value);
        if ($length === 0) {
            if ($required) {
                $this->errors[$key] = "$label wajib diisi.";
            }
            return null;
        }
        if ($length < $min) {
            $this->errors[$key] = "$label minimal $min karakter.";
        } elseif ($length > $max) {
            $this->errors[$key] = "$label maksimal $max karakter.";
        }
        return $value;
    }

    public function phone(string $key, string $label = 'Nomor WhatsApp'): ?string
    {
        $value = $this->string($key, $label, 25);
        if ($value === null) {
            return null;
        }
        $normalized = preg_replace('/[\s\-().]/', '', $value);
        if (!preg_match('/^(\+62|62|0)8\d{7,12}$/', $normalized)) {
            $this->errors[$key] = "$label tidak valid. Contoh: 081234567890.";
            return null;
        }
        return $normalized;
    }

    public function int(string $key, string $label, int $min, int $max, bool $required = true): ?int
    {
        $value = $this->input[$key] ?? null;
        if ($value === null || $value === '') {
            if ($required) {
                $this->errors[$key] = "$label wajib diisi.";
            }
            return null;
        }
        if (is_string($value) && preg_match('/^-?\d+$/', trim($value))) {
            $value = (int) trim($value);
        }
        if (!is_int($value)) {
            $this->errors[$key] = "$label harus berupa angka bulat.";
            return null;
        }
        if ($value < $min || $value > $max) {
            $this->errors[$key] = "$label harus antara $min dan $max.";
            return null;
        }
        return $value;
    }

    public function number(string $key, string $label, float $minExclusive, float $max): ?float
    {
        $value = $this->input[$key] ?? null;
        if ($value === null || $value === '') {
            $this->errors[$key] = "$label wajib diisi.";
            return null;
        }
        if (!is_numeric($value)) {
            $this->errors[$key] = "$label harus berupa angka.";
            return null;
        }
        $value = (float) $value;
        if ($value <= $minExclusive || $value > $max) {
            $this->errors[$key] = "$label harus lebih dari $minExclusive dan maksimal $max.";
            return null;
        }
        return $value;
    }

    public function in(string $key, string $label, array $allowed, bool $required = true): ?string
    {
        $value = $this->input[$key] ?? null;
        if ($value === null || $value === '') {
            if ($required) {
                $this->errors[$key] = "$label wajib dipilih.";
            }
            return null;
        }
        if (!is_string($value) || !in_array($value, $allowed, true)) {
            $this->errors[$key] = "$label tidak valid.";
            return null;
        }
        return $value;
    }

    public function bool(string $key, string $label): ?bool
    {
        $value = $this->input[$key] ?? null;
        if (!is_bool($value)) {
            $this->errors[$key] = "$label harus bernilai true/false.";
            return null;
        }
        return $value;
    }

    public function addError(string $key, string $message): void
    {
        $this->errors[$key] = $message;
    }

    /** Hentikan request dengan 422 bila ada error. */
    public function check(): void
    {
        if ($this->errors) {
            fail(reset($this->errors), 422, $this->errors);
        }
    }
}

/* ---------------- SESI ADMIN ---------------- */

const SESSION_IDLE_SECONDS = 7200;

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_maxlifetime', (string) SESSION_IDLE_SECONDS);
    session_name('HOLANDOSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function current_admin(): ?array
{
    start_session();
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    if (time() - (int) ($_SESSION['last_activity'] ?? 0) > SESSION_IDLE_SECONDS) {
        $_SESSION = [];
        session_destroy();
        return null;
    }
    $_SESSION['last_activity'] = time();
    return [
        'id'       => (int) $_SESSION['admin_id'],
        'username' => (string) $_SESSION['admin_username'],
        'nama'     => (string) $_SESSION['admin_nama'],
    ];
}

function require_admin(): array
{
    $admin = current_admin();
    if ($admin === null) {
        fail('Sesi admin berakhir. Silakan login kembali.', 401);
    }
    return $admin;
}
