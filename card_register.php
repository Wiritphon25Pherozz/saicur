<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username']; // ????????????????????? login
$card_uid = $_GET['uid'] ?? null;

if (!$card_uid) {
    echo "No card UID provided.";
    exit;
}

// ????????????????????????????????
$stmt = $pdo->prepare("SELECT * FROM nfc_cards WHERE card_uid = ?");
$stmt->execute([$card_uid]);
$card = $stmt->fetch();

if ($card) {
    echo "Card already registered.";
    exit;
}

// Insert card ???????? db
$stmt = $pdo->prepare("INSERT INTO nfc_cards (user_id, card_uid, username, is_active, created_at)
                       VALUES (?, ?, ?, 1, NOW())");
$stmt->execute([$user_id, $card_uid, $username]);

echo "Card registered successfully for user: " . htmlspecialchars($username);
echo "<br><a href='admin_users.php'>Go back to Admin Panel</a>";
