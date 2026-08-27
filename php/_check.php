<?php
/**
 * Deployment check — upload this, open it in a browser, then DELETE IT.
 *
 * It verifies everything the site needs from the server before you go
 * looking for bugs in the site itself: PHP version, the two extensions
 * used, whether the enquiry log can actually be written, and whether the
 * files that must not be public are in fact blocked.
 *
 * It never prints the API key, only whether one is present.
 *
 * DELETE THIS FILE once the checks pass. It reveals server details that
 * are nobody else's business.
 */

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$results = [];

function check(string $label, bool $ok, string $detail = '', bool $fatal = true): void
{
    global $results;
    $results[] = ['label' => $label, 'ok' => $ok, 'detail' => $detail, 'fatal' => $fatal];
}

/* ---- PHP itself ---- */
check(
    'PHP 8.0 or newer',
    PHP_VERSION_ID >= 80000,
    'found ' . PHP_VERSION
);

check(
    'cURL extension (sends the emails)',
    function_exists('curl_init'),
    function_exists('curl_init') ? 'available' : 'MISSING — no email can be sent'
);

check(
    'mbstring extension',
    function_exists('mb_strlen'),
    function_exists('mb_strlen') ? 'available' : 'missing — falls back to byte length, not fatal',
    false
);

/* ---- Files that must be present ---- */
foreach ([
    'index.php' => 'the page itself',
    'enquiry.php' => 'the form handler',
    'inc/content.php' => 'all site copy',
    'assets/css/styles.css' => 'compiled stylesheet',
    'assets/js/app.js' => 'all interactivity',
    '.htaccess' => 'security headers and config blocking',
    'storage/.htaccess' => 'blocks public access to customer data',
] as $file => $why) {
    check(
        'File present: ' . $file,
        is_file(__DIR__ . '/' . $file),
        is_file(__DIR__ . '/' . $file) ? $why : 'MISSING — ' . $why
    );
}

/* Dotfiles are the ones FTP clients silently skip, so call it out loudly. */
if (!is_file(__DIR__ . '/storage/.htaccess')) {
    check(
        'storage/.htaccess uploaded',
        false,
        'Without this, stored customer names, emails and phone numbers are '
        . 'downloadable by anyone who guesses the URL. Many FTP clients hide '
        . 'dotfiles by default — turn on "show hidden files" and upload it.'
    );
}

/* ---- Storage ---- */
$dir = __DIR__ . '/storage';
$dirOk = is_dir($dir);
check('storage/ exists', $dirOk, $dirOk ? '' : 'create it');

if ($dirOk) {
    $probe = $dir . '/.write-test';
    $written = @file_put_contents($probe, 'test') !== false;
    if ($written) {
        @unlink($probe);
    }
    check(
        'storage/ is writable',
        $written,
        $written
            ? 'enquiries can be logged'
            : 'CHMOD it to 755 (or 775). Every enquiry is written here before '
              . 'any email is sent — if this fails, a mail outage loses customers.'
    );
}

/* ---- Config ---- */
$configPath = __DIR__ . '/config.php';
$hasConfig = is_file($configPath);
check(
    'config.php present',
    $hasConfig,
    $hasConfig ? '' : 'copy config.sample.php to config.php — without it the form still stores enquiries but sends no email',
    false
);

if ($hasConfig) {
    $cfg = require $configPath;
    $key = is_array($cfg) ? (string) ($cfg['resend_api_key'] ?? '') : '';
    check(
        'Resend API key set',
        $key !== '',
        $key !== ''
            // Never print the key. Length and prefix are enough to spot a paste error.
            ? 'present (' . substr($key, 0, 3) . '…, ' . strlen($key) . ' chars)'
            : 'empty — no email will be sent',
        false
    );

    $from = is_array($cfg) ? (string) ($cfg['from'] ?? '') : '';
    check(
        'Sender is a verified domain',
        $from !== '' && !str_contains($from, 'resend.dev'),
        str_contains($from, 'resend.dev')
            ? 'still using Resend’s sandbox sender — it only delivers to the address '
              . 'the Resend account was registered with and returns 403 for anyone else. '
              . 'Verify dpxgolf.co.uk in Resend, then change "from".'
            : $from,
        false
    );
}

