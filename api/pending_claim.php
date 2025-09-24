<?php
// /api/pending_claim.php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';

$pdo = $__pdo;
$d = api_read_json();
$pending_id = isset($d['pending_id']) ? (int)$d['pending_id'] : 0;
$terminal = isset($d['terminal']) ? trim((string)$d['terminal']) : 'terminal';

if ($pending_id <= 0) api_json(['ok'=>false, 'msg'=>'bad input'], 400);

$pdo->beginTransaction();
try {
    $st = $pdo->prepare('SELECT * FROM pending_writes WHERE id=? FOR UPDATE');
    $st->execute([$pending_id]);
    $row = $st->fetch();
    if (!$row || $row['claimed_at'] !== null) {
        $pdo->rollBack();
        api_json(['ok'=>false, 'msg'=>'not available'], 409);
    }
    $pdo->prepare('UPDATE pending_writes SET claimed_at = NOW(), claimed_by = ? WHERE id = ?')->execute([$terminal, $pending_id]);
    $pdo->commit();
    api_json(['ok'=>true,'msg'=>'claimed']);
} catch (Throwable $e) {
    $pdo->rollBack();
    api_json(['ok'=>false,'msg'=>'db error'],500);
}
