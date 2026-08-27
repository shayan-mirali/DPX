<?php
/**
 * Venue details — name, contact, address, hours, booking link, legal.
 *
 * The whole SITE array is rewritten on save, starting from the current
 * merged values, so a field left untouched keeps whatever it had.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/auth.php';
admin_require_login();
require_once __DIR__ . '/inc/layout.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf_check();

    $site = SITE;

    $site['name'] = post_str('name', $site['name']);
    $site['tagline'] = post_str('tagline');
    $site['descriptor'] = post_str('descriptor');
    $site['town'] = post_str('town');

    $site['address'] = [
        'line1' => post_str('addr_line1'),
        'line2' => post_str('addr_line2'),
        'line3' => post_str('addr_line3'),
        'town' => post_str('addr_town'),
        'postcode' => post_str('addr_postcode'),
        'country' => post_str('addr_country', 'GB'),
    ];

    // An emptied email box means "remove this address", not "save a blank".
    $site['emails'] = compact_list(post_list('emails'));
    $site['phone'] = post_str('phone');

    $site['hours'] = [
        'opens' => post_str('opens'),
        'closes' => post_str('closes'),
        'days' => array_values(array_intersect(WEEK, post_list('days'))),
    ];

    $site['legal'] = [
        'company' => post_str('legal_company'),
        'companyNumber' => post_str('legal_number'),
        'office' => [
            'line1' => post_str('office_line1'),
            'line2' => post_str('office_line2'),
            'town' => post_str('office_town'),
            'country' => post_str('office_country'),
            'postcode' => post_str('office_postcode'),
        ],
    ];

    /* An empty booking URL must become null, not "". The templates test it
     * with empty(), but null is what the field means: not set yet. */
    $booking = post_str('bookingUrl');
    $site['bookingUrl'] = $booking === '' ? null : $booking;
    $site['origin'] = rtrim(post_str('origin'), '/');

    $problems = [];
    if ($site['name'] === '') {
        $problems[] = 'the venue name';
    }
    if (!$site['hours']['days']) {
        $problems[] = 'at least one opening day';
    }
    foreach ($site['emails'] as $em) {
        if (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
            $problems[] = 'a valid email address (' . $em . ' is not one)';
        }
    }
    if ($booking !== '' && !filter_var($booking, FILTER_VALIDATE_URL)) {
        $problems[] = 'a valid booking URL, including https://';
    }

    if ($problems) {
        admin_flash('Not saved — needs ' . implode(', ', $problems) . '.', 'err');
    } elseif (admin_save_section('SITE', $site)) {
        admin_flash('Venue details saved.');
    } else {
        admin_flash('Could not save. Check that storage/ is writable.', 'err');
    }

    header('Location: site.php');
    exit;
}

admin_head('Venue details', 'site.php');
?>

<h1>Venue details</h1>
<p class="lede">
  Contact details, opening hours and the address. These feed the page, the
  footer, the enquiry emails and the Google listing data all at once.
</p>

<form method="post" action="site.php">
<?php csrf_input(); ?>

<div class="card">
  <h2>Identity</h2>
  <div class="grid two">
    <?php field('name', 'Venue name', SITE['name']); ?>
    <?php field('town', 'Town', SITE['town']); ?>
  </div>
  <div class="grid two" style="margin-top:16px">
    <?php field('descriptor', 'Descriptor', SITE['descriptor'], 'Shown above the headline and in the page title.'); ?>
    <?php field('tagline', 'Tagline', SITE['tagline'], 'The big scrolling line between sections.'); ?>
  </div>
</div>

