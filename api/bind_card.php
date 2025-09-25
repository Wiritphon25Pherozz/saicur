<?php
// api/bind_card.php
require_once __DIR__ . '/../db_config.php'; // $conn (PDO)
$message = ''; 
$prefill = isset($_GET['card_uid']) ? htmlspecialchars($_GET['card_uid']) : '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $card_uid = trim($_POST['card_uid'] ?? '');
  $username = trim($_POST['username'] ?? '');
  $force    = isset($_POST['force']);

  if ($card_uid==='' || $username==='') {
    $message = "check  Card UID, Username";
  } else {
    try {
      $conn->beginTransaction();

      // หา user
      $st = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1 FOR UPDATE");
      $st->execute([$username]);
      $user = $st->fetch(PDO::FETCH_ASSOC);
      if (!$user) { 
        $conn->rollBack(); 
        $message = "not find username"; 
      } else {
        $uid = (int)$user['id'];

        // user นี้มีบัตรอยู่แล้วไหม
        $e = $conn->prepare("SELECT id, card_uid FROM nfc_cards WHERE user_id=? FOR UPDATE");
        $e->execute([$uid]); 
        $ex = $e->fetch(PDO::FETCH_ASSOC);

        if ($ex && !$force) {
          $conn->rollBack();
          $message = "this user have card: ".htmlspecialchars($ex['card_uid'])." (ติ๊ก Replace ถ้าจะย้าย)";
        } else {
          if ($ex && $force) {
            $conn->prepare("UPDATE nfc_cards SET user_id=NULL, bound_at=NULL WHERE id=?")->execute([$ex['id']]);
          }
          // หา card row
          $c = $conn->prepare("SELECT id FROM nfc_cards WHERE card_uid=? FOR UPDATE");
          $c->execute([$card_uid]); 
          $row = $c->fetch(PDO::FETCH_ASSOC);

          if ($row) {
            $conn->prepare("UPDATE nfc_cards SET user_id=?, bound_at=NOW() WHERE id=?")
                 ->execute([$uid, $row['id']]);
          } else {
            // ถ้า schema บังคับ user_id NOT NULL ก็ insert พร้อม user_id
            $conn->prepare("INSERT INTO nfc_cards (card_uid, user_id, created_at, bound_at) VALUES (?, ?, NOW(), NOW())")
                 ->execute([$card_uid, $uid]);
          }
          $conn->commit(); 
          $message = "succuss: {$card_uid} → {$username}";
        }
      }
    } catch (Throwable $e) {
      $conn->rollBack(); 
      $message = "falid: ".$e->getMessage();
    }
  }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Bind NFC Card</title>
<style>body{font-family:sans-serif;max-width:560px;margin:24px auto}input,button{padding:8px;margin:6px 0;width:100%}.note{color:#666;font-size:13px}</style>
</head><body>
<h2>join NFC, user</h2>
<?php if($message) echo "<p><strong>".htmlspecialchars($message)."</strong></p>"; ?>
<form method="post">
  <label>Card UID</label><input name="card_uid" required value="<?= $prefill ?>">
  <label>Username</label><input name="username" required placeholder="USER">
  <label><input type="checkbox" name="force"> Replace existing card for this user</label>
  <button type="submit">ผูกบัตร</button>
</form>
<p class="note">ถ้าแตะบัตรแล้วระบบแจ้ง need_bind ให้เอา UID นั้นมาใส่ที่นี่เพื่อผูก</p>
</body></html>
