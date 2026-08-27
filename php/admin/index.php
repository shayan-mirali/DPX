<?php
/**
 * Overview — a starting point rather than a status dump.
 *
 * Leads with the things somebody actually came here to do, keeps the
 * numbers small, and puts the destructive option at the bottom behind a
 * typed confirmation.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/auth.php';
admin_require_login();
require_once __DIR__ . '/inc/layout.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'reset') {
    admin_csrf_check();
    if (post_str('confirm') !== 'RESET') {
        admin_flash('Nothing was changed — type RESET in the box to confirm.', 'err');
    } elseif (admin_reset_all()) {
        admin_flash('Done. Everything is back to how the site was built, and a backup was saved first.');
    } else {
        admin_flash('Could not do that. The storage folder on the server is not writable.', 'err');
    }
    header('Location: index.php');
    exit;
}

$enquiries = admin_enquiries();
$overrides = admin_overrides();
$latest = $enquiries[0] ?? null;

$edited = [];
foreach ([
    'SITE' => 'Venue details',
    'PRICING' => 'Pricing',
    'FEATURES' => 'Page content',
    'AUDIENCES' => 'Page content',
    'ROADMAP' => 'Page content',
    'METRICS' => 'Page content',
    'TICKER' => 'Page content',
] as $key => $label) {
    if (isset($overrides[$key])) {
        $edited[$label] = true;
    }
}

$hasEmail = ((string) (admin_config()['resend_api_key'] ?? '')) !== '';

admin_head('Overview', 'index.php');
page_header(
    'Welcome back',
    'Everything on the site that changes regularly can be edited here. '
    . 'Nothing needs publishing — saving is enough.'
);
?>

<div class="tiles">
  <a class="tile" href="enquiries.php">
    <div class="n"><?= count($enquiries) ?></div>
    <div class="k">Enquiries so far</div>
  </a>
  <a class="tile" href="pricing.php">
    <div class="n"><?= e(price_range()) ?></div>
    <div class="k">Your price range</div>
  </a>
  <a class="tile" href="site.php">
    <div class="n" style="font-size:19px;line-height:1.3"><?= e(days_label()) ?></div>
    <div class="k"><?= e(SITE['hours']['opens']) ?> – <?= e(SITE['hours']['closes']) ?></div>
  </a>
</div>

<div class="card">
  <h2>What would you like to change?</h2>
  <span class="hint">The four things people ask for most often.</span>

  <div class="tasks">
    <a class="task" href="pricing.php">
      <?= admin_icon('tag') ?>
      <span>
        <strong>Put prices up or down</strong>
        <em>The whole rate card, plus the VAT line and small print</em>
      </span>
    </a>
    <a class="task" href="site.php">
      <?= admin_icon('pin') ?>
      <span>
        <strong>Change opening hours or contact details</strong>
        <em>Updates the page, the footer and your Google listing together</em>
      </span>
    </a>
    <a class="task" href="enquiries.php">
      <?= admin_icon('inbox') ?>
      <span>
        <strong>Read new enquiries</strong>
        <em>Search them, or download the lot as a spreadsheet</em>
      </span>
    </a>
    <a class="task" href="sections.php">
      <?= admin_icon('text') ?>
      <span>
        <strong>Reword the page</strong>
        <em>The boxes, lists and cards down the middle of the site</em>
      </span>
    </a>
  </div>
</div>

<?php if ($latest): ?>
  <div class="card">
    <h2>Latest enquiry</h2>
    <span class="hint">
      <?= isset($latest['receivedAt'])
          ? e(date('j M Y \a\t H:i', strtotime((string) $latest['receivedAt'])))
          : 'Time unknown' ?>
    </span>
    <p style="margin:14px 0 0;font-size:15px">
      <strong><?= e((string) ($latest['name'] ?? 'Someone')) ?></strong>
      <?php if (!empty($latest['interestLabel'])): ?>
        — <?= e((string) $latest['interestLabel']) ?>
      <?php endif; ?>
      <?php if (!empty($latest['email'])): ?>
        <br><a href="mailto:<?= e((string) $latest['email']) ?>"><?= e((string) $latest['email']) ?></a>
      <?php endif; ?>
    </p>
    <p style="margin-top:14px"><a class="ghost" href="enquiries.php">See all enquiries</a></p>
  </div>
<?php endif; ?>

<div class="card">
  <h2>Worth knowing</h2>
  <ul class="notes">
    <li>
      <?php if (empty(SITE['bookingUrl'])): ?>
        <strong>No booking link is set.</strong> Every <em>Book a Bay</em> button
        sends people to the enquiry form instead.
        <a href="site.php">Add one</a>
      <?php else: ?>
        Booking buttons open <code><?= e((string) SITE['bookingUrl']) ?></code>.
        <a href="site.php">Change</a>
      <?php endif; ?>
    </li>
    <li>
      <?php if ($hasEmail): ?>
        Enquiry emails are switched on — you get one, and the customer gets a
        confirmation.
      <?php else: ?>
        <strong>Enquiry emails are not switched on.</strong> Enquiries are still
        saved and listed here, but nothing is emailed to you. Ask whoever looks
        after the website to finish setting it up.
      <?php endif; ?>
    </li>
    <li>
      <?php if ($edited): ?>
        You have made changes to: <?= e(implode(', ', array_keys($edited))) ?>.
      <?php else: ?>
        Nothing has been changed yet — the site is showing the words it was
        built with.
      <?php endif; ?>
    </li>
  </ul>
</div>

<div class="card danger-card">
  <h2>Start over</h2>
  <span class="hint">
    Puts every word and price back to how the site was originally built. A
    backup is saved first, so this can be undone by whoever looks after the
    website. Type <code>RESET</code> to confirm you mean it.
  </span>
  <form method="post" action="index.php" class="resetform">
    <?php csrf_input(); ?>
    <input type="hidden" name="action" value="reset">
    <input type="text" name="confirm" placeholder="Type RESET" autocomplete="off">
    <button type="submit" class="btn danger">Undo all my changes</button>
  </form>
</div>

<?php admin_foot(); ?>
