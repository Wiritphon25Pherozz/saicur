<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// api/ingest_card.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db_config.php'; //  $conn (PDO)

$in = json_decode(file_get_contents('php://input'), true);
if (!$in || empty($in['uid'])) {
  http_response_code(400);
  echo json_encode(['error'=>'bad_request','msg'=>'missing uid']);
  exit;
}
$card_uid  = trim($in['uid']);
$block4hex = isset($in['block4']) ? trim($in['block4']) : '';
$reader_id = $in['reader_id'] ?? null;
$ts        = $in['ts'] ?? time();

try {
  //  cards ( orphan-list )
  $conn->prepare("INSERT IGNORE INTO cards (uid, balance) VALUES (?, 0)")->execute([$card_uid]);

  //
  $st = $conn->prepare("SELECT id, user_id FROM nfc_cards WHERE card_uid=? LIMIT 1");
  $st->execute([$card_uid]);
  $card = $st->fetch(PDO::FETCH_ASSOC);

  if (!$card) {
    echo json_encode(['status'=>'need_bind','card_uid'=>$card_uid], JSON_UNESCAPED_UNICODE);
    exit;
  }

  //  forward  authorize
  $forward = [
    'card_uid'  => $card_uid,
    'user_id'   => (int)$card['user_id'],
    'reader_id' => $reader_id,
    'ts'        => $ts,
    'block4'    => $block4hex
  ];
  $url = 'http://localhost/api/wallet_authorize.php'; // 
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($forward, JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
  ]);
  $resp = curl_exec($ch);
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($http !== 200) {
    http_response_code(502);
    echo json_encode(['error'=>'authorize_failed','backend_status'=>$http,'backend_response'=>$resp]);
    exit;
  }
  $ans = json_decode($resp, true) ?: [];
  echo json_encode($ans, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error'=>'server_error','msg'=>$e->getMessage()]);
}
