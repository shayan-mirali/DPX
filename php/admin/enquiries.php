<?php
/**
 * Enquiries received.
 *
 * Reads storage/enquiries.jsonl — the copy written before any email is
 * attempted, so what's listed here is everything that ever arrived, even
 * the ones a mail outage swallowed.
 *
 * CSV export streams straight out; nothing is written to disk.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/auth.php';
admin_require_login();
require_once __DIR__ . '/inc/store.php';

/* Export has to run before any layout output, since it sends its own
 * headers and body. */
if (($_GET['export'] ?? '') === 'csv') {
    $rows = admin_enquiries();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="dpx-enquiries-' . gmdate('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    // BOM, or Excel mangles the pound signs and any accented names.
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Received', 'Name', 'Email', 'Phone', 'Interested in', 'Message']);

    foreach ($rows as $r) {
        fputcsv($out, [
            isset($r['receivedAt']) ? date('Y-m-d H:i', strtotime((string) $r['receivedAt'])) : '',
            (string) ($r['name'] ?? ''),
            (string) ($r['email'] ?? ''),
            (string) ($r['phone'] ?? ''),
            (string) ($r['interestLabel'] ?? $r['interest'] ?? ''),
            (string) ($r['message'] ?? ''),
        ]);
    }

    fclose($out);
    exit;
}

require_once __DIR__ . '/inc/layout.php';

$rows = admin_enquiries();

/* Optional filter, so a busy inbox can be narrowed without a database. */
$q = trim((string) ($_GET['q'] ?? ''));
if ($q !== '') {
    $needle = mb_strtolower($q);
    $rows = array_values(array_filter($rows, static function (array $r) use ($needle): bool {
        $hay = mb_strtolower(implode(' ', [
            (string) ($r['name'] ?? ''),
            (string) ($r['email'] ?? ''),
            (string) ($r['phone'] ?? ''),
            (string) ($r['interestLabel'] ?? ''),
            (string) ($r['message'] ?? ''),
        ]));
        return str_contains($hay, $needle);
    }));
}

admin_head('Enquiries', 'enquiries.php');
page_header(
    'Enquiries',
    'Everyone who has filled in the form, newest first. These are saved here '
    . 'before any email goes out, so nothing is lost even if an email fails.',
    'book'
);
?>

<div class="card">
  <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:space-between">
    <form method="get" action="enquiries.php" style="display:flex;gap:8px;flex:1;min-width:240px">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, email, message…">
      <button type="submit" class="btn quiet">Search</button>
      <?php if ($q !== ''): ?>
        <a class="ghost" href="enquiries.php" style="align-self:center">Clear</a>
      <?php endif; ?>
    </form>
    <?php if ($rows): ?>
      <a class="ghost" href="enquiries.php?export=csv">Download CSV</a>
    <?php endif; ?>
  </div>
</div>

<?php if (!$rows): ?>
  <div class="card">
    <p style="margin:0;color:var(--bone-dim)">
      <?= $q !== '' ? 'Nothing matches that search.' : 'No enquiries yet.' ?>
    </p>
    <?php if ($q === ''): ?>
      <span class="hint" style="margin-top:10px">
        They'll appear the moment someone uses the form on the site.
      </span>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="card">
    <span class="hint" style="margin-bottom:12px">
      Showing <?= count($rows) ?> enquir<?= count($rows) === 1 ? 'y' : 'ies' ?>.
    </span>
    <div class="scroll-x">
      <table>
        <thead>
          <tr>
            <th>Received</th>
            <th>Name</th>
            <th>Contact</th>
            <th>Interested in</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td style="white-space:nowrap;color:var(--bone-dim);font-size:13px">
                <?= isset($r['receivedAt'])
                    ? e(date('j M Y', strtotime((string) $r['receivedAt'])))
                      . '<br>' . e(date('H:i', strtotime((string) $r['receivedAt'])))
                    : '—' ?>
              </td>
              <td><?= e((string) ($r['name'] ?? '')) ?></td>
              <td style="font-size:13px">
                <?php $em = (string) ($r['email'] ?? ''); ?>
                <?php if ($em !== ''): ?>
                  <a href="mailto:<?= e($em) ?>"><?= e($em) ?></a>
                <?php endif; ?>
                <?php if (!empty($r['phone'])): ?>
                  <br><span style="color:var(--bone-dim)"><?= e((string) $r['phone']) ?></span>
                <?php endif; ?>
              </td>
              <td style="font-size:13px"><?= e((string) ($r['interestLabel'] ?? $r['interest'] ?? '')) ?></td>
              <td style="font-size:13px;max-width:340px">
                <?= nl2br(e((string) ($r['message'] ?? ''))) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<div class="card">
  <h2>Why there is no delete button</h2>
  <span class="hint">
    These are customer records, and one mis-click should not be able to wipe
    them. If you genuinely need to clear the list, ask whoever looks after the
    website — it is a single file on the server.
  </span>
</div>

<?php admin_foot(); ?>
