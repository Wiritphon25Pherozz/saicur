<?php
// /web_admin/admin_requests.php
require_once __DIR__ . '/../web_inc/app.php'; // if you created it earlier
require_admin(); // from web_inc/app.php

// fetch pending requests via internal call
$apiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/requests_list.php?status=pending';
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
    CURLOPT_TIMEOUT => 8,
]);
$out = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

$rows = [];
if ($http === 200 && $out) {
    $j = json_decode($out, true);
    if (!empty($j['ok'])) $rows = $j['rows'];
}

// Approve form handling (this page posts to /api/request_approve.php directly)
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin - Requests</title>
<link rel="stylesheet" href="/style.css"></head><body>
<h1>Pending Top-up Requests</h1>
<?php if (empty($rows)): ?>
  <p>No pending requests.</p>
<?php else: ?>
  <table border="1" cellpadding="6">
    <thead><tr><th>ID</th><th>UID</th><th>Amount (baht)</th><th>Requested</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['id']) ?></td>
        <td><?= htmlspecialchars($r['uid']) ?></td>
        <td><?= number_format($r['amount']/100,2) ?></td>
        <td><?= htmlspecialchars($r['created_at']) ?></td>
        <td>
          <form method="post" action="/api/request_approve.php" style="display:inline">
            <input type="hidden" name="request_id" value="<?= htmlspecialchars($r['id']) ?>">
            <input type="hidden" name="admin_note" value="Approved via admin UI">
            <button type="submit">Approve</button>
          </form>
          <form method="post" action="/api/request_approve.php" style="display:inline">
            <input type="hidden" name="request_id" value="<?= htmlspecialchars($r['id']) ?>">
            <input type="hidden" name="approve" value="0">
            <button type="submit">Reject</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<p><a href="/index.php">Back</a></p>
</body></html>
