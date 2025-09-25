<?php
// nfc_simulate.php

?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Top-up Request</title>
    <style>
      body { font-family: sans-serif; max-width: 560px; margin: 24px auto; }
      input, button, select, textarea { padding:8px; margin:6px 0; width:100%; box-sizing:border-box; }
      .note { color:#666; font-size: 13px; }
      .warn { color:#b00; }
      .ok { color:#0a0; }
      .card { border:1px solid #ddd; padding:12px; border-radius:8px; }
    </style>
  </head>
  <body>
    <h2>TOPUP (Username)</h2>
    <div class="card">
      <form method="post" action="api/ingest_topup_request.php">
        <label>Username</label>
        <input name="username" required placeholder="milk123">

        <label>Amount</label>
        <input name="amount" type="number" min="1" step="1" required placeholder="amount (bhat)">

        <label>note</label>
        <input name="note" placeholder="optional">

        <button type="submit">request</button>
      </form>
    </div>

    <p class="note">
      The system will search for your card using the username you provided.
If you haven't linked your card to your account yet, go to <code>api/bind_card.php</code>
    </p>
  </body>
</html>
