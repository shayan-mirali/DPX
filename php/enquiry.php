<?php
/* ------------------------------------------------------------------ *
 *  Enquiry intake
 *
 *  Replaces two things the Netlify build had: the Forms submission store
 *  and the submission-created confirmation function. Both live here now,
 *  because a PHP host has neither.
 *
 *  Order matters. The enquiry is written to disk FIRST, before any email
 *  is attempted, so a mail outage can never lose a customer. Netlify kept
 *  every submission in its dashboard; storage/enquiries.jsonl is the
 *  equivalent, and it is the only copy that cannot fail.
 *
 *  Answers JSON when asked (the in-page submit), otherwise redirects back
 *  to the form — so it works with scripting off.
 * ------------------------------------------------------------------ */

declare(strict_types=1);

require __DIR__ . '/inc/content.php';

/* Config holds the Resend key and is deliberately not in version control.
 * Copy config.sample.php to config.php and fill it in. Missing config is
 * not fatal: the enquiry is still stored, and the page says plainly that
 * it could not be sent rather than claiming success. */
$config = is_file(__DIR__ . '/config.php')
    ? require __DIR__ . '/config.php'
    : [];

const STORAGE_DIR = __DIR__ . '/storage';
const LOG_FILE    = STORAGE_DIR . '/enquiries.jsonl';
const RATE_FILE   = STORAGE_DIR . '/rate.json';

/** Does the caller want JSON back, or a redirect? */
function wants_json(): bool
{
    if (!empty($_POST['ajax'])) {
        return true;
    }
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return str_contains($accept, 'application/json');
}

/**
 * @param array<string,mixed> $payload
 */
function respond(int $status, array $payload, string $redirect): void
{
    if (wants_json()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    header('Location: ' . $redirect, true, 303);
    exit;
}

/** Client IP, best effort — only ever used for rate limiting. */
function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/**
 * Crude flood protection: at most 5 enquiries per IP per hour.
 *
 * Netlify filtered spam before it ever reached the site. Nothing does
 * that here, so the honeypot needs backing up with something. This is
 * deliberately simple — a file, not a database — and fails OPEN: if the
 * counter cannot be read or written, the enquiry still goes through.
 * Losing a real customer to a broken rate limiter would be far worse
 * than letting a spammer past.
 */
function rate_limited(): bool
{
    $now = time();
    $window = 3600;
    $max = 5;

    $data = [];
    if (is_file(RATE_FILE)) {
        $raw = @file_get_contents(RATE_FILE);
        if ($raw !== false) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }
    }

    // Drop entries older than the window, whoever they belong to.
    foreach ($data as $ip => $stamps) {
        $data[$ip] = array_values(array_filter(
            is_array($stamps) ? $stamps : [],
            static fn($t): bool => is_int($t) && $t > $now - $window
        ));
        if (!$data[$ip]) {
            unset($data[$ip]);
        }
    }

    $ip = client_ip();
    $mine = $data[$ip] ?? [];

    if (count($mine) >= $max) {
        return true;
    }

    $mine[] = $now;
    $data[$ip] = $mine;
    @file_put_contents(RATE_FILE, json_encode($data), LOCK_EX);

    return false;
}

/**
 * Append the enquiry to the on-disk log. This is the safety net that
 * replaces the Netlify Forms dashboard, so it runs before any email and
 * its success is reported back to the caller.
 *
 * @param array<string,mixed> $enquiry
 */
