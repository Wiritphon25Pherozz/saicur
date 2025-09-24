<?php
// /api/wallet_topup.php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';

$pdo = $__pdo;
$d = api_read_json();
$uid = isset($d['uid']) ? trim((string)$d['uid']) : '';
$amount = isset($d['amount']) ? (int)$d['amount'] : 0;

if ($uid === '' || $amount <= 0) {
    api_json(['allow'=>false, 'msg'=>'bad input'], 400);
}

$pdo->beginTransaction();
try {
    $st = $pdo->prepare('SELECT balance, version FROM cards WHERE uid=? FOR UPDATE');
    $st->execute([$uid]);
    $row = $st->fetch();

    if (!$row) {
        $pdo->prepare('INSERT INTO cards(uid, balance, version) VALUES(?,0,0)')->execute([$uid]);
        $bal = 0;
        $ver = 0;
    } else {
        $bal = (int)$row['balance'];
        $ver = (int)$row['version'];
    }

    $new = $bal + $amount;
    $newVer = $ver + 1;

    $pdo->prepare('UPDATE cards SET balance=?, version=? WHERE uid=?')->execute([$new, $newVer, $uid]);
    $pdo->prepare('INSERT INTO logs(uid, action, delta, balance_after, request_id) VALUES(?,?,?,?,NULL)')->execute([$uid, 'topup', $amount, $new]);

    // optional: create pending write - but topup via admin should use request flow; keep simple
    $pdo->commit();

    $hex32 = api_block4_from_balance($new);
    api_json(['allow'=>true, 'newBlock4'=>$hex32, 'msg'=>'OK']);
} catch (Throwable $e) {
    $pdo->rollBack();
    api_json(['allow'=>false, 'msg'=>'db error'], 500);
}
