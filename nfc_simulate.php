<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db_config.php';

// ตรวจสอบ login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$msg = "";

// ถ้ามีการ submit request
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $amount = (int)($_POST['amount'] ?? 0);
    if($amount > 0){
        $stmt = $conn->prepare("INSERT INTO wallet_requests(user_id, amount, status, created_at) VALUES(?,?, 'pending', NOW())");
        $stmt->execute([$userId, $amount]);
        $msg = "Request submitted! Waiting for admin approval.";
    } else {
        $msg = "Please enter a valid amount.";
    }
}

// ดึงคำขอของ user
$stmt = $conn->prepare("SELECT * FROM wallet_requests WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Top-up Request</title>
<style>
body{ font-family: Arial; margin: 40px; }
input, button { padding: 6px; margin: 4px 0; }
table { border-collapse: collapse; width: 50%; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
</style>
</head>
<body>
<h2>Request Top-up</h2>
<?php if($msg): ?>
<p style="color:green;"><?=htmlspecialchars($msg)?></p>
<?php endif; ?>
<form method="post">
    <label>Amount:</label>
    <input type="number" name="amount" min="1" required>
    <button type="submit">Request Top-up</button>
</form>

<h3>Your Requests</h3>
<table>
<tr><th>ID</th><th>Amount</th><th>Status</th><th>Created At</th></tr>
<?php foreach($requests as $r): ?>
<tr>
    <td><?=htmlspecialchars($r['id'])?></td>
    <td><?=htmlspecialchars($r['amount'])?></td>
    <td><?=htmlspecialchars($r['status'])?></td>
    <td><?=htmlspecialchars($r['created_at'])?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
