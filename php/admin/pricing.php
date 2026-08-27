<?php
/**
 * The rate card.
 *
 * Only the bay total is edited — the "£33 each" line under each price and
 * the price range in the Google listing data are both derived from it, so
 * there is exactly one number per cell to keep right.
 *
 * The grid is rendered from the existing shape rather than a fixed 4×4,
 * so adding a fifth duration or a fifth player row later means changing
 * defaults.php, not this page.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/auth.php';
admin_require_login();
require_once __DIR__ . '/inc/layout.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_csrf_check();

    $pricing = PRICING;
    $problems = [];

    foreach ($pricing['periods'] as $pi => $period) {
        $pricing['periods'][$pi]['label'] = post_str("period_label_$pi", $period['label']);
        $pricing['periods'][$pi]['when'] = post_str("period_when_$pi", $period['when']);

        foreach ($period['rows'] as $ri => $row) {
            foreach ($row['totals'] as $ti => $old) {
                $raw = post_str("price_{$pi}_{$ri}_{$ti}");
                // Tolerate a pasted "£84" or "84.00" — people will do both.
                $clean = str_replace(['£', ',', ' '], '', $raw);

                if ($clean === '' || !is_numeric($clean)) {
                    $problems[] = sprintf(
                        '%s, %s, %s is not a number',
                        $period['label'],
                        players_label((int) $row['players']),
                        hours_word((int) ($pricing['durations'][$ti] ?? $ti + 1))
                    );
                    continue;
                }

                $n = (float) $clean;
                if ($n < 0) {
                    $problems[] = 'a price cannot be negative';
                    continue;
                }

                // Keep whole pounds as int so the JSON stays tidy.
                $pricing['periods'][$pi]['rows'][$ri]['totals'][$ti] =
                    floor($n) === $n ? (int) $n : round($n, 2);
            }
        }
    }

    $pricing['vatNote'] = post_str('vatNote');
    $pricing['notes'] = compact_list(post_list('notes'));

    if ($problems) {
        admin_flash('Not saved — ' . implode('; ', array_unique($problems)) . '.', 'err');
    } elseif (admin_save_section('PRICING', $pricing)) {
        admin_flash('Pricing saved.');
    } else {
        admin_flash('Could not save. Check that storage/ is writable.', 'err');
    }

    header('Location: pricing.php');
    exit;
}

admin_head('Pricing', 'pricing.php');
?>

<h1>Pricing</h1>
<p class="lede">
  Enter the price for the <strong>whole bay</strong>. The “each” figure under
  every price on the site is worked out from it, so there is only ever one
  number to change.
</p>

<form method="post" action="pricing.php">
<?php csrf_input(); ?>

<?php foreach (PRICING['periods'] as $pi => $period): ?>
  <div class="card">
    <div class="grid two">
      <?php field("period_label_$pi", 'Period name', $period['label']); ?>
      <?php field("period_when_$pi", 'When it applies', $period['when'], 'Shown under the switch, e.g. “Monday – Friday · 10am – 4pm”.'); ?>
    </div>

    <div class="scroll-x" style="margin-top:18px">
      <table class="price-grid">
        <thead>
          <tr>
            <th></th>
            <?php foreach (PRICING['durations'] as $h): ?>
              <th style="text-align:right"><?= e(hours_word((int) $h)) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($period['rows'] as $ri => $row): ?>
            <tr>
              <td><?= e(players_label((int) $row['players'])) ?></td>
              <?php foreach ($row['totals'] as $ti => $total): ?>
                <td>
                  <input type="text" inputmode="decimal"
                         name="price_<?= (int) $pi ?>_<?= (int) $ri ?>_<?= (int) $ti ?>"
                         value="<?= e((string) $total) ?>"
                         aria-label="<?= e($period['label'] . ' ' . players_label((int) $row['players']) . ' ' . hours_word((int) PRICING['durations'][$ti])) ?>">
                  <?php if ((int) $row['players'] > 1): ?>
                    <span class="hint" style="text-align:right">
                      <?= e(gbp(per_player((float) $total, (int) $row['players']))) ?> each
                    </span>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>

<div class="card">
  <h2>Small print</h2>
  <?php field_area('vatNote', 'VAT line', PRICING['vatNote'], 'Shown on its own line directly under the table.', 2); ?>

  <div style="margin-top:18px">
    <span class="lab">Conditions</span>
    <span class="hint" style="margin-bottom:8px">
      One per box, shown as bullets under the table. Clear a box to remove it.
    </span>
    <?php
    $notes = PRICING['notes'];
    $notes[] = ''; // spare box for adding one
    foreach ($notes as $n): ?>
      <textarea name="notes[]" rows="2" style="margin-bottom:8px"><?= e($n) ?></textarea>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <h2>How it will read</h2>
  <span class="hint">
    Price range shown to Google: <strong><?= e(price_range()) ?></strong>.
    This updates itself from the table above.
  </span>
</div>

<?php save_bar('Prices go live the moment you save.'); ?>
</form>

<?php admin_foot(); ?>
