<?php
/**
 * Enquiries received.
 *
 * Reads storage/enquiries.jsonl — the copy written before any email is
 * attempted, so what's listed here is everything that ever arrived, even
 * the ones a mail outage swallowed.
 *
 * Deleting asks first, names the person in the question, and keeps a copy
 * of anything removed. CSV export streams straight out; nothing is
 * written to disk.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/auth.php';
admin_require_login();
require_once __DIR__ . '/inc/store.php';

/* Delete runs before any output, since it redirects. */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    admin_csrf_check();

    $who = post_str('who');
    if (admin_delete_enquiry(post_str('id'))) {
        admin_flash(($who !== '' ? "Enquiry from $who" : 'Enquiry') . ' deleted.');
    } else {
        admin_flash('That enquiry could not be found — it may already have been deleted.', 'err');
    }

    $back = 'enquiries.php';
    if (post_str('q') !== '') {
        $back .= '?q=' . rawurlencode(post_str('q'));
    }
    header('Location: ' . $back);
    exit;
}

/* Export also has to run before the layout, since it sends its own
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

$all = admin_enquiries();
$rows = $all;

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
  <div class="toolbar">
    <form method="get" action="enquiries.php" class="searchform">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, email, message…">
      <button type="submit" class="btn quiet">Search</button>
      <?php if ($q !== ''): ?>
        <a class="ghost" href="enquiries.php">Clear</a>
      <?php endif; ?>
    </form>
    <?php if ($all): ?>
      <a class="ghost nowrap" href="enquiries.php?export=csv">Download CSV</a>
    <?php endif; ?>
  </div>
</div>

<?php if (!$rows): ?>
  <div class="card">
    <p style="margin:0;color:var(--dim)">
      <?= $q !== '' ? 'Nothing matches that search.' : 'No enquiries yet.' ?>
    </p>
    <?php if ($q === ''): ?>
      <span class="hint" style="margin-top:10px">
        They will appear the moment someone uses the form on the site.
      </span>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="card">
    <span class="hint" style="margin-bottom:12px">
      Showing <?= count($rows) ?> enquir<?= count($rows) === 1 ? 'y' : 'ies' ?><?php
        if ($q !== '' && count($all) !== count($rows)) {
            echo ' of ' . count($all);
        } ?>.
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
            <th><span class="sr-only">Delete</span></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r):
              $name = (string) ($r['name'] ?? 'this enquiry');
              $when = isset($r['receivedAt'])
                  ? date('j M Y \a\t H:i', strtotime((string) $r['receivedAt']))
                  : 'an unknown date'; ?>
            <tr>
              <td class="when">
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
                  <br><span style="color:var(--dim)"><?= e((string) $r['phone']) ?></span>
                <?php endif; ?>
              </td>
              <td style="font-size:13px"><?= e((string) ($r['interestLabel'] ?? $r['interest'] ?? '')) ?></td>
              <td style="font-size:13px;max-width:320px">
                <?= nl2br(e((string) ($r['message'] ?? ''))) ?>
              </td>
              <td style="text-align:right">
                <button type="button" class="del"
                        data-id="<?= e((string) ($r['_id'] ?? '')) ?>"
                        data-name="<?= e($name) ?>"
                        data-when="<?= e($when) ?>"
                        aria-label="Delete the enquiry from <?= e($name) ?>">
                  Delete
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<div class="card">
  <h2>If you delete one by mistake</h2>
  <span class="hint">
    A copy of anything you delete is kept on the server, so it can be
    recovered by whoever looks after the website. It will not show in this
    list again, but it is not gone for good.
  </span>
</div>

<!-- Confirmation. Uses <dialog>, which gives keyboard trapping and Escape
     for free; the button falls back to a plain confirm() if the browser
     is too old for it. -->
<dialog id="confirm-delete" class="modal" aria-labelledby="cd-title">
  <form method="post" action="enquiries.php">
    <?php csrf_input(); ?>
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="cd-id" value="">
    <input type="hidden" name="who" id="cd-who" value="">
    <input type="hidden" name="q" value="<?= e($q) ?>">

    <h2 id="cd-title">Delete this enquiry?</h2>
    <p class="modal-body">
      From <strong id="cd-name">this person</strong>, received
      <span id="cd-when">recently</span>.
    </p>
    <p class="hint">
      It will disappear from this list. A copy is kept on the server in case
      you need it back.
    </p>

    <div class="modal-actions">
      <button type="button" class="btn quiet" id="cd-cancel">Keep it</button>
      <button type="submit" class="btn danger">Yes, delete it</button>
    </div>
  </form>
</dialog>

<script>
(function () {
  var dlg = document.getElementById('confirm-delete');
  var idField = document.getElementById('cd-id');
  var whoField = document.getElementById('cd-who');
  var nameEl = document.getElementById('cd-name');
  var whenEl = document.getElementById('cd-when');
  var cancel = document.getElementById('cd-cancel');
  if (!dlg || !idField) return;

  var canModal = typeof dlg.showModal === 'function';

  document.querySelectorAll('.del').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-id') || '';
      var name = btn.getAttribute('data-name') || 'this enquiry';
      var when = btn.getAttribute('data-when') || '';

      idField.value = id;
      whoField.value = name;

      /* No <dialog> support: fall back to the browser's own confirm rather
         than showing a dialog nobody can dismiss. */
      if (!canModal) {
        if (window.confirm('Delete the enquiry from ' + name + '? A copy is kept on the server.')) {
          idField.form.submit();
        }
        return;
      }

      nameEl.textContent = name;
      whenEl.textContent = when;
      dlg.showModal();
    });
  });

  if (cancel) {
    cancel.addEventListener('click', function () { dlg.close(); });
  }

  /* Clicking the backdrop cancels. The dialog element itself fills the
     whole viewport, so compare against the inner box. */
  dlg.addEventListener('click', function (e) {
    if (e.target === dlg) dlg.close();
  });
})();
</script>

<?php admin_foot(); ?>
