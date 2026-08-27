<?php
/**
 * Password hash generator — run once, then delete.
 *
 * Type a password, get the hash to paste into config.php. It deliberately
 * does NOT write config.php itself: a page that can set the admin
 * password is a page that can take the dashboard over, and it would be
 * reachable by anyone who found the URL.
 *
 * Because it only prints a hash of what you typed, leaving it in place
 * gives an attacker nothing — they still need file access to use it. Even
 * so, delete it once you're in.
 */

declare(strict_types=1);

require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/inc/auth.php';

$hash = '';
$tooShort = false;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $pw = $_POST['password'] ?? '';
    $pw = is_string($pw) ? $pw : '';

    if (strlen($pw) < 12) {
        $tooShort = true;
    } else {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
    }
}

$alreadySet = admin_password_hash() !== '';
?>
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Set the admin password</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="login-wrap">
  <div class="login" style="max-width:560px">
    <h1 style="font-size:20px">Set the admin password</h1>
    <p class="lede" style="margin-bottom:20px">
      This page turns a password into a hash. Nothing is saved here — you paste
      the result into <code>config.php</code> yourself.
    </p>

    <?php if ($alreadySet): ?>
      <p class="flash ok">A password is already configured. Only continue if you want to change it.</p>
    <?php endif; ?>

    <?php if ($tooShort): ?>
      <p class="flash err">Use at least 12 characters. This is the only lock on the dashboard.</p>
    <?php endif; ?>

    <form method="post" action="setup.php">
      <label class="field" for="pw">
        <span class="lab">New password</span>
        <input type="password" id="pw" name="password" autocomplete="new-password" autofocus required>
        <span class="hint">
          At least 12 characters. A few unrelated words is both stronger and
          easier to remember than something short with punctuation in it.
        </span>
      </label>
      <button type="submit" class="btn">Generate hash</button>
    </form>

    <?php if ($hash !== ''): ?>
      <hr style="border:0;border-top:1px solid var(--line);margin:26px 0">
      <p class="lab">Paste this into config.php</p>
      <code style="display:block;padding:12px;word-break:break-all;margin-bottom:14px"><?= e($hash) ?></code>
      <p class="hint">
        In <code>config.php</code>, set:<br>
        <code style="display:block;margin-top:8px">'admin_password_hash' =&gt; '<?= e($hash) ?>',</code>
      </p>
      <p class="hint" style="margin-top:16px">
        Then <strong>delete setup.php</strong> and sign in at
        <a href="login.php">login.php</a>.
      </p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
