<?php
/**
 * The repeating content blocks: venue features, audience rows, roadmap
 * cards, the eight TrackMan figures and the scrolling ticker.
 *
 * Each block keeps its existing count. Emptying a title removes that
 * block entirely, which is the only removal gesture that needs no
 * JavaScript and cannot be triggered by accident on a single keystroke.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/auth.php';
admin_require_login();
require_once __DIR__ . '/inc/layout.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf_check();

    /* ---- Venue features ---- */
    $features = [];
    foreach (FEATURES as $i => $f) {
        $title = post_str("feat_title_$i");
        if ($title === '') {
            continue; // emptied title = delete this card
        }
        $features[] = [
            'n' => str_pad((string) (count($features) + 1), 2, '0', STR_PAD_LEFT),
            'title' => $title,
            'body' => post_str("feat_body_$i"),
            'span' => in_array(post_str("feat_span_$i"), ['wide', 'tall', 'normal'], true)
                ? post_str("feat_span_$i")
                : 'normal',
        ];
    }

    /* ---- Audiences ---- */
    $audiences = [];
    foreach (AUDIENCES as $i => $a) {
        $title = post_str("aud_title_$i");
        if ($title === '') {
            continue;
        }
        $audiences[] = [
            'id' => $a['id'],
            'title' => $title,
            'body' => post_str("aud_body_$i"),
            'note' => post_str("aud_note_$i"),
        ];
    }

    /* ---- Roadmap ---- */
    $roadmap = [];
    foreach (ROADMAP as $i => $r) {
        $title = post_str("road_title_$i");
        if ($title === '') {
            continue;
        }
        $roadmap[] = [
            'id' => $r['id'],
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
        if ($key === '') {
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

    /* ---- Ticker ---- */
    $ticker = compact_list(post_list('ticker'));

    if ($badMetric) {
        admin_flash('Not saved — every readout needs a numeric value.', 'err');
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
        admin_flash('Page content saved.');
    } else {
        admin_flash('Could not save. Check that storage/ is writable.', 'err');
    }

    header('Location: sections.php');
    exit;
}

admin_head('Page content', 'sections.php');
?>

<h1>Page content</h1>
<p class="lede">
  The cards and lists down the page. Clearing a title removes that block from
  the site — there is no separate delete button, so nothing goes in one click.
</p>

<form method="post" action="sections.php">
<?php csrf_input(); ?>

<div class="card">
  <h2>Venue features</h2>
  <span class="hint">The bento grid under the venue photograph.</span>

  <?php foreach (FEATURES as $i => $f): ?>
    <div class="block" style="margin-top:14px">
      <div class="block-head">
        <span class="block-num"><?= e($f['n']) ?></span>
        <select name="feat_span_<?= (int) $i ?>" style="max-width:190px">
          <?php foreach (['normal' => 'Normal width', 'wide' => 'Wide (2 columns)', 'tall' => 'Tall (2 rows)'] as $v => $lab): ?>
            <option value="<?= e($v) ?>" <?= $f['span'] === $v ? 'selected' : '' ?>><?= e($lab) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php field("feat_title_$i", 'Title', $f['title'], 'Clear to remove this card.'); ?>
      <div style="margin-top:12px">
        <?php field_area("feat_body_$i", 'Body', $f['body']); ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <h2>Who it's for</h2>
  <span class="hint">The expanding list. The first row is open by default on the site.</span>

  <?php foreach (AUDIENCES as $i => $a): ?>
    <div class="block" style="margin-top:14px">
      <div class="grid two">
        <?php field("aud_title_$i", 'Title', $a['title'], 'Clear to remove this row.'); ?>
        <?php field("aud_note_$i", 'Note', $a['note'], 'The small line on the right, e.g. “All ages welcome.”'); ?>
      </div>
      <div style="margin-top:12px">
        <?php field_area("aud_body_$i", 'Body', $a['body']); ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <h2>What's coming</h2>
  <span class="hint">The three “coming soon” cards. Each button drops the visitor into the enquiry form with that topic selected.</span>

  <?php foreach (ROADMAP as $i => $r): ?>
    <div class="block" style="margin-top:14px">
      <div class="grid two">
        <?php field("road_title_$i", 'Title', $r['title'], 'Clear to remove this card.'); ?>
        <?php field("road_cta_$i", 'Button label', $r['cta']); ?>
      </div>
      <div style="margin-top:12px">
        <?php field_area("road_lede_$i", 'Intro line', $r['lede'], '', 2); ?>
      </div>
      <div style="margin-top:14px">
        <span class="lab">Bullet points</span>
        <span class="hint" style="margin-bottom:8px">Clear a box to remove that bullet.</span>
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
  <h2>TrackMan readout</h2>
  <span class="hint">
    The instrument panel in the Technology section. These are labelled on the
    site as a sample readout, not a claim about anyone's golf.
  </span>

  <div class="scroll-x" style="margin-top:14px">
    <table>
      <thead>
        <tr><th>Name</th><th>Value</th><th>Unit</th><th>Decimals</th></tr>
      </thead>
      <tbody>
        <?php foreach (METRICS as $i => $m): ?>
          <tr>
            <td><input type="text" name="met_key_<?= (int) $i ?>" value="<?= e($m['key']) ?>"></td>
            <td><input type="text" inputmode="decimal" name="met_value_<?= (int) $i ?>" value="<?= e((string) $m['value']) ?>"></td>
            <td><input type="text" name="met_unit_<?= (int) $i ?>" value="<?= e($m['unit']) ?>" style="max-width:90px"></td>
            <td><input type="number" min="0" max="3" name="met_decimals_<?= (int) $i ?>" value="<?= (int) $m['decimals'] ?>" style="max-width:80px"></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <h2>Scrolling ticker</h2>
  <span class="hint">The band between the hero and the venue section. Clear a box to remove that word.</span>
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
