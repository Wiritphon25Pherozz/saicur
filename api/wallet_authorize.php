<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// api/wallet_authorize.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db_config.php'; // $conn (PDO)

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$uid     = trim($in['card_uid'] ?? $in['uid'] ?? '');
$user_id = isset($in['user_id']) ? (int)$in['user_id'] : null;
$amount  = isset($in['amount']) ? (int)$in['amount'] : 0; // หน่วยสตางค์
$block4  = strtoupper(trim($in['block4'] ?? ''));

if ($uid === '' || !$user_id) { http_response_code(400); echo json_encode(['allow'=>false,'msg'=>'missing uid/user_id']); exit; }

// ยืนยันความสัมพันธ์ uid ↔ user_id
$chk = $conn->prepare("SELECT 1 FROM nfc_cards WHERE user_id=? AND card_uid=? LIMIT 1");
$chk->execute([$user_id, $uid]);
if (!$chk->fetchColumn()) { echo json_encode(['allow'=>false,'msg'=>'uid does not belong to this user']); exit; }

// หาคำขอล่าสุดของ uid
$req = $conn->prepare("SELECT id, amount, status FROM requests WHERE uid=? AND status IN ('pending','approved') ORDER BY id DESC LIMIT 1");
$req->execute([$uid]);
$R = $req->fetch(PDO::FETCH_ASSOC);

if (!$R && $amount <= 0) { echo json_encode(['allow'=>false,'msg'=>'no pending/approved request']); exit; }
if ($R) $amount = (int)$R['amount'];

// สร้าง newBlock4
$seed = $uid.'|'.$user_id.'|'.$amount.'|'.time().$block4;
$newBlock4 = strtoupper(substr(hash('sha256', $seed), 0, 32));

// บันทึก pending_writes
$stmt = $conn->prepare("
  INSERT INTO pending_writes (uid, newBlock4, request_id, created_at)
  VALUES (?, ?, ?, NOW())
  ON DUPLICATE KEY UPDATE
    newBlock4 = VALUES(newBlock4),
    request_id = VALUES(request_id),
    created_at = NOW()
");
$stmt->execute([$uid, $newBlock4, $R ? (int)$R['id'] : null]);

echo json_encode(['allow'=>true,'newBlock4'=>$newBlock4,'msg'=>'ok']);
