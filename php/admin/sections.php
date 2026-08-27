<?php
/**
 * The repeating content blocks: venue features, audience rows, roadmap
 * cards, the eight TrackMan figures and the scrolling ticker.
 *
 * Removing is an explicit "Remove this" checkbox rather than the older
 * trick of clearing the title — nobody would ever guess that. Adding is
 * an empty block at the end of each list: fill it in, save, and it
 * appears, with a fresh empty one ready next time.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/auth.php';
admin_require_login();
require_once __DIR__ . '/inc/layout.php';

/** Make a URL-ish id from a title, for a newly added card. */
function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    return trim($s, '-') ?: 'item';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf_check();

    $removed = 0;
    $added = 0;

    /* ---- Venue features ---- */
    $features = [];
    foreach (array_merge(FEATURES, [['title' => '', 'body' => '', 'span' => 'normal']]) as $i => $f) {
        $title = post_str("feat_title_$i");
        $isNew = $i >= count(FEATURES);

        if ($title === '') {
            continue; // an untouched "add" slot, or a card left blank
        }
        if (!$isNew && post_str("feat_remove_$i") !== '') {
            $removed++;
            continue;
        }
        if ($isNew) {
            $added++;
        }

        $span = post_str("feat_span_$i");
        $features[] = [
            'n' => str_pad((string) (count($features) + 1), 2, '0', STR_PAD_LEFT),
            'title' => $title,
            'body' => post_str("feat_body_$i"),
            'span' => in_array($span, ['wide', 'tall', 'normal'], true) ? $span : 'normal',
        ];
    }

    /* ---- Audiences ---- */
    $audiences = [];
    foreach (array_merge(AUDIENCES, [['id' => '', 'title' => '', 'body' => '', 'note' => '']]) as $i => $a) {
        $title = post_str("aud_title_$i");
        $isNew = $i >= count(AUDIENCES);

        if ($title === '') {
            continue;
        }
        if (!$isNew && post_str("aud_remove_$i") !== '') {
            $removed++;
            continue;
        }
        if ($isNew) {
            $added++;
        }

        $audiences[] = [
            'id' => $a['id'] !== '' ? $a['id'] : slugify($title),
            'title' => $title,
            'body' => post_str("aud_body_$i"),
            'note' => post_str("aud_note_$i"),
        ];
    }

    /* ---- Roadmap ---- */
    $roadmap = [];
    foreach (array_merge(ROADMAP, [['id' => '', 'title' => '', 'lede' => '', 'points' => [], 'cta' => 'Register interest']]) as $i => $r) {
        $title = post_str("road_title_$i");
        $isNew = $i >= count(ROADMAP);

        if ($title === '') {
            continue;
        }
        if (!$isNew && post_str("road_remove_$i") !== '') {
            $removed++;
            continue;
        }
        if ($isNew) {
            $added++;
        }

        $roadmap[] = [
            'id' => $r['id'] !== '' ? $r['id'] : slugify($title),
            'title' => $title,
            'lede' => post_str("road_lede_$i"),
            'points' => compact_list(post_list("road_points_$i")),
            'cta' => post_str("road_cta_$i", 'Register interest'),
        ];
    }

    /* ---- Metrics ---- */
    $metrics = [];
    $badMetric = false;
    foreach (METRICS as $i => $m) {
        $key = post_str("met_key_$i");
        if ($key === '' || post_str("met_remove_$i") !== '') {
            if ($key !== '') {
                $removed++;
            }
            continue;
        }
        $raw = str_replace([',', ' '], '', post_str("met_value_$i"));
        if ($raw === '' || !is_numeric($raw)) {
            $badMetric = true;
            continue;
        }
        $decimals = (int) post_str("met_decimals_$i", '0');
        $metrics[] = [
            'key' => $key,
            'value' => $decimals > 0 ? (float) $raw : (int) (float) $raw,
            'unit' => post_str("met_unit_$i"),
            'decimals' => max(0, min(3, $decimals)),
        ];
    }

    $ticker = compact_list(post_list('ticker'));

    if ($badMetric) {
        admin_flash('Nothing was saved — every readout needs a number in the Value box.', 'err');
        header('Location: sections.php');
        exit;
    }

    $all = admin_overrides();
    $all['FEATURES'] = $features;
    $all['AUDIENCES'] = $audiences;
    $all['ROADMAP'] = $roadmap;
    $all['METRICS'] = $metrics;
    $all['TICKER'] = $ticker;

    if (admin_write_overrides($all)) {
        $bits = [];
        if ($added) {
            $bits[] = $added === 1 ? '1 added' : "$added added";
        }
        if ($removed) {
            $bits[] = $removed === 1 ? '1 removed' : "$removed removed";
        }
        admin_flash('Saved.' . ($bits ? ' ' . ucfirst(implode(', ', $bits)) . '.' : ''));
    } else {
        admin_flash('Could not save. The storage folder on the server is not writable.', 'err');
    }

    header('Location: sections.php');
    exit;
}

admin_head('Page content', 'sections.php');
page_header(
    'Page content',
    'The cards and lists down the middle of the page. Fill in the empty box at the '
    . 'bottom of any group to add a new one, or tick <em>Remove this</em> to take one away.',
    'venue'
);
?>

<form method="post" action="sections.php">
<?php csrf_input(); ?>

