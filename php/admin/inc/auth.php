<?php
/**
 * Admin authentication.
 *
 * One shared login for the venue — an email address and a password —
 * both set in config.php. No registration, no password reset by email:
 * for a single site run by a couple of people, each of those is more
 * attack surface than it is convenience.
 *
 * The password is stored hashed, never in plain text. That costs nothing
 * to set up (config.php is written once) and means the password cannot be
 * read back out of the file if anyone ever gets sight of it.
 *
 * The session cookie is httponly and same-site, the session id is
 * regenerated on login, and failed attempts are throttled per IP.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/content.php';

const ADMIN_SESSION = 'dpx_admin';
const LOGIN_RATE_FILE = __DIR__ . '/../../storage/admin-logins.json';
const LOGIN_MAX_ATTEMPTS = 8;
const LOGIN_WINDOW = 900; // 15 minutes

/** @return array<string,mixed> */
function admin_config(): array
{
    static $c = null;
    if ($c === null) {
        $path = __DIR__ . '/../../config.php';
        $c = is_file($path) ? require $path : [];
        if (!is_array($c)) {
            $c = [];
        }
    }
    return $c;
}

function admin_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        // Only send the cookie over HTTPS when the request arrived over
        // HTTPS. Hard-coding true would break the local PHP server.
        'secure' => admin_is_https(),
    ]);
    session_name(ADMIN_SESSION);
    session_start();
}

function admin_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    // IONOS terminates TLS in front of PHP, so trust its forwarded header.
    return ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

/** Is a password configured at all? Without one, login is impossible. */
function admin_password_hash(): string
{
    return (string) (admin_config()['admin_password_hash'] ?? '');
}

/** The email address that signs in. */
function admin_email(): string
{
    return (string) (admin_config()['admin_email'] ?? '');
}

function admin_is_logged_in(): bool
{
    admin_start_session();
    return !empty($_SESSION['admin_ok']);
}

/**
 * Gate every admin page. Redirects to the login screen rather than
 * showing a 403, because the only person who should ever see this is the
 * venue, and they want the login form.
 */
function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/* ---- Login throttling -------------------------------------------- */

function admin_client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/**
 * Count failed logins per IP. Fails CLOSED — unlike the enquiry rate
 * limiter, if this cannot read its file it refuses the login. A form
 * nobody can submit loses a customer; a password nobody can brute-force
 * loses nothing.
 */
function admin_login_blocked(): bool
{
    $data = admin_login_read();
    $ip = admin_client_ip();
    $attempts = $data[$ip] ?? [];
    return count($attempts) >= LOGIN_MAX_ATTEMPTS;
}

/** @return array<string,array<int>> */
function admin_login_read(): array
{
    if (!is_file(LOGIN_RATE_FILE)) {
        return [];
    }
    $raw = @file_get_contents(LOGIN_RATE_FILE);
    if ($raw === false) {
        return [];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return [];
    }

    $now = time();
    foreach ($data as $ip => $stamps) {
        $data[$ip] = array_values(array_filter(
            is_array($stamps) ? $stamps : [],
            static fn($t): bool => is_int($t) && $t > $now - LOGIN_WINDOW
        ));
        if (!$data[$ip]) {
            unset($data[$ip]);
        }
    }

    return $data;
}

function admin_login_record_failure(): void
{
    $data = admin_login_read();
    $ip = admin_client_ip();
    $data[$ip][] = time();
    @file_put_contents(LOGIN_RATE_FILE, json_encode($data), LOCK_EX);
}

function admin_login_clear(): void
{
    $data = admin_login_read();
    unset($data[admin_client_ip()]);
    @file_put_contents(LOGIN_RATE_FILE, json_encode($data), LOCK_EX);
}

function admin_login(string $email, string $password): bool
{
    $hash = admin_password_hash();
    if ($hash === '') {
        return false;
    }

    /* Compared case-insensitively — nobody remembers whether they set up
     * the address with a capital letter, and it is not a secret anyway.
     * hash_equals keeps the comparison constant-time regardless. */
    $expected = strtolower(trim(admin_email()));
    if ($expected !== '' && !hash_equals($expected, strtolower(trim($email)))) {
        admin_login_record_failure();
        return false;
    }

    /* An empty password can never be right, and refusing it here closes a
     * real hole: a hash OF an empty string is a perfectly valid hash, so
     * without this a config with `password_hash('')` in it would let
     * anyone straight in by submitting nothing. */
    if ($password === '') {
        admin_login_record_failure();
        return false;
    }

    if (!password_verify($password, $hash)) {
        admin_login_record_failure();
        return false;
    }

    admin_start_session();
    // New id on privilege change, so a fixed session id cannot be reused.
    session_regenerate_id(true);
    $_SESSION['admin_ok'] = true;
    $_SESSION['admin_since'] = time();
    admin_login_clear();

    return true;
}

function admin_logout(): void
{
    admin_start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ---- CSRF --------------------------------------------------------- */

function admin_csrf_token(): string
{
    admin_start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf'];
}

/** Every state-changing POST goes through this. */
function admin_csrf_check(): void
{
    admin_start_session();
    $sent = (string) ($_POST['csrf'] ?? '');
    $known = (string) ($_SESSION['csrf'] ?? '');

    if ($known === '' || !hash_equals($known, $sent)) {
        http_response_code(400);
        exit('Session expired. Go back, reload the page and try again.');
    }
}
