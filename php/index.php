<?php
/**
 * DPX Golf — single page.
 *
 * Sections are partials under inc/partials and are included in reading
 * order below. All copy lives in inc/content.php.
 */

declare(strict_types=1);

require __DIR__ . '/inc/content.php';
require __DIR__ . '/inc/helpers.php';

/* Cache-busting from file mtime. The .htaccess caches CSS and JS for a
 * week, so without this a returning visitor keeps the old file after a
 * deploy. Automatic beats remembering to bump a number by hand. */
function asset(string $path): string
{
    $full = __DIR__ . '/' . $path;
    $v = is_file($full) ? (string) filemtime($full) : '1';
    return $path . '?v=' . $v;
}

$title = SITE['name'] . ' — ' . SITE['descriptor'] . ', ' . SITE['town'];
$description = 'TrackMan-powered indoor golf in Burton upon Trent. Play iconic courses, practise against tour-level data, or bring the group for a night out. Rain or shine, your next round is always on.';

/* Local business structured data — this is what puts the venue on the map
 * panel in Google results, so the postal address matters. Everything here
 * derives from content.php, including the price range, so the panel cannot
 * drift out of step with the rate card on the page. */
$json_ld = [
    '@context' => 'https://schema.org',
    '@type' => 'SportsActivityLocation',
    'name' => SITE['name'],
    'description' => SITE['descriptor'] . ' in ' . SITE['town'] . ', powered by TrackMan.',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => implode(', ', [SITE['address']['line1'], SITE['address']['line2'], SITE['address']['line3']]),
        'addressLocality' => SITE['address']['town'],
        'addressRegion' => 'Staffordshire',
        'postalCode' => SITE['address']['postcode'],
        'addressCountry' => SITE['address']['country'],
    ],
    'email' => SITE['emails'][0],
    'telephone' => SITE['phone'],
    'priceRange' => price_range(),
    'currenciesAccepted' => 'GBP',
    'openingHoursSpecification' => [[
        '@type' => 'OpeningHoursSpecification',
        'dayOfWeek' => SITE['hours']['days'],
        'opens' => SITE['hours']['opens'],
        'closes' => SITE['hours']['closes'],
    ]],
    'sport' => 'Golf',
];
?>
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<meta name="theme-color" content="#060a09">
<meta name="color-scheme" content="dark">
<link rel="canonical" href="<?= e(SITE['origin']) ?>/">
<link rel="icon" href="assets/img/icon.png" type="image/png">

<meta property="og:type" content="website">
<meta property="og:locale" content="en_GB">
<meta property="og:site_name" content="<?= e(SITE['name']) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="TrackMan-powered indoor golf bays in Burton upon Trent. Play, practise and compete — whatever the weather.">
<meta property="og:image" content="<?= e(SITE['origin']) ?>/assets/img/venue-wide.webp">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e(SITE['name']) ?> — <?= e(SITE['descriptor']) ?>">
<meta name="twitter:description" content="TrackMan-powered indoor golf in Burton upon Trent.">
<meta name="twitter:image" content="<?= e(SITE['origin']) ?>/assets/img/venue-wide.webp">

<!-- Bricolage carries the display voice — it has enough character to feel
     drawn rather than picked. Instrument Sans keeps body copy quiet, and
     JetBrains Mono does all the telemetry. -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,700&family=Instrument+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= e(asset('assets/css/styles.css')) ?>">

