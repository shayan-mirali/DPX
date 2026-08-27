<?php
/** Dashboard overview — what's here, what's been changed, what's outstanding. */

declare(strict_types=1);

require_once __DIR__ . '/inc/auth.php';
admin_require_login();
require_once __DIR__ . '/inc/layout.php';

/* A reset is destructive enough to want its own confirmation step, so it
 * posts here rather than living behind a link. */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'reset') {
    admin_csrf_check();
    if (post_str('confirm') !== 'RESET') {
        admin_flash('Type RESET in the box to confirm.', 'err');
    } elseif (admin_reset_all()) {
        admin_flash('All edits discarded. The site is back to its shipped content — a backup was saved first.');
    } else {
        admin_flash('Could not reset. Check that storage/ is writable.', 'err');
    }
    header('Location: index.php');
    exit;
}

$enquiries = admin_enquiries();
$overrides = admin_overrides();
$prices = 0;
foreach (PRICING['periods'] as $p) {
    foreach ($p['rows'] as $r) {
        $prices += count($r['totals']);
    }
}

$latest = $enquiries[0]['receivedAt'] ?? null;

admin_head('Overview', 'index.php');
?>

<h1>Overview</h1>
<p class="lede">
  Everything on the public site that changes regularly is editable here.
  Saves take effect immediately — there is nothing to publish or deploy.
</p>

<div class="tiles">
  <a class="tile" href="enquiries.php">
    <div class="n"><?= count($enquiries) ?></div>
    <div class="k">Enquiries received</div>
  </a>
  <a class="tile" href="pricing.php">
    <div class="n"><?= $prices ?></div>
    <div class="k">Prices on the rate card</div>
  </a>
  <a class="tile" href="sections.php">
    <div class="n"><?= count(FEATURES) + count(AUDIENCES) + count(ROADMAP) ?></div>
    <div class="k">Content cards</div>
  </a>
  <a class="tile" href="site.php">
    <div class="n"><?= count(SITE['hours']['days']) ?></div>
    <div class="k">Days open per week</div>
  </a>
</div>

<div class="card">
  <h2>What you've changed</h2>
  <span class="hint">
    Sections not listed are still showing the copy the site shipped with.
  </span>
  <table style="margin-top:14px">
    <tbody>
      <?php foreach ([
          'SITE' => ['Venue details', 'site.php'],
          'PRICING' => ['Pricing', 'pricing.php'],
          'FEATURES' => ['Venue feature cards', 'sections.php'],
          'AUDIENCES' => ['Who it\'s for', 'sections.php'],
          'ROADMAP' => ['What\'s coming', 'sections.php'],
          'METRICS' => ['TrackMan readout', 'sections.php'],
          'TICKER' => ['Scrolling ticker', 'sections.php'],
      ] as $key => [$label, $href]): ?>
        <tr>
          <td><a href="<?= e($href) ?>"><?= e($label) ?></a></td>
          <td style="text-align:right">
            <?php if (isset($overrides[$key])): ?>
              <span class="badge">Edited</span>
            <?php else: ?>
              <span class="badge plain">Default</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h2>Worth knowing</h2>
  <ul style="margin:10px 0 0;padding-left:18px;color:var(--bone-dim);font-size:14px;line-height:1.7">
    <li>
      Bookings: <?= empty(SITE['bookingUrl'])
        ? 'no booking link set, so every <strong>Book a Bay</strong> button scrolls to the enquiry form'
        : 'linking out to <code>' . e(SITE['bookingUrl']) . '</code>' ?>.
      <a href="site.php">Change</a>
    </li>
    <li>
      Enquiry emails: <?= admin_config()['resend_api_key'] ?? '' ? 'configured' : '<strong>not configured</strong> — enquiries are still saved here, but no email is sent' ?>.
    </li>
    <li>
      Last enquiry: <?= $latest ? e(date('j M Y, H:i', strtotime((string) $latest))) : 'none yet' ?>.
    </li>
  </ul>
</div>

<div class="card">
  <h2>Start over</h2>
  <span class="hint">
    Discards every edit made here and returns the site to the copy it shipped
    with. A backup is written to <code>storage/backups/</code> first, so this
    is undoable by someone with file access.
  </span>
  <form method="post" action="index.php" style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
    <?php csrf_input(); ?>
    <input type="hidden" name="action" value="reset">
    <input type="text" name="confirm" placeholder="Type RESET" style="max-width:180px" autocomplete="off">
    <button type="submit" class="btn danger">Discard all edits</button>
  </form>
</div>

<?php admin_foot(); ?>
