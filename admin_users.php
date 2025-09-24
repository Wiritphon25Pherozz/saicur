<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// admin_users.php (modified)
// Requires web_inc/app.php
require_once __DIR__ . '/web_inc/app.php';
require_admin(); // block access if not admin

$pdo = web_get_pdo();

// Handle user management POST (optional) - keep minimal and safe
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && web_check_csrf($_POST['_csrf'] ?? '')) {
    $action = $_POST['action'];
    if ($action === 'create_user') {
        // minimal create user example (adjust to your users table)
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username !== '' && $password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // Adjust insert according to your users table structure
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            try {
                $stmt->execute([$username, $hash, 'user']);
                $msg = "User created: " . htmlspecialchars($username);
            } catch (Throwable $e) {
                $msg = "Error creating user: " . htmlspecialchars($e->getMessage());
            }
        } else {
            $msg = "Username/password required";
        }
    }
}

// Fetch existing users (if users table exists)
$users = [];
try {
    $uStmt = $pdo->query('SELECT id, username, role FROM users ORDER BY id DESC LIMIT 200');
    $users = $uStmt->fetchAll();
} catch (Throwable $ignored) {
    // If no users table, skip — page still works
    $users = [];
}

// Fetch pending requests from API
$apiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/requests_list.php?status=pending';
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
    CURLOPT_TIMEOUT => 8,
    // You can add admin session cookie automatically since it's same host
]);
$apiOut = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

$pendingRows = [];
if ($http === 200 && $apiOut) {
    $j = json_decode($apiOut, true);
    if (!empty($j['ok']) && !empty($j['rows'])) {
        $pendingRows = $j['rows'];
    }
}

$csrf = web_csrf_token();
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
<?php if ($msg): ?>
  <p style="color:green;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<section>
  <h2>Create user</h2>
  <form method="post">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="action" value="create_user">
    <label>Username: <input name="username"></label><br>
    <label>Password: <input name="password" type="password"></label><br>
    <button type="submit">Create</button>
  </form>
</section>

<section>
  <h2>Existing users</h2>
  <?php if (empty($users)): ?>
    <p>No users table or zero users.</p>
  <?php else: ?>
    <table border="1" cellpadding="6">
      <thead><tr><th>ID</th><th>Username</th><th>Role</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['id']) ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
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
      <thead><tr><th>ID</th><th>UID</th><th>Amount (Baht)</th><th>Requested</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($pendingRows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['id']) ?></td>
          <td><?= htmlspecialchars($r['uid']) ?></td>
          <td style="text-align:right;"><?= number_format((int)$r['amount']/100, 2) ?></td>
          <td><?= htmlspecialchars($r['created_at']) ?></td>
          <td>
            <!-- Approve form posts to API endpoint directly; include CSRF for safety -->
            <form method="post" action="/api/request_approve.php" style="display:inline">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="request_id" value="<?= htmlspecialchars($r['id']) ?>">
              <input type="hidden" name="admin_note" value="Approved by admin_users UI">
              <button type="submit">Approve</button>
            </form>

            <!-- Reject: we use same endpoint but with approve=0 parameter -->
            <form method="post" action="/api/request_approve.php" style="display:inline">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="request_id" value="<?= htmlspecialchars($r['id']) ?>">
              <input type="hidden" name="approve" value="0">
              <button type="submit">Reject</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

  <?php
    $UCstmt = $pdo->query("
      SELECT u.username, c.card_uid, c.is_active, c.created_at
      FROM nfc_cards c
      JOIN users u ON c.user_id = u.id
      ORDER BY c.created_at DESC
    ");

    $cards = $UCstmt->fetchAll();
  ?>
  ?>

  <h1>Registered User Cards</h1>
    <table border="1" cellpadding="8">
      <tr>
        <th>Username</th>
        <th>Card UID</th>
        <th>Status</th>
        <th>Registered At</th>
      </tr>
      <?php foreach ($cards as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['username']) ?></td>
          <td><?= htmlspecialchars($c['card_uid']) ?></td>
          <td><?= $c['is_active'] ? 'Active' : 'Inactive' ?></td>
          <td><?= $c['created_at'] ?></td>
        </tr>
      <?php endforeach; ?>
    </table>

  <?php endif; ?>
</section>

<p><a href="/index.php">Back to Dashboard</a></p>
</body>
</html>
