<?php
// admin_users.php (ฉบับใช้ $conn)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db_config.php'; // ให้ $conn (PDO)

// ฟังก์ชันช่วยเล็กๆ
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$msg = '';

// ดึงผู้ใช้
try {
  $uStmt = $conn->query('SELECT id, username, role, name FROM users ORDER BY id DESC LIMIT 200');
  $users = $uStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $users = []; }

// ดึงคำขอ pending (ผ่าน API ภายใน หรือจะ query ตรงก็ได้)
$pendingRows = [];
try {
  $st = $conn->prepare("SELECT id, uid, amount, status, created_at FROM requests WHERE status='pending' ORDER BY created_at DESC LIMIT 200");
  $st->execute();
  $pendingRows = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $pendingRows = []; }

// การ์ดที่ลงทะเบียนไว้แล้ว (มีเจ้าของ)
try {
  $UCstmt = $conn->query("
    SELECT u.username, c.card_uid, c.is_active, c.created_at
    FROM nfc_cards c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
  ");
  $registeredCards = $UCstmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $registeredCards = []; }

// Users & NFC Cards (LEFT JOIN)
try {
  $stmtUC = $conn->query("
    SELECT u.id AS user_id, u.username, u.name AS fullname,
           c.card_uid, c.created_at AS bound_at
    FROM users u
    LEFT JOIN nfc_cards c ON c.user_id = u.id
    ORDER BY u.id ASC
  ");
  $usersWithCards = $stmtUC->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $usersWithCards = []; }

// บัตรที่ยังไม่มีเจ้าของ: ใช้ตาราง cards ที่ไม่อยู่ใน nfc_cards
try {
  $orphanCards = $conn->query("
    SELECT c.uid AS card_uid
    FROM cards c
    LEFT JOIN nfc_cards n ON n.card_uid = c.uid
    WHERE n.card_uid IS NULL
    ORDER BY c.uid ASC
  ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $orphanCards = []; }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin - Users & Requests</title>
<link rel="stylesheet" href="/style.css">
</head>
<body>
<h1>Admin - Users</h1>

<section>
  <h2>Existing users</h2>
  <?php if (empty($users)): ?>
    <p>No users.</p>
  <?php else: ?>
    <table border="1" cellpadding="6">
      <thead><tr><th>ID</th><th>Username</th><th>Name</th><th>Role</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= h($u['id']) ?></td>
            <td><?= h($u['username']) ?></td>
            <td><?= h($u['name']) ?></td>
            <td><?= h($u['role']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<hr>

<section>
  <h2>Pending Top-up Requests</h2>
  <?php if (empty($pendingRows)): ?>
    <p>No pending requests.</p>
  <?php else: ?>
    <table border="1" cellpadding="6">
      <thead><tr><th>ID</th><th>UID</th><th>Amount (Baht)</th><th>Requested</th></tr></thead>
      <tbody>
      <?php foreach ($pendingRows as $r): ?>
        <tr>
          <td><?= h($r['id']) ?></td>
          <td><?= h($r['uid']) ?></td>
          <td style="text-align:right;"><?= number_format(((int)$r['amount'])/100, 2) ?></td>
          <td><?= h($r['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<section>
  <h1>Registered User Cards</h1>
  <?php if (empty($registeredCards)): ?>
    <p>No registered cards.</p>
  <?php else: ?>
    <table border="1" cellpadding="8">
      <tr>
        <th>Username</th>
        <th>Card UID</th>
        <th>Status</th>
        <th>Registered At</th>
      </tr>
      <?php foreach ($registeredCards as $c): ?>
        <tr>
          <td><?= h($c['username']) ?></td>
          <td><?= h($c['card_uid']) ?></td>
          <td><?= ($c['is_active'] ? 'Active' : 'Inactive') ?></td>
          <td><?= h($c['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</section>

<section style="margin-top:24px">
  <h2>Users &amp; NFC Cards</h2>
  <?php if (!empty($usersWithCards)): ?>
    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse; width:100%">
      <thead style="background:#f2f2f2">
        <tr>
          <th style="text-align:left">User ID</th>
          <th style="text-align:left">Username</th>
          <th style="text-align:left">Name</th>
          <th style="text-align:left">Card UID</th>
          <th style="text-align:left">Bound At</th>
          <th style="text-align:left">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usersWithCards as $r): ?>
          <tr>
            <td><?= h($r['user_id']) ?></td>
            <td><?= h($r['username']) ?></td>
            <td><?= h($r['fullname'] ?? '') ?></td>
            <td><?= h($r['card_uid'] ?? '-') ?></td>
            <td><?= h($r['bound_at'] ?? '-') ?></td>
            <td>
              <?php if (!empty($r['card_uid'])): ?>
                <a href="api/bind_card.php?card_uid=<?= urlencode($r['card_uid']) ?>">Rebind/Transfer</a>
              <?php else: ?>-<?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>ไม่มีข้อมูลผู้ใช้</p>
  <?php endif; ?>
</section>

<section style="margin-top:24px">
  <h3>บัตรที่ยังไม่มีเจ้าของ</h3>
  <?php if (!empty($orphanCards)): ?>
    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse; width:100%">
      <thead style="background:#f2f2f2">
        <tr>
          <th style="text-align:left">Card UID</th>
          <th style="text-align:left">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orphanCards as $o): ?>
          <tr>
            <td><?= h($o['card_uid']) ?></td>
            <td><a href="api/bind_card.php?card_uid=<?= urlencode($o['card_uid']) ?>">ผูกตอนนี้</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>ไม่มีบัตรกำพร้า</p>
  <?php endif; ?>
</section>

<p><a href="/index.php">Back to Dashboard</a></p>
</body>
</html>