<script type="application/ld+json"><?= json_encode($json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</head>
<body class="grain antialiased">

<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-lime focus:px-5 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-ink">
  Skip to content
</a>

<!-- Preloader. Rendered hidden and revealed by the inline script below so
     it never flashes for people who have already booted this session, and
     never appears at all with scripting off. -->
<div id="preloader" hidden aria-hidden="true"
     class="fixed inset-0 z-[90] flex items-center justify-center bg-ink transition-[clip-path] duration-[900ms]"
     style="transition-timing-function:var(--ease-in-out-quint);clip-path:inset(0 0 0 0)">
  <div class="flex w-[min(78vw,340px)] flex-col items-center gap-6">
    <div class="relative h-16 w-16">
      <div id="pre-ring" class="absolute inset-0 rounded-full border border-lime/40" style="transform:scale(.6);transition:transform .2s linear"></div>
      <div class="absolute inset-[22px] rounded-full bg-bone"></div>
      <span class="absolute left-1/2 top-0 h-3 w-px -translate-x-1/2 bg-lime"></span>
      <span class="absolute bottom-0 left-1/2 h-3 w-px -translate-x-1/2 bg-lime"></span>
      <span class="absolute left-0 top-1/2 h-px w-3 -translate-y-1/2 bg-lime"></span>
      <span class="absolute right-0 top-1/2 h-px w-3 -translate-y-1/2 bg-lime"></span>
    </div>
    <div class="w-full">
      <div class="h-px w-full bg-bone/12">
        <div id="pre-bar" class="h-px bg-lime" style="width:0%;transition:width .12s linear"></div>
      </div>
      <div class="data mt-3 flex justify-between text-[10px] uppercase tracking-[0.24em] text-bone-dim">
        <span>Acquiring</span>
        <span id="pre-pct" class="text-lime">000</span>
      </div>
    </div>
  </div>
</div>
<script>
/* Runs before paint: decide whether the boot sequence should show at all.
   Capped at ~1.7s, once per session, skipped for reduced motion — a
   preloader that holds content hostage is a bug, not a flourish. */
(function () {
  try {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var seen = sessionStorage.getItem('dpx:booted');
    if (!reduced && !seen) {
      document.getElementById('preloader').hidden = false;
      document.documentElement.style.overflow = 'hidden';
      window.__dpxBoot = true;
    }
  } catch (e) { /* private mode, blocked storage — just skip the preloader */ }
})();
</script>

<!-- Reticle cursor. The DPX mark is a crosshair locking onto a ball, so the
     cursor becomes that crosshair. Mouse only, never blocks a click. -->
<div id="reticle" aria-hidden="true" class="pointer-events-none fixed left-0 top-0 z-[70] hidden [@media(pointer:fine)]:block" style="opacity:0;transition:opacity .25s ease">
  <div id="reticle-body" class="relative -translate-x-1/2 -translate-y-1/2 transition-[width,height] duration-500"
       style="width:30px;height:30px;transition-timing-function:var(--ease-out-expo)">
    <div id="reticle-ring" class="absolute inset-0 rounded-full border transition-colors duration-300" style="border-color:rgba(237,232,220,.45)"></div>
    <span class="reticle-tick absolute left-1/2 top-0 h-[7px] w-px -translate-x-1/2 transition-colors duration-300"></span>
    <span class="reticle-tick absolute bottom-0 left-1/2 h-[7px] w-px -translate-x-1/2 transition-colors duration-300"></span>
    <span class="reticle-tick absolute left-0 top-1/2 h-px w-[7px] -translate-y-1/2 transition-colors duration-300"></span>
    <span class="reticle-tick absolute right-0 top-1/2 h-px w-[7px] -translate-y-1/2 transition-colors duration-300"></span>
    <div id="reticle-dot" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 rounded-full transition-all duration-500"
         style="width:3px;height:3px;background:rgb(237,232,220)"></div>
  </div>
  <span id="reticle-label" class="data absolute left-9 top-4 whitespace-nowrap text-[10px] uppercase tracking-[0.2em] text-lime"></span>
</div>

<!-- Scroll tracer: a hairline down the right edge that fills as you read. -->
<div aria-hidden="true" class="fixed right-5 top-1/2 z-40 hidden -translate-y-1/2 flex-col items-center gap-3 min-[1620px]:flex">
  <span class="data text-[9px] tracking-[0.2em] text-bone/30">000</span>
  <span class="relative block h-40 w-px bg-bone/12">
    <span id="tracer-fill" class="absolute inset-0 block origin-top bg-gradient-to-b from-lime to-amber" style="transform:scaleY(0)"></span>
  </span>
  <span id="tracer-pct" class="data text-[9px] tracking-[0.2em] text-lime">000</span>
</div>

<?php require __DIR__ . '/inc/partials/nav.php'; ?>

<main id="main">
  <?php
  require __DIR__ . '/inc/partials/hero.php';
  require __DIR__ . '/inc/partials/ticker.php';
  require __DIR__ . '/inc/partials/venue.php';
  require __DIR__ . '/inc/partials/tech.php';
  require __DIR__ . '/inc/partials/audience.php';
  require __DIR__ . '/inc/partials/pricing.php';
  require __DIR__ . '/inc/partials/roadmap.php';
  require __DIR__ . '/inc/partials/interlude.php';
  require __DIR__ . '/inc/partials/book.php';
  ?>
</main>

<?php require __DIR__ . '/inc/partials/footer.php'; ?>

<script src="<?= e(asset('assets/js/vendor/lenis.min.js')) ?>" defer></script>
<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