/* ---- Admin dashboard ---- */
if ($hasConfig) {
    $cfg = require $configPath;
    $adminHash = is_array($cfg) ? (string) ($cfg['admin_password_hash'] ?? '') : '';
    check(
        'Admin password set',
        $adminHash !== '',
        $adminHash !== ''
            ? 'the dashboard can be logged into'
            : 'admin/setup.php will generate a hash to paste into config.php',
        false
    );
}

check(
    'admin/setup.php removed',
    !is_file(__DIR__ . '/admin/setup.php'),
    is_file(__DIR__ . '/admin/setup.php')
        ? 'still present — delete it once the password is set'
        : 'gone, as it should be',
    false
);

check(
    'admin/inc/.htaccess uploaded',
    is_file(__DIR__ . '/admin/inc/.htaccess'),
    is_file(__DIR__ . '/admin/inc/.htaccess')
        ? 'admin includes are blocked from direct access'
        : 'MISSING — another dotfile FTP clients like to skip',
    false
);

/* ---- Outbound connectivity ---- */
if (function_exists('curl_init')) {
    $ch = curl_init('https://api.resend.com');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_NOBODY => true,
    ]);
    $reached = curl_exec($ch) !== false;
    $err = curl_error($ch);
    curl_close($ch);

    check(
        'Can reach api.resend.com',
        $reached,
        $reached ? 'outbound HTTPS works' : 'blocked: ' . $err . ' — some shared hosts firewall outbound requests',
        false
    );
}

$fatalFails = 0;
$warnings = 0;
foreach ($results as $r) {
    if (!$r['ok']) {
        $r['fatal'] ? $fatalFails++ : $warnings++;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DPX Golf — deployment check</title>
<style>
  body { font: 15px/1.6 -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
         max-width: 760px; margin: 40px auto; padding: 0 20px; color: #1a1a1a; }
  h1 { font-size: 22px; margin: 0 0 4px; }
  .sub { color: #666; margin: 0 0 28px; }
  ul { list-style: none; padding: 0; margin: 0 0 28px; }
  li { padding: 10px 14px; border-radius: 8px; margin-bottom: 6px; background: #f6f6f4; }
  li.bad { background: #fdeaea; }
  li.warn { background: #fff6e5; }
  .mark { font-weight: 700; margin-right: 8px; }
  .ok .mark { color: #2b7a2b; }
  .bad .mark { color: #b32020; }
  .warn .mark { color: #9a6700; }
  .detail { display: block; color: #555; font-size: 13px; margin-top: 2px; }
  .banner { padding: 16px 18px; border-radius: 10px; margin-bottom: 28px; font-weight: 600; }
  .banner.good { background: #e6f4e6; color: #1d5c1d; }
  .banner.bad { background: #fdeaea; color: #8c1c1c; }
  code { background: #eceae4; padding: 1px 5px; border-radius: 4px; font-size: 13px; }
</style>
</head>
<body>
<h1>DPX Golf — deployment check</h1>
<p class="sub">Delete this file once everything passes.</p>

<?php if ($fatalFails === 0): ?>
  <div class="banner good">
    All required checks passed<?= $warnings ? ' — ' . $warnings . ' warning(s) below' : '' ?>.
    Open <code>index.php</code>, send a test enquiry, then delete <code>_check.php</code>.
  </div>
<?php else: ?>
  <div class="banner bad">
    <?= $fatalFails ?> required check(s) failed. The site will not work correctly until these are fixed.
  </div>
<?php endif; ?>

<ul>
<?php foreach ($results as $r):
    $cls = $r['ok'] ? 'ok' : ($r['fatal'] ? 'bad' : 'warn');
    $mark = $r['ok'] ? '✓' : ($r['fatal'] ? '✕' : '!'); ?>
  <li class="<?= $cls ?>">
    <span class="mark"><?= $mark ?></span>
    <?= htmlspecialchars($r['label'], ENT_QUOTES, 'UTF-8') ?>
    <?php if ($r['detail'] !== ''): ?>
      <span class="detail"><?= htmlspecialchars($r['detail'], ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>

<p class="sub">
  Document root: <code><?= htmlspecialchars(__DIR__, ENT_QUOTES, 'UTF-8') ?></code>
</p>
</body>
</html>