<div class="card">
  <h2>Why come to DPX</h2>
  <span class="hint">The boxes underneath the big photo of the venue.</span>

  <?php
  $featureList = FEATURES;
  $featureList[] = ['n' => '', 'title' => '', 'body' => '', 'span' => 'normal'];
  foreach ($featureList as $i => $f):
      $isNew = $i >= count(FEATURES); ?>
    <div class="block <?= $isNew ? 'block-new' : '' ?>" style="margin-top:14px">
      <div class="block-head">
        <span class="block-num"><?= $isNew ? 'Add a new box' : e($f['n']) ?></span>
        <?php if (!$isNew): ?>
          <?php remove_toggle("feat_remove_$i", 'box'); ?>
        <?php endif; ?>
      </div>

      <div class="grid two">
        <?php field("feat_title_$i", 'Heading', $f['title'], $isNew ? 'Leave blank if you don&rsquo;t need another one.' : ''); ?>
        <label class="field">
          <span class="lab">Size on the page</span>
          <select name="feat_span_<?= (int) $i ?>">
            <?php foreach (['normal' => 'Normal', 'wide' => 'Wide — two columns', 'tall' => 'Tall — two rows'] as $v => $lab): ?>
              <option value="<?= e($v) ?>" <?= $f['span'] === $v ? 'selected' : '' ?>><?= e($lab) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>
      <div style="margin-top:14px">
        <?php field_area("feat_body_$i", 'Description', $f['body']); ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <h2>Who it&rsquo;s for</h2>
  <span class="hint">The list visitors can click open. The first one is already open when the page loads.</span>

  <?php
  $audList = AUDIENCES;
  $audList[] = ['id' => '', 'title' => '', 'body' => '', 'note' => ''];
  foreach ($audList as $i => $a):
      $isNew = $i >= count(AUDIENCES); ?>
    <div class="block <?= $isNew ? 'block-new' : '' ?>" style="margin-top:14px">
      <div class="block-head">
        <span class="block-num"><?= $isNew ? 'Add a new row' : e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
        <?php if (!$isNew): ?>
          <?php remove_toggle("aud_remove_$i", 'row'); ?>
        <?php endif; ?>
      </div>

      <div class="grid two">
        <?php field("aud_title_$i", 'Heading', $a['title'], $isNew ? 'Leave blank if you don&rsquo;t need another one.' : ''); ?>
        <?php field("aud_note_$i", 'Short note', $a['note'], 'The small line on the right, e.g. &ldquo;All ages welcome.&rdquo;'); ?>
      </div>
      <div style="margin-top:14px">
        <?php field_area("aud_body_$i", 'Description', $a['body']); ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <h2>Coming soon</h2>
  <span class="hint">
    Membership, coaching and competitions. Each button drops the visitor into
    the enquiry form with that subject already chosen.
  </span>

  <?php
  $roadList = ROADMAP;
  $roadList[] = ['id' => '', 'title' => '', 'lede' => '', 'points' => [], 'cta' => 'Register interest'];
  foreach ($roadList as $i => $r):
      $isNew = $i >= count(ROADMAP); ?>
    <div class="block <?= $isNew ? 'block-new' : '' ?>" style="margin-top:14px">
      <div class="block-head">
        <span class="block-num"><?= $isNew ? 'Add a new card' : e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
        <?php if (!$isNew): ?>
          <?php remove_toggle("road_remove_$i", 'card'); ?>
        <?php endif; ?>
      </div>

      <div class="grid two">
        <?php field("road_title_$i", 'Heading', $r['title'], $isNew ? 'Leave blank if you don&rsquo;t need another one.' : ''); ?>
        <?php field("road_cta_$i", 'Button wording', $r['cta']); ?>
      </div>
      <div style="margin-top:14px">
        <?php field_area("road_lede_$i", 'Intro line', $r['lede'], '', 2); ?>
      </div>
      <div style="margin-top:14px">
        <span class="lab">Bullet points</span>
        <span class="hint" style="margin-bottom:8px">Empty the box to remove a bullet. The last box adds a new one.</span>
        <?php
        $points = $r['points'];
        $points[] = '';
        foreach ($points as $pt): ?>
          <input type="text" name="road_points_<?= (int) $i ?>[]" value="<?= e($pt) ?>" style="margin-bottom:7px">
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <h2>The numbers panel</h2>
  <span class="hint">
    The readout in the Technology section. It&rsquo;s labelled on the site as an
    example, not a claim about anyone&rsquo;s golf.
  </span>

  <div class="scroll-x" style="margin-top:14px">
    <table>
      <thead>
        <tr>
          <th>Name</th><th>Value</th><th>Unit</th>
          <th>Decimal places</th><th>Remove</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (METRICS as $i => $m): ?>
          <tr>
            <td><input type="text" name="met_key_<?= (int) $i ?>" value="<?= e($m['key']) ?>"></td>
            <td><input type="text" inputmode="decimal" name="met_value_<?= (int) $i ?>" value="<?= e((string) $m['value']) ?>"></td>
            <td><input type="text" name="met_unit_<?= (int) $i ?>" value="<?= e($m['unit']) ?>" style="max-width:90px"></td>
            <td><input type="number" min="0" max="3" name="met_decimals_<?= (int) $i ?>" value="<?= (int) $m['decimals'] ?>" style="max-width:80px"></td>
            <td style="text-align:center">
              <input type="checkbox" name="met_remove_<?= (int) $i ?>" value="1" aria-label="Remove <?= e($m['key']) ?>">
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <h2>Scrolling words</h2>
  <span class="hint">The band that slides across under the main photo. Empty a box to remove that word; the last box adds one.</span>
  <div class="grid three" style="margin-top:14px">
    <?php
    $ticker = TICKER;
    $ticker[] = '';
    foreach ($ticker as $t): ?>
      <input type="text" name="ticker[]" value="<?= e($t) ?>">
    <?php endforeach; ?>
  </div>
</div>

<?php save_bar(); ?>
</form>

<?php admin_foot(); ?>
