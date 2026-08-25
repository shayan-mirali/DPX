<?php
/**
 * Small rendering helpers. Nothing clever — the point is that every
 * value reaching the page goes through one escaping function, so it is
 * obvious when something doesn't.
 */

declare(strict_types=1);

/** Escape for HTML text and quoted attribute values. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Render an attribute array as `key="value"` pairs. A null or false
 * value omits the attribute entirely; true renders it bare.
 *
 * @param array<string,string|bool|null> $attrs
 */
function attrs(array $attrs): string
{
    $out = [];
    foreach ($attrs as $k => $v) {
        if ($v === null || $v === false) {
            continue;
        }
        if ($v === true) {
            $out[] = e($k);
            continue;
        }
        $out[] = e($k) . '="' . e((string) $v) . '"';
    }
    return implode(' ', $out);
}

/**
 * A scroll-reveal wrapper. The JS observer sets `data-inview="true"` once
 * the element crosses the viewport; the CSS in styles.css does the rest.
 *
 * Mirrors <Reveal> from the React build. `$variant` is 'up' | 'wipe'.
 */
function reveal_open(string $class = '', int $delay = 0, string $variant = 'up'): string
{
    $cls = $variant === 'wipe' ? 'reveal-wipe' : 'reveal-up';
    $style = $delay > 0 ? ' style="--d:' . (int) $delay . 'ms"' : '';
    $inner = $variant === 'wipe' ? '<div>' : '';

    return '<div class="' . e(trim($cls . ' ' . $class)) . '"' . $style . '>' . $inner;
}

function reveal_close(string $variant = 'up'): string
{
    return $variant === 'wipe' ? '</div></div>' : '</div>';
}

/**
 * A masked heading whose lines slide up on a stagger. Mirrors
 * <RevealLines>. Each entry of $lines is raw HTML, already escaped by
 * the caller — these carry markup such as the lime gradient span.
 *
 * @param string[] $lines
 */
function reveal_lines(array $lines, string $class = '', int $delay = 0, int $step = 90): string
{
    $out = '<div class="reveal-lines ' . e($class) . '">';
    foreach ($lines as $i => $line) {
        $d = $delay + $i * $step;
        $out .= '<span><span style="--d:' . (int) $d . 'ms">' . $line . '</span></span>';
    }
    return $out . '</div>';
}

/** Pluralise a player count: "1 Player" / "3 Players". */
function players_label(int $n): string
{
    return $n . ' ' . ($n === 1 ? 'Player' : 'Players');
}

/** "1 Hour" / "2 Hours". */
function hours_word(int $n): string
{
    return $n . ' ' . ($n === 1 ? 'Hour' : 'Hours');
}
