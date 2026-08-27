<?php
/**
 * Site content — defaults plus whatever the admin dashboard has changed.
 *
 * The shipped copy lives in inc/defaults.php. Admin edits are written to
 * storage/content.json and merged over the top here, which keeps the two
 * strictly separate: the dashboard never rewrites PHP, so a bad edit can
 * produce wrong words but never a syntax error or a white screen, and
 * deleting storage/content.json restores the factory content exactly.
 *
 * Everything downstream still reads SITE, PRICING, NAV and the rest as
 * constants, exactly as before — the merge is invisible to the templates.
 */

declare(strict_types=1);

/** Where the dashboard writes its overrides. */
function content_overrides_path(): string
{
    return __DIR__ . '/../storage/content.json';
}

/**
 * Merge overrides over defaults.
 *
 * Associative arrays merge key by key, so an override need only carry the
 * keys it actually changes. Lists are REPLACED wholesale — for a list of
 * price rows or feature cards, merging by index would leave orphaned
 * entries behind whenever the admin removes one.
 *
 * @param array<mixed> $base
 * @param array<mixed> $over
 * @return array<mixed>
 */
function content_merge(array $base, array $over): array
{
    if (array_is_list($over)) {
        return $over;
    }

    foreach ($over as $k => $v) {
        if (is_array($v) && isset($base[$k]) && is_array($base[$k])) {
            $base[$k] = content_merge($base[$k], $v);
        } else {
            $base[$k] = $v;
        }
    }

    return $base;
}

/**
 * The live content: defaults with any saved overrides applied.
 *
 * A missing, unreadable or malformed overrides file is ignored rather
 * than fatal. Serving the factory copy is a far better failure than
 * serving nothing, and the dashboard writes atomically so a half-written
 * file should not occur in the first place.
 *
 * @return array<string,mixed>
 */
function content_all(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $defaults = require __DIR__ . '/defaults.php';
    $cache = $defaults;

    $path = content_overrides_path();
    if (is_file($path)) {
        $raw = @file_get_contents($path);
        if ($raw !== false && $raw !== '') {
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $cache = content_merge($defaults, $data);
            } else {
                error_log('[content] storage/content.json is not valid JSON — serving defaults');
            }
        }
    }

    return $cache;
}

/* Published as constants so every template reads them exactly as it did
 * before the dashboard existed. define() takes runtime values; `const`
 * would not. */
$__content = content_all();
define('SITE', $__content['SITE']);
define('NAV', $__content['NAV']);
define('PRICING', $__content['PRICING']);
define('METRICS', $__content['METRICS']);
define('FEATURES', $__content['FEATURES']);
define('AUDIENCES', $__content['AUDIENCES']);
define('ROADMAP', $__content['ROADMAP']);
define('TICKER', $__content['TICKER']);
define('INTERESTS', $__content['INTERESTS']);
unset($__content);

/**
 * Attributes for every "Book a Bay" control. While bookingUrl is null
 * these scroll to the enquiry form; the moment it's set they open the
 * booking system in a new tab instead.
 *
 * @return array<string,string>
 */
function booking_link_attrs(): array
{
    if (!empty(SITE['bookingUrl'])) {
        return [
            'href'   => SITE['bookingUrl'],
            'target' => '_blank',
            'rel'    => 'noopener noreferrer',
        ];
    }

    return ['href' => '#book'];
}

const WEEK = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

/**
 * "Mon – Fri" rather than "Mon, Tue, Wed, Thu, Fri". Collapses any
 * unbroken run of days into a range, so this still reads properly if the
 * opening days change later.
 */
function days_label(): string
{
    $days = SITE['hours']['days'];
    if (count($days) === 7) {
        return 'Every day';
    }

    $idx = [];
    foreach ($days as $d) {
        $pos = array_search($d, WEEK, true);
        if ($pos !== false) {
            $idx[] = $pos;
        }
    }
    sort($idx);

    if (count($idx) === 0) {
        return '';
    }

    $contiguous = true;
    for ($i = 1, $n = count($idx); $i < $n; $i++) {
        if ($idx[$i] !== $idx[$i - 1] + 1) {
            $contiguous = false;
            break;
        }
    }

    $short = static fn(int $n): string => substr(WEEK[$n], 0, 3);

    if ($contiguous && count($idx) > 1) {
        return $short($idx[0]) . ' – ' . $short($idx[count($idx) - 1]);
    }

    return implode(', ', array_map($short, $idx));
}

/** Human-readable hours, e.g. "Every day · 10:00 – 22:00". */
function hours_label(): string
{
    return days_label() . ' · ' . SITE['hours']['opens'] . ' – ' . SITE['hours']['closes'];
}

/** `tel:` needs the number stripped of spaces; the display form keeps them. */
function tel_href(): ?string
{
    if (empty(SITE['phone'])) {
        return null;
    }
    return 'tel:' . preg_replace('/\s+/', '', SITE['phone']);
}

/** The registered office on one line, for the footer's legal notice. */
function registered_office_one_line(): string
{
    return implode(', ', [
        SITE['legal']['office']['line1'],
        SITE['legal']['office']['line2'],
        SITE['legal']['office']['town'],
        SITE['legal']['office']['country'],
        SITE['legal']['office']['postcode'],
    ]);
}

/** Formatted one-line address, for inline use and map links. */
function address_one_line(): string
{
    return implode(', ', [
        SITE['address']['line1'],
        SITE['address']['line2'],
        SITE['address']['line3'],
        SITE['address']['town'],
        SITE['address']['postcode'],
    ]);
}

/**
 * "£15", or "£12.50" if it ever needs to be. Every price on the current
 * rate card is whole pounds and every bay total divides exactly by its
 * player count — but the per-person figure is derived, so an edit to a
 * total could land on a half.
 */
function gbp(float $n): string
{
    return '£' . (floor($n) === $n ? (string) (int) $n : number_format($n, 2));
}

/** Per-player share of a bay total. */
function per_player(float $total, int $players): float
{
    return $total / $players;
}

/** "£15 – £192". Google shows this on the business panel. */
function price_range(): string
{
    $all = [];
    foreach (PRICING['periods'] as $p) {
        foreach ($p['rows'] as $row) {
            foreach ($row['totals'] as $t) {
                $all[] = $t;
            }
        }
    }

    if (!$all) {
        return '';
    }

    return gbp((float) min($all)) . ' – ' . gbp((float) max($all));
}

/** Look up an interest label by its value, for emails and confirmations. */
function interest_label(string $value): string
{
    foreach (INTERESTS as $i) {
        if ($i['value'] === $value) {
            return $i['label'];
        }
    }
    return 'General enquiry';
}