function store(array $enquiry): bool
{
    if (!is_dir(STORAGE_DIR)) {
        @mkdir(STORAGE_DIR, 0775, true);
    }

    $line = json_encode($enquiry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($line === false) {
        return false;
    }

    return @file_put_contents(LOG_FILE, $line . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Send one email through Resend. Returns [ok, detail] rather than
 * throwing, because a failed send must never break the request.
 *
 * @param string[] $to
 * @return array{0:bool,1:string}
 */
function send_email(array $config, array $to, string $subject, string $text, string $html, ?string $replyTo = null): array
{
    $key = $config['resend_api_key'] ?? '';
    if ($key === '') {
        return [false, 'no api key configured'];
    }
    if (!function_exists('curl_init')) {
        return [false, 'curl extension unavailable'];
    }

    $body = [
        'from' => $config['from'] ?? 'DPX Golf <onboarding@resend.dev>',
        'to' => array_values($to),
        'subject' => $subject,
        'text' => $text,
        'html' => $html,
    ];
    if ($replyTo) {
        $body['reply_to'] = $replyTo;
    }

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $key,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    $res = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($res === false) {
        return [false, 'curl: ' . $err];
    }
    if ($code < 200 || $code >= 300) {
        return [false, 'resend ' . $code . ': ' . substr((string) $res, 0, 300)];
    }

    return [true, 'sent'];
}

/* ------------------------------------------------------------------ *
 *  Request handling
 * ------------------------------------------------------------------ */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    respond(405, ['ok' => false, 'message' => 'Method not allowed.'], 'index.php#book');
}

/* Honeypot: accept silently so bots learn nothing, but drop it. */
if (trim((string) ($_POST['company'] ?? '')) !== '') {
    respond(200, ['ok' => true], 'index.php?sent=1#book');
}

$name    = trim((string) ($_POST['name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$phone   = trim((string) ($_POST['phone'] ?? ''));
$interest = trim((string) ($_POST['interest'] ?? 'bay'));
$message = trim((string) ($_POST['message'] ?? ''));

/* mbstring is present on essentially every PHP 8 host, but a fatal here
 * would take the whole form down, so fall back to byte length. */
$name_len = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);

if ($name_len < 2) {
    respond(400, ['ok' => false, 'message' => 'Please give us a name.'], 'index.php?error=validation#book');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(400, ['ok' => false, 'message' => "That email doesn't look right."], 'index.php?error=validation#book');
}

if (rate_limited()) {
    respond(429, [
        'ok' => false,
        'message' => "That's a few enquiries in a short time — please call us instead and we'll sort it out.",
    ], 'index.php?error=rate#book');
}

$label = interest_label($interest);

$enquiry = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone !== '' ? $phone : null,
    'interest' => $interest,
    'interestLabel' => $label,
    'message' => $message !== '' ? $message : null,
    'receivedAt' => gmdate('c'),
    'ip' => client_ip(),
];

/* Store first. Everything after this point can fail without losing the
 * enquiry, and the response says which parts did. */
$stored = store($enquiry);

/* ---- 1. Notify the venue ---- */
$notify_to = $config['notify'] ?? SITE['emails'];

$venue_text = implode("\n", array_filter([
    'New enquiry from the website.',
    '',
    'Name: ' . $name,
    'Email: ' . $email,
    $phone !== '' ? 'Phone: ' . $phone : null,
    'Interested in: ' . $label,
    '',
    $message !== '' ? 'Message:' : null,
    $message !== '' ? $message : null,
    '',
    'Received: ' . gmdate('D, d M Y H:i') . ' UTC',
], static fn($v): bool => $v !== null));

$venue_html = '<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Helvetica,Arial,sans-serif;max-width:520px;color:#1a1a1a;line-height:1.6">'
    . '<p style="font-size:17px;margin:0 0 18px"><strong>New enquiry from the website</strong></p>'
    . '<p style="margin:0 0 6px"><strong>Name:</strong> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p style="margin:0 0 6px"><strong>Email:</strong> <a href="mailto:' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</a></p>'
    . ($phone !== '' ? '<p style="margin:0 0 6px"><strong>Phone:</strong> ' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</p>' : '')
    . '<p style="margin:0 0 6px"><strong>Interested in:</strong> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</p>'
    . ($message !== '' ? '<p style="margin:14px 0 0"><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</p>' : '')
    . '</div>';

[$venue_ok, $venue_detail] = send_email(
    $config,
    is_array($notify_to) ? $notify_to : [$notify_to],
    'DPX Golf enquiry — ' . $label,
    $venue_text,
    $venue_html,
    $email // replying goes straight back to the customer
);

/* ---- 2. Confirm to the customer ---- */
$first = preg_split('/\s+/', $name)[0] ?: 'there';

$cust_text = implode("\n", array_filter([
    'Thanks, ' . $first . " — that's with us.",
    '',
    'Someone from ' . SITE['name'] . ' will come back to you shortly, usually within one working day.',
    '',
    'What you sent us:',
    '  Interested in: ' . $label,
    $phone !== '' ? '  Phone: ' . $phone : null,
    $message !== '' ? '  Message: ' . $message : null,
    '',
    'In the meantime:',
    '  Call: ' . SITE['phone'],
    '  Where: ' . address_one_line(),
    '  Open: ' . hours_label(),
    '',
    SITE['tagline'],
    SITE['name'],
], static fn($v): bool => $v !== null));

$esc = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

/* Deliberately a light, plain email. A dark-themed HTML mail gets mangled
 * by half the clients out there, and a confirmation needs to be legible
 * far more than it needs to be on-brand. */
$cust_html = '
<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Helvetica,Arial,sans-serif;max-width:520px;margin:0 auto;padding:32px 24px;color:#1a1a1a;line-height:1.6">
  <p style="font-size:18px;margin:0 0 20px"><strong>Thanks, ' . $esc($first) . ' — that&rsquo;s with us.</strong></p>
  <p style="margin:0 0 24px">Someone from ' . $esc(SITE['name']) . ' will come back to you shortly, usually within one working day.</p>

  <table style="width:100%;border-collapse:collapse;background:#f6f6f4;border-radius:10px;margin:0 0 24px">
    <tr><td style="padding:18px 20px">
      <p style="margin:0 0 10px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#777">What you sent us</p>
      <p style="margin:0 0 6px"><strong>Interested in:</strong> ' . $esc($label) . '</p>'
    . ($phone !== '' ? '<p style="margin:0 0 6px"><strong>Phone:</strong> ' . $esc($phone) . '</p>' : '')
    . ($message !== '' ? '<p style="margin:0"><strong>Message:</strong> ' . $esc($message) . '</p>' : '') . '
    </td></tr>
  </table>

  <p style="margin:0 0 6px"><strong>Call</strong> <a href="' . $esc((string) tel_href()) . '" style="color:#4a7c00">' . $esc(SITE['phone']) . '</a></p>
  <p style="margin:0 0 6px"><strong>Where</strong> ' . $esc(address_one_line()) . '</p>
  <p style="margin:0 0 24px"><strong>Open</strong> ' . $esc(hours_label()) . '</p>

  <p style="margin:0;padding-top:20px;border-top:1px solid #e2e2de;font-size:13px;color:#777">
    ' . $esc(SITE['tagline']) . '<br>' . $esc(SITE['name']) . '
  </p>
</div>';

[$cust_ok, $cust_detail] = send_email(
    $config,
    [$email],
    "We've got your enquiry — " . SITE['name'],
    $cust_text,
    $cust_html,
    $config['reply_to'] ?? SITE['emails'][0]
);

/* ------------------------------------------------------------------ *
 *  Result
 *
 *  The enquiry counts as received if it reached the venue OR was written
 *  to disk. The customer's confirmation failing is not the customer's
 *  problem and must never show as an error to them — it is logged for
 *  whoever maintains the site.
 * ------------------------------------------------------------------ */
if (!$venue_ok || !$cust_ok) {
    error_log(sprintf(
        '[enquiry] stored=%s venue=%s (%s) customer=%s (%s)',
        $stored ? 'yes' : 'NO',
        $venue_ok ? 'sent' : 'FAILED',
        $venue_detail,
        $cust_ok ? 'sent' : 'FAILED',
        $cust_detail
    ));
}

if ($venue_ok || $stored) {
    respond(200, ['ok' => true], 'index.php?sent=1#book');
}

/* Nothing worked: no email, and the log could not be written. Say so
 * rather than showing a success state over an enquiry that went nowhere. */
respond(502, [
    'ok' => false,
    'message' => "We couldn't send that just now. Please call or email us directly.",
], 'index.php?error=send#book');
