<?php
// /api/requests_list.php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';

// admin-only
api_require_admin();

$pdo = $__pdo;
$status = isset($_GET['status']) ? trim((string)$_GET['status']) : null;

if ($status) {
    $st = $pdo->prepare('SELECT * FROM requests WHERE status = ? ORDER BY created_at DESC LIMIT 500');
    $st->execute([$status]);
} else {
    $st = $pdo->query('SELECT * FROM requests ORDER BY created_at DESC LIMIT 500');
}
$rows = $st->fetchAll();
api_json(['ok'=>true, 'rows'=>$rows]);
