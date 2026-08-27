<?php
/**
 * Reading and writing the content overrides.
 *
 * The dashboard never rewrites PHP. It writes JSON to
 * storage/content.json, which inc/content.php merges over the shipped
 * defaults. The worst a bad save can do is put wrong words on the page —
 * it cannot produce a syntax error, and deleting the file undoes
 * everything at once.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/content.php';

/** @return array<string,mixed> the saved overrides only, not the merged result */
function admin_overrides(): array
{
    $path = content_overrides_path();
    if (!is_file($path)) {
        return [];
    }
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Replace one top-level section of the overrides and save.
 *
 * Writes to a temporary file and renames it into place. rename() is
 * atomic on the same filesystem, so a visitor mid-request either sees the
 * old file or the new one, never a half-written one.
 *
 * @param array<mixed> $value
 */
function admin_save_section(string $key, array $value): bool
{
    $all = admin_overrides();
    $all[$key] = $value;
    return admin_write_overrides($all);
}

/** @param array<string,mixed> $all */
function admin_write_overrides(array $all): bool
{
    $path = content_overrides_path();
    $dir = dirname($path);

    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
        return false;
    }

    $json = json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    $tmp = $path . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
        return false;
    }

    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        return false;
    }

    /* A backup per save, capped. Someone will eventually paste over the
     * pricing table and want yesterday's back. */
    admin_snapshot($json);

    return true;
}

const SNAPSHOT_KEEP = 20;

function admin_snapshot_dir(): string
{
    return __DIR__ . '/../../storage/backups';
}

function admin_snapshot(string $json): void
{
    $dir = admin_snapshot_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
        return;
    }

    @file_put_contents($dir . '/content-' . gmdate('Ymd-His') . '.json', $json);

    $files = glob($dir . '/content-*.json') ?: [];
    sort($files);
    while (count($files) > SNAPSHOT_KEEP) {
        @unlink(array_shift($files));
    }
}

/** Discard every override and return to the shipped content. */
function admin_reset_all(): bool
{
    $path = content_overrides_path();
    if (!is_file($path)) {
        return true;
    }
    // Snapshot first, so "reset everything" is itself undoable.
    $raw = @file_get_contents($path);
    if (is_string($raw) && $raw !== '') {
        admin_snapshot($raw);
    }
    return @unlink($path);
}

/** Has this section been changed from the shipped default? */
function admin_section_is_overridden(string $key): bool
{
    return array_key_exists($key, admin_overrides());
}

/* ---- Enquiries ---------------------------------------------------- */

function admin_enquiries_path(): string
{
    return __DIR__ . '/../../storage/enquiries.jsonl';
}

/**
 * Read the enquiry log, newest first.
 *
 * One JSON object per line. A malformed line is skipped rather than
 * throwing — a single bad row must not hide every other enquiry.
 *
 * @return array<int,array<string,mixed>>
 */
function admin_enquiries(): array
{
    $path = admin_enquiries_path();
    if (!is_file($path)) {
        return [];
    }

    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return [];
    }

    $out = [];
    foreach ($lines as $line) {
        $row = json_decode($line, true);
        if (is_array($row)) {
            /* Identify a row by a hash of its exact stored line. The log has
             * no ids and predates this feature, so deriving one costs nothing
             * and works for rows written before delete existed. */
            $row['_id'] = sha1($line);
            $out[] = $row;
        }
    }

    return array_reverse($out);
}

/**
 * Delete one enquiry, identified by the hash from admin_enquiries().
 *
 * The row is copied to storage/backups/deleted-enquiries.jsonl before it
 * goes. These are customer records: a confirmed delete should still be
 * recoverable by whoever runs the server, even though the person doing
 * the deleting meant it.
 *
 * Exactly one line is removed, even in the vanishingly unlikely case of
 * two byte-identical submissions.
 */
function admin_delete_enquiry(string $id): bool
{
    $path = admin_enquiries_path();
    if ($id === '' || !is_file($path)) {
        return false;
    }

    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return false;
    }

    $kept = [];
    $removed = null;
    foreach ($lines as $line) {
        if ($removed === null && sha1($line) === $id) {
            $removed = $line;
            continue;
        }
        $kept[] = $line;
    }

    if ($removed === null) {
        return false; // already gone, or someone else deleted it first
    }

    $dir = admin_snapshot_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents(
        $dir . '/deleted-enquiries.jsonl',
        rtrim($removed) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );

    // Same temp-then-rename dance as the content file, for the same reason.
    $tmp = $path . '.tmp';
    $body = $kept ? implode(PHP_EOL, $kept) . PHP_EOL : '';
    if (@file_put_contents($tmp, $body, LOCK_EX) === false) {
        return false;
    }
    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        return false;
    }

    return true;
}

/* ---- Form helpers -------------------------------------------------- */

/** Trimmed POST string. */
function post_str(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

/**
 * A POST array of trimmed strings, e.g. name="points[]".
 *
 * @return string[]
 */
function post_list(string $key): array
{
    $v = $_POST[$key] ?? [];
    if (!is_array($v)) {
        return [];
    }
    $out = [];
    foreach ($v as $item) {
        if (is_string($item)) {
            $out[] = trim($item);
        }
    }
    return $out;
}

/** Drop empty strings — an emptied input means "remove this line". */
function compact_list(array $items): array
{
    return array_values(array_filter($items, static fn($s): bool => $s !== ''));
}

/** Flash a message across the redirect that follows a save. */
function admin_flash(string $msg, string $kind = 'ok'): void
{
    admin_start_session();
    $_SESSION['flash'] = ['msg' => $msg, 'kind' => $kind];
}

/** @return array{msg:string,kind:string}|null */
function admin_take_flash(): ?array
{
    admin_start_session();
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($f) ? $f : null;
}
