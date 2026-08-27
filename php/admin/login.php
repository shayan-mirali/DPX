<?php
/**
 * Admin login.
 *
 * Deliberately vague on failure — "that password isn't right" tells an
 * attacker nothing they didn't already know, and there is no username to
 * enumerate.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

admin_start_session();

if (admin_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$configured = admin_password_hash() !== '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf_check();

    if (!$configured) {
        $error = 'No admin password is set yet. See the note below.';
    } elseif (admin_login_blocked()) {
        $error = 'Too many attempts. Wait fifteen minutes and try again.';
    } elseif (admin_login(post_str_login('password'))) {
        header('Location: index.php');
        exit;
    } else {
        $error = "That password isn't right.";
    }
}

function post_str_login(string $k): string
{
    $v = $_POST[$k] ?? '';
    return is_string($v) ? $v : '';
}
?>
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Sign in · <?= e(SITE['name']) ?> admin</title>
<link rel="icon" href="../assets/img/icon.png" type="image/png">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="login-wrap">
  <form class="login" method="post" action="login.php">
    <img src="../assets/img/dpx-bone.png" alt="<?= e(SITE['name']) ?>">

    <?php if ($error !== ''): ?>
      <p class="flash err"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if (!$configured): ?>
      <p class="flash err">No admin password has been set.</p>
      <p class="hint">
        Open <code>setup.php</code> in this folder to turn a password of your
        choosing into a hash, paste that into <code>config.php</code>, then
        delete <code>setup.php</code>.
      </p>
    <?php else: ?>
      <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>">
      <label class="field" for="pw">
        <span class="lab">Password</span>
        <input type="password" id="pw" name="password" autocomplete="current-password" autofocus required>
      </label>
      <button type="submit" class="btn">Sign in</button>
    <?php endif; ?>
  </form>
</div>
</body>
</html>
