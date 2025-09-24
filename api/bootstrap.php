<?php
// /api/bootstrap.php
declare(strict_types=1);

// try reuse root db_config.php if exists (your repo likely has it)
$rootDbConfig = dirname(__DIR__) . '/db_config.php';
if (is_file($rootDbConfig)) {
    require_once $rootDbConfig; // may define $pdo or $conn
}

// ensure we expose a PDO $__pdo
function api_get_pdo(): PDO {
    // reuse existing $pdo
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        $GLOBALS['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $GLOBALS['pdo']->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $GLOBALS['pdo'];
    }

    // if repo used mysqli $conn => try to derive
    if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
        $mysqli = $GLOBALS['conn'];
        // best-effort extract, fallback defaults
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'if0_39302480_nano_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: 'RootSaicur';
        $dsn  = "mysql:host={$host};dbname={$db};charset=utf8mb4";
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    // fallback to environment or defaults
    $dsn = getenv('DB_DSN') ?: null;
    if (!$dsn) {
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'if0_39302480_nano_db';
        $dsn  = "mysql:host={$host};dbname={$db};charset=utf8mb4";
    }
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: 'RootSaicur';

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}

function api_migrate(PDO $pdo): void {
    // create minimal tables if missing
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS cards (
        uid VARCHAR(32) PRIMARY KEY,
        balance INT NOT NULL DEFAULT 0,
        version INT NOT NULL DEFAULT 0
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        uid VARCHAR(32),
        action VARCHAR(32),
        delta INT NULL,
        balance_after INT NULL,
        request_id INT NULL,
        applied VARCHAR(32) NULL,
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_logs_uid_ts (uid, ts)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        uid VARCHAR(32) NOT NULL,
        req_by INT NULL,
        amount INT NOT NULL,
        type ENUM('topup','manual_adjust') NOT NULL DEFAULT 'topup',
        status ENUM('pending','approved','rejected','applied') NOT NULL DEFAULT 'pending',
        admin_id INT NULL,
        admin_note TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS pending_writes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        uid VARCHAR(32),
        newBlock4 VARCHAR(32),
        request_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        claimed_at TIMESTAMP NULL,
        claimed_by VARCHAR(128) NULL,
        INDEX (request_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

function api_read_json(): array {
    $raw = file_get_contents('php://input');
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}

function api_json($payload, int $code=200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// bootstrap
$__pdo = api_get_pdo();
api_migrate($__pdo);
