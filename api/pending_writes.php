<?php
// /api/pending_writes.php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';

$pdo = $__pdo;
$st = $pdo->prepare('SELECT id, uid, newBlock4, request_id, created_at FROM pending_writes WHERE claimed_at IS NULL ORDER BY created_at ASC LIMIT 10');
$st->execute();
$rows = $st->fetchAll();
api_json(['ok'=>true, 'rows'=>$rows]);
