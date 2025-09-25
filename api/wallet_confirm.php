<?php
// api/wallet_confirm.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db_config.php'; // $conn (PDO)

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$uid     = strtoupper(trim($in['uid'] ?? ''));
$applied = strtoupper(trim($in['applied'] ?? ''));

if ($uid==='' || strlen($applied)!==32) {
  http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'bad payload']); exit;
}

// หา pending_writes
$st = $conn->prepare("SELECT id, request_id FROM pending_writes WHERE uid=? AND newBlock4=? ORDER BY id DESC LIMIT 1");
$st->execute([$uid, $applied]);
$pw = $st->fetch(PDO::FETCH_ASSOC);
if (!$pw) { echo json_encode(['ok'=>false,'msg'=>'no pending write']); exit; }

// หา request เพื่อรู้จำนวนเงิน
$amount = 0; 
$req_id = $pw['request_id'] ? (int)$pw['request_id'] : null;
if ($req_id) {
  $q = $conn->prepare("SELECT amount FROM requests WHERE id=? LIMIT 1");
  $q->execute([$req_id]);
  $r = $q->fetch(PDO::FETCH_ASSOC);
  if ($r) $amount = (int)$r['amount'];
}

// อัปเดต balance ใน cards (create if not exists)
$conn->prepare("INSERT INTO cards (uid, balance) VALUES (?, 0) ON DUPLICATE KEY UPDATE uid=uid")->execute([$uid]);
$conn->prepare("UPDATE cards SET balance = balance + ? WHERE uid=?")->execute([$amount, $uid]);

// อ่าน balance ล่าสุดเพื่อ log
$bq = $conn->prepare("SELECT balance FROM cards WHERE uid=? LIMIT 1");
$bq->execute([$uid]);
$bal = (int)($bq->fetchColumn() ?: 0);

// logs
$conn->prepare("INSERT INTO logs (uid, action, delta, balance_after, applied, ts) VALUES (?, 'topup', ?, ?, ?, NOW())")
     ->execute([$uid, $amount, $bal, $applied]);

// ปิด request
if ($req_id) {
  $conn->prepare("UPDATE requests SET status='applied', updated_at=NOW() WHERE id=?")->execute([$req_id]);
}

echo json_encode(['ok'=>true,'msg'=>'confirmed','balance'=>$bal]);
