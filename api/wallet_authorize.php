<?php
// /api/wallet_authorize.php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';
session_start();

$pdo = $__pdo;
$d = api_read_json();
$uid = isset($d['uid']) ? trim((string)$d['uid']) : '';
$amount = isset($d['amount']) ? (int)$d['amount'] : 0;

if ($uid === '' || $amount <= 0) {
    api_json(['allow'=>false, 'msg'=>'bad input'], 400);
}

// ?????????? user login ???????????
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    api_json(['allow'=>false, 'msg'=>'not logged in'], 403);
}
$userId   = (int)$_SESSION['user_id'];
$username = $_SESSION['username'];

$pdo->beginTransaction();
try {
    $st = $pdo->prepare('SELECT id, balance, version, user_id FROM nfc_cards WHERE card_uid=? FOR UPDATE');
    $st->execute([$uid]);
    $row = $st->fetch();

    if (!$row) {
        // ????????????????????????? ? auto insert
        $pdo->prepare('INSERT INTO nfc_cards (card_uid, user_id, username, balance, version, is_active, created_at)
                       VALUES (?, ?, ?, 0, 1, 1, NOW())')
            ->execute([$uid, $userId, $username]);

        $pdo->commit();
        api_json(['allow'=>false, 'msg'=>'card registered, no balance'], 200);
    }

    // ????????????????????????? user ??? ? ??????
    if ((int)$row['user_id'] !== $userId) {
        $pdo->rollBack();
        api_json(['allow'=>false, 'msg'=>'card not belong to user'], 403);
    }

    $bal = (int)$row['balance'];
    $ver = (int)$row['version'];

    if ($bal < $amount) {
        $pdo->rollBack();
        api_json(['allow'=>false, 'msg'=>'insufficient funds'], 200);
    }

    $new = $bal - $amount;
    $newVer = $ver + 1;

    $pdo->prepare('UPDATE nfc_cards SET balance=?, version=? WHERE card_uid=?')
        ->execute([$new, $newVer, $uid]);

    $pdo->prepare('INSERT INTO logs(card_uid, action, delta, balance_after, request_id) VALUES(?,?,?,?,NULL)')
        ->execute([$uid, 'spend', -$amount, $new]);

    $pdo->commit();

    $hex32 = api_block4_from_balance($new);
    api_json(['allow'=>true, 'newBlock4'=>$hex32, 'msg'=>'OK']);
} catch (Throwable $e) {
    $pdo->rollBack();
    api_json(['allow'=>false, 'msg'=>'db error'], 500);
}
