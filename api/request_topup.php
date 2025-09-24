<?php
// /api/request_topup.php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';

$pdo = $__pdo;
$d = api_read_json();
$uid = isset($d['uid']) ? trim((string)$d['uid']) : '';
$amount = isset($d['amount']) ? (int)$d['amount'] : 0;
$req_by = isset($d['req_by']) ? (int)$d['req_by'] : null;

if ($uid === '' || $amount <= 0) {
    api_json(['ok'=>false, 'msg'=>'bad input'], 400);
}

try {
    $st = $pdo->prepare('INSERT INTO requests(uid, req_by, amount, type, status) VALUES(?,?,?,?,?)');
    $st->execute([$uid, $req_by, $amount, 'topup', 'pending']);
    $id = (int)$pdo->lastInsertId();
    api_json(['ok'=>true, 'request_id'=>$id, 'msg'=>'requested']);
} catch (Throwable $e) {
    api_json(['ok'=>false, 'msg'=>'db error'], 500);
}
