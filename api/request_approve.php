<?php
// /api/request_approve.php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';

// admin-only
api_require_admin();

$pdo = $__pdo;
$d = api_read_json();
$request_id = isset($d['request_id']) ? (int)$d['request_id'] : 0;
$admin_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($d['admin_id']) ? (int)$d['admin_id'] : null);
$admin_note = isset($d['admin_note']) ? trim((string)$d['admin_note']) : '';

if ($request_id <= 0) api_json(['ok'=>false,'msg'=>'bad input'], 400);

$pdo->beginTransaction();
try {
    $st = $pdo->prepare('SELECT * FROM requests WHERE id=? FOR UPDATE');
    $st->execute([$request_id]);
    $req = $st->fetch();
    if (!$req) {
        $pdo->rollBack();
        api_json(['ok'=>false,'msg'=>'request not found'], 404);
    }
    if ($req['status'] !== 'pending') {
        $pdo->rollBack();
        api_json(['ok'=>false,'msg'=>'request not pending'], 400);
    }

    $uid = $req['uid'];
    $amount = (int)$req['amount'];

    // lock or create card
    $st2 = $pdo->prepare('SELECT balance, version FROM cards WHERE uid=? FOR UPDATE');
    $st2->execute([$uid]);
    $card = $st2->fetch();
    if (!$card) {
        $pdo->prepare('INSERT INTO cards(uid, balance, version) VALUES(?,?,?)')->execute([$uid, 0, 0]);
        $bal = 0;
        $ver = 0;
    } else {
        $bal = (int)$card['balance'];
        $ver = (int)$card['version'];
    }

    $new = $bal + $amount;
    $newVer = $ver + 1;

    $pdo->prepare('UPDATE cards SET balance=?, version=? WHERE uid=?')->execute([$new, $newVer, $uid]);
    $pdo->prepare('UPDATE requests SET status=?, admin_id=?, admin_note=? WHERE id=?')->execute(['approved', $admin_id, $admin_note, $request_id]);
    $pdo->prepare('INSERT INTO logs(uid, action, delta, balance_after, request_id) VALUES(?,?,?,?,?)')->execute([$uid, 'topup', $amount, $new, $request_id]);

    // create pending write for terminal to pick up
    $hex = api_block4_from_balance($new);
    $pdo->prepare('INSERT INTO pending_writes(uid, newBlock4, request_id) VALUES(?,?,?)')->execute([$uid, $hex, $request_id]);
    $pending_id = (int)$pdo->lastInsertId();

    $pdo->commit();

    api_json(['ok'=>true, 'newBlock4'=>$hex, 'pending_id'=>$pending_id, 'msg'=>'approved']);
} catch (Throwable $e) {
    $pdo->rollBack();
    api_json(['ok'=>false, 'msg'=>'db error '. $e->getMessage()], 500);
}
