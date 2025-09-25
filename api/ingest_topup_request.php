<?php
// api/ingest_topup_request.php
require_once __DIR__ . '/../db_config.php'; // $conn (PDO)

$username    = trim($_POST['username'] ?? '');
$amount_baht = trim($_POST['amount'] ?? '');
$note        = trim($_POST['note'] ?? '');

if ($username === '' || $amount_baht === '') {
  http_response_code(400);
  echo "ข้อมูลไม่ครบ (ต้องมี username และ amount)"; 
  exit;
}

$amount_baht = floatval($amount_baht);
if ($amount_baht <= 0) { http_response_code(400); echo "จำนวนเงินไม่ถูกต้อง"; exit; }

try {
  // หา user
  $st = $conn->prepare("SELECT id, username FROM users WHERE username=? LIMIT 1");
  $st->execute([$username]);
  $user = $st->fetch(PDO::FETCH_ASSOC);
  if (!$user) { http_response_code(404); echo "ไม่พบผู้ใช้ ".htmlspecialchars($username); exit; }
  $user_id = (int)$user['id'];

  // หา card_uid ของ user นี้
  $c = $conn->prepare("SELECT card_uid FROM nfc_cards WHERE user_id=? LIMIT 1");
  $c->execute([$user_id]); 
  $row = $c->fetch(PDO::FETCH_ASSOC);
  if (!$row || empty($row['card_uid'])) {
    echo "<h3>ผู้ใช้นี้ยังไม่มีบัตรที่ผูกไว้</h3><p>ไปผูกที่ <code>api/bind_card.php</code> ก่อน</p>";
    exit;
  }
  $card_uid = $row['card_uid'];

  // แปลงเป็นสตางค์ (int)
  $amt_satang = (int) round($amount_baht * 100);

  // บันทึกคำขอ topup → pending
  $conn->prepare("
    INSERT INTO requests (uid, req_by, amount, type, status, admin_id, admin_note, created_at)
    VALUES (?, ?, ?, 'topup', 'pending', NULL, ?, NOW())
  ")->execute([$card_uid, $user_id, $amt_satang, $note]);

  // forward ไป authorize
  $forward = ['card_uid'=>$card_uid, 'user_id'=>$user_id, 'amount'=>$amt_satang, 'note'=>$note];
  $url = 'http://127.0.0.1/api/wallet_authorize.php';
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true,
    CURLOPT_POSTFIELDS=>json_encode($forward, JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
    CURLOPT_TIMEOUT=>10,
  ]);
  $resp = curl_exec($ch); 
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
  curl_close($ch);

  if ($http!==200) { echo "<h3>authorize ไม่สำเร็จ [$http]</h3><pre>".htmlspecialchars($resp)."</pre>"; exit; }
  $ans = json_decode($resp, true) ?: [];
  if (!empty($ans['allow']) && !empty($ans['newBlock4']) && strlen($ans['newBlock4'])===32) {
    echo "<h3>ส่งคำขอแล้ว</h3><p>ผู้ใช้ <strong>".htmlspecialchars($username)."</strong> (UID: ".htmlspecialchars($card_uid).")</p><p>ไปแตะบัตรที่เครื่องอ่านเพื่อเขียนข้อมูล</p>";
  } else {
    echo "<h3>ไม่ได้รับอนุญาต</h3><pre>".htmlspecialchars($resp)."</pre>";
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo "server error: ".htmlspecialchars($e->getMessage());
}
