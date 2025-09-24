<?php
// web_inc/app.php
declare(strict_types=1);

// Start session (if not started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -----------------------------------------------------------------------------
// DB: Try reuse repo db_config.php if exists, else create PDO from ENV / defaults
// -----------------------------------------------------------------------------
$rootDbConfig = dirname(__DIR__) . '/db_config.php';
if (is_file($rootDbConfig)) {
    // db_config.php may define $pdo (PDO) or $conn (mysqli) or connection constants.
    require_once $rootDbConfig;
}

/**
 * Return PDO instance (tries to reuse existing $pdo or derives from $conn).
 * If none found, build from ENV or sensible defaults.
 *
 * @return PDO
 */
function web_get_pdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Reuse global $pdo if present
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        $pdo = $GLOBALS['pdo'];
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }

    // If repo used mysqli $conn, attempt to derive credentials (best-effort)
    if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
        $mysqli = $GLOBALS['conn'];
        // fallback defaults (may be inaccurate) — prefer setting ENV on VPS
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'if0_39302480_nano_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: 'RootSaicur';
        $dsn  = "mysql:host={$host};dbname={$db};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    }

    // Build from environment or defaults
    $dsn = getenv('DB_DSN') ?: null;
    if (!$dsn) {
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'if0_39302480_nano_db';
        $dsn  = "mysql:host={$host};dbname={$db};charset=utf8mb4";
    }
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: 'RootSaicur';

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    return $pdo;
}

// -----------------------------------------------------------------------------
// Auth helpers for web pages (session-based). Adjust to fit your existing auth.
// -----------------------------------------------------------------------------
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

function is_admin(): bool {
    return !empty($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_admin(): void {
    if (!is_admin()) {
        http_response_code(403);
        echo "Forbidden: admin only";
        exit;
    }
}

// -----------------------------------------------------------------------------
// Small helpers
// -----------------------------------------------------------------------------
function money(int $cents): string {
    $baht = $cents / 100;
    return number_format($baht, 2);
}

function get_balance_by_uid(PDO $pdo, string $uid): ?int {
    $st = $pdo->prepare('SELECT balance FROM cards WHERE uid = ?');
    $st->execute([$uid]);
    $r = $st->fetch();
    return $r ? (int)$r['balance'] : null;
}

function get_logs(PDO $pdo, string $uid, int $limit = 100): array {
    $st = $pdo->prepare('SELECT action, delta, balance_after, applied, ts FROM logs WHERE uid = ? ORDER BY ts DESC LIMIT ?');
    // PDO requires bound params for LIMIT to be integers; use cast for safety
    $st->bindValue(1, $uid, PDO::PARAM_STR);
    $st->bindValue(2, (int)$limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
}

// CSRF token simple helpers for admin forms
function web_csrf_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf_token'];
}
function web_check_csrf(string $token): bool {
    return !empty($token) && !empty($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}
