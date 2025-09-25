<?php
// api/requests_list.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db_config.php'; // $conn (PDO)

$status = $_GET['status'] ?? 'pending';
$st = $conn->prepare("SELECT id, uid, amount, status, created_at FROM requests WHERE status=? ORDER BY created_at DESC LIMIT 200");
$st->execute([$status]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['ok'=>true, 'rows'=>$rows], JSON_UNESCAPED_UNICODE);