<div class="card">
  <h2>Contact</h2>
  <div class="grid two">
    <?php field('phone', 'Phone', (string) SITE['phone'], 'Displayed as typed; the tel: link strips the spaces.'); ?>
    <?php field('bookingUrl', 'Booking system URL', (string) (SITE['bookingUrl'] ?? ''),
        'Leave empty and every <strong>Book a Bay</strong> button scrolls to the enquiry form. Add a URL and they all open it in a new tab instead.', 'url'); ?>
  </div>

  <div style="margin-top:18px">
    <span class="lab">Email addresses</span>
    <span class="hint" style="margin-bottom:8px">
      Enquiry notifications go to the first one. Clear a box to remove it.
    </span>
    <?php
    $emails = SITE['emails'];
    // A spare empty box so a new address can be added without any JS.
    $emails[] = '';
    foreach ($emails as $i => $em): ?>
      <input type="email" name="emails[]" value="<?= e($em) ?>"
             placeholder="name@example.co.uk" style="margin-bottom:8px">
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <h2>Where the venue is</h2>
  <span class="hint">This is the address customers visit, and what the map link points at.</span>
  <div class="grid two" style="margin-top:14px">
    <?php field('addr_line1', 'Line 1', SITE['address']['line1']); ?>
    <?php field('addr_line2', 'Line 2', SITE['address']['line2']); ?>
    <?php field('addr_line3', 'Line 3', SITE['address']['line3']); ?>
    <?php field('addr_town', 'Town', SITE['address']['town']); ?>
    <?php field('addr_postcode', 'Postcode', SITE['address']['postcode']); ?>
    <?php field('addr_country', 'Country code', SITE['address']['country'], 'Two letters, e.g. GB. Used by the Google listing data.'); ?>
  </div>
</div>

<div class="card">
  <h2>Opening hours</h2>
  <div class="grid two">
    <?php field('opens', 'Opens', SITE['hours']['opens'], '24-hour, e.g. 10:00', 'time'); ?>
    <?php field('closes', 'Closes', SITE['hours']['closes'], '24-hour, e.g. 22:00', 'time'); ?>
  </div>
  <div style="margin-top:16px">
    <span class="lab">Days open</span>
    <span class="hint" style="margin-bottom:10px">
      The label writes itself: seven days becomes “Every day”, an unbroken run
      becomes “Mon – Fri”.
    </span>
    <div style="display:flex;flex-wrap:wrap;gap:14px">
      <?php foreach (WEEK as $d): ?>
        <label style="display:flex;align-items:center;gap:7px;font-size:14px">
          <input type="checkbox" name="days[]" value="<?= e($d) ?>" style="width:auto"
                 <?= in_array($d, SITE['hours']['days'], true) ? 'checked' : '' ?>>
          <?= e(substr($d, 0, 3)) ?>
        </label>
      <?php endforeach; ?>
    </div>
    <span class="hint" style="margin-top:10px">Currently reads: <strong><?= e(hours_label()) ?></strong></span>
  </div>
</div>

<div class="card">
  <h2>Company details</h2>
  <span class="hint">
    Shown in the footer. The registered office is a legal address and is
    <strong>not</strong> where customers go — the footer says so explicitly.
  </span>
  <div class="grid two" style="margin-top:14px">
    <?php field('legal_company', 'Registered company name', SITE['legal']['company']); ?>
    <?php field('legal_number', 'Company number', SITE['legal']['companyNumber']); ?>
  </div>
  <div class="grid two" style="margin-top:16px">
    <?php field('office_line1', 'Office line 1', SITE['legal']['office']['line1']); ?>
    <?php field('office_line2', 'Office line 2', SITE['legal']['office']['line2']); ?>
    <?php field('office_town', 'Office town', SITE['legal']['office']['town']); ?>
    <?php field('office_postcode', 'Office postcode', SITE['legal']['office']['postcode']); ?>
    <?php field('office_country', 'Country', SITE['legal']['office']['country']); ?>
    <?php field('origin', 'Site address', SITE['origin'], 'Used for share previews and the canonical URL, e.g. https://dpxgolf.co.uk', 'url'); ?>
  </div>
</div>

<?php save_bar('Changes appear on the site as soon as you save.'); ?>
</form>

<?php admin_foot(); ?>
