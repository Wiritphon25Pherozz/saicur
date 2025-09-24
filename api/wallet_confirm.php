<?php
// /api/wallet_confirm.php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';

$pdo = $__pdo;
$d = api_read_json();
$uid = isset($d['uid']) ? trim((string)$d['uid']) : '';
$applied = isset($d['applied']) ? strtoupper(trim((string)$d['applied'])) : '';
$request_id = isset($d['request_id']) ? (int)$d['request_id'] : null;

if ($uid === '' || $applied === '' || !api_is_hex32($applied)) {
    api_json(['ok'=>false, 'msg'=>'bad input'], 400);
}

$pdo->beginTransaction();
try {
    $pdo->prepare('INSERT INTO logs(uid, action, applied, request_id) VALUES(?,?,?,?)')->execute([$uid, 'confirm', $applied, $request_id]);
    if ($request_id) {
        $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?')->execute(['applied', $request_id]);
        // and optionally remove pending_writes
        $pdo->prepare('DELETE FROM pending_writes WHERE request_id = ?')->execute([$request_id]);
    }
    $pdo->commit();
    api_json(['ok'=>true, 'msg'=>'confirmed']);
} catch (Throwable $e) {
    $pdo->rollBack();
    api_json(['ok'=>false, 'msg'=>'db error'], 500);
}
