<?php
// api/request_approve.php
require_once __DIR__ . '/../db_config.php'; // $conn (PDO)
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit; }

$id = (int)($_POST['request_id'] ?? 0);
$approve = isset($_POST['approve']) ? (int)$_POST['approve'] : 1;
$note = trim($_POST['admin_note'] ?? '');

if ($id<=0) { http_response_code(400); echo "bad id"; exit; }

if ($approve===0) {
  $conn->prepare("UPDATE requests SET status='rejected', admin_note=? WHERE id=?")->execute([$note, $id]);
  header("Location: /admin_users.php"); exit;
}

$conn->prepare("UPDATE requests SET status='approved', admin_note=? WHERE id=?")->execute([$note, $id]);
header("Location: /admin_users.php");
