<?php declare(strict_types=1); ?>
<!-- The rate card is a 4 × 4 matrix per period — players down, hours across.
     Shown whole on desktop, because comparing across it is the entire point
     of a rate card. On phones the same grid becomes an hours picker over four
     player rows, since sixteen price cells at 360px is either unreadable or a
     sideways scroll.

     BOTH periods are rendered server-side and toggled with CSS, so the table
     is complete without JavaScript and the switch costs no request. -->
<section id="pricing" class="relative overflow-hidden py-24 sm:py-36">
  <!-- Faint measurement grid, as on the shot report — a rate card is another readout. -->
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.04]"
       style="background-image:linear-gradient(var(--color-bone) 1px, transparent 1px), linear-gradient(90deg, var(--color-bone) 1px, transparent 1px);background-size:72px 72px;-webkit-mask-image:radial-gradient(75% 60% at 50% 45%, black, transparent 80%);mask-image:radial-gradient(75% 60% at 50% 45%, black, transparent 80%)"></div>

  <div class="relative mx-auto max-w-[1440px] px-5 sm:px-8">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <?= reveal_open() ?>
          <p class="eyebrow flex items-center gap-3 text-lime">
            <span class="h-px w-8 bg-lime/50"></span>
            Pricing
          </p>
        <?= reveal_close() ?>
        <?= reveal_lines([
            'By the hour,',
            'by the <span class="text-gradient-lime">bay.</span>',
        ], 'display t-h2 mt-7') ?>
      </div>

      <?= reveal_open('', 180) ?>
        <p class="max-w-[26rem] text-[0.95rem] leading-relaxed text-bone/55">
          One price for the bay, up to four players in it. Split four ways,
          an hour costs less each than a round of drinks.
        </p>
      <?= reveal_close() ?>
    </div>

    <!-- Period switch. Toggle buttons with aria-pressed, not a tablist: the
         same panel is rendered twice (desktop table, mobile list) and a
         tablist would need one unique panel id per tab to point at. -->
    <?= reveal_open('mt-12', 120) ?>
      <div role="group" aria-label="Pricing period"
           class="inline-flex rounded-full border border-bone/12 bg-ink-2/60 p-1 backdrop-blur-md">
        <?php foreach (PRICING['periods'] as $i => $p): $on = $i === 0; ?>
          <button type="button" data-period-btn="<?= e($p['id']) ?>" aria-pressed="<?= $on ? 'true' : 'false' ?>"
                  data-reticle="Switch"
                  class="rounded-full px-5 py-2.5 text-[0.85rem] font-medium transition-colors duration-400 sm:px-7 <?= $on ? 'bg-lime text-ink' : 'text-bone/60 hover:text-bone' ?>">
            <?= e($p['label']) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <?php foreach (PRICING['periods'] as $i => $p): ?>
        <p data-period-when="<?= e($p['id']) ?>" <?= $i === 0 ? '' : 'hidden' ?>
           class="data mt-4 text-[11px] uppercase tracking-[0.16em] text-bone/40"><?= e($p['when']) ?></p>
      <?php endforeach; ?>
    <?= reveal_close() ?>

    <!-- Desktop: the whole matrix -->
    <?php foreach (PRICING['periods'] as $i => $p): ?>
      <div data-period-panel="<?= e($p['id']) ?>" <?= $i === 0 ? '' : 'hidden' ?>>
        <?= reveal_open('mt-8 hidden overflow-hidden rounded-3xl border border-bone/10 bg-ink-2/70 backdrop-blur-md md:block', 200) ?>
          <table class="w-full border-collapse text-left">
            <caption class="sr-only">
              <?= e($p['label']) ?> — <?= e($p['when']) ?>. Bay prices by number of players and session length.
            </caption>
            <thead>
              <tr class="border-b border-bone/10">
                <th scope="col" class="data px-6 py-4 text-[10px] font-medium uppercase tracking-[0.2em] text-bone/50"><?= e($p['label']) ?></th>
                <?php foreach (PRICING['durations'] as $h): ?>
                  <th scope="col" class="data px-6 py-4 text-[10px] font-medium uppercase tracking-[0.2em] text-bone/50"><?= e(hours_word((int) $h)) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($p['rows'] as $row): ?>
                <tr class="group border-b border-bone/[0.07] transition-colors duration-500 last:border-b-0 hover:bg-ink-3/60">
                  <th scope="row" class="px-6 py-6 text-[0.95rem] font-medium text-bone/75"><?= e(players_label((int) $row['players'])) ?></th>
                  <?php foreach ($row['totals'] as $total): ?>
                    <td class="px-6 py-6">
                      <span class="data block text-[1.45rem] leading-none text-bone transition-colors duration-500 group-hover:text-lime"><?= e(gbp((float) $total)) ?></span>
                      <?php if ((int) $row['players'] > 1): ?>
                        <span class="data mt-1.5 block text-[11px] text-bone/40"><?= e(gbp(per_player((float) $total, (int) $row['players']))) ?> each</span>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?= reveal_close() ?>

        <!-- Mobile: pick the hours, read the rows -->
        <div class="mt-8 md:hidden">
          <div role="group" aria-label="Session length"
               class="grid grid-cols-4 gap-1.5 rounded-2xl border border-bone/12 bg-ink-2/60 p-1.5">
            <?php foreach (PRICING['durations'] as $hi => $h): $on = $hi === 0; ?>
              <button type="button" data-hours-btn="<?= (int) $hi ?>" aria-pressed="<?= $on ? 'true' : 'false' ?>"
                      class="data rounded-xl py-2.5 text-[11px] uppercase tracking-[0.12em] transition-colors duration-400 <?= $on ? 'bg-lime text-ink' : 'text-bone/55' ?>">
                <?= (int) $h ?> hr<?= (int) $h === 1 ? '' : 's' ?>
              </button>
            <?php endforeach; ?>
          </div>

          <ul class="mt-4 overflow-hidden rounded-3xl border border-bone/10 bg-ink-2/70">
            <?php foreach ($p['rows'] as $row): ?>
              <li class="flex items-baseline justify-between gap-4 border-b border-bone/[0.07] px-5 py-5 last:border-b-0">
                <span class="text-[0.95rem] text-bone/75"><?= e(players_label((int) $row['players'])) ?></span>
                <span class="text-right">
                  <?php foreach ($row['totals'] as $hi => $total): ?>
                    <span data-hours-cell="<?= (int) $hi ?>" <?= $hi === 0 ? '' : 'hidden' ?>>
                      <span class="data block text-[1.35rem] leading-none text-bone"><?= e(gbp((float) $total)) ?></span>
                      <?php if ((int) $row['players'] > 1): ?>
                        <span class="data mt-1.5 block text-[11px] text-bone/40"><?= e(gbp(per_player((float) $total, (int) $row['players']))) ?> each</span>
                      <?php endif; ?>
                    </span>
                  <?php endforeach; ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Conditions, then the ask -->
    <div class="mt-8 flex flex-col gap-8 sm:flex-row sm:items-end sm:justify-between">
      <?= reveal_open('', 120) ?>
        <ul class="flex flex-col gap-2.5">
          <?php foreach (PRICING['notes'] as $n): ?>
            <li class="flex items-start gap-3">
              <span class="mt-[7px] h-1 w-1 shrink-0 rotate-45 bg-lime/70"></span>
              <span class="max-w-[36rem] text-[0.88rem] leading-snug text-bone/55"><?= e($n) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?= reveal_close() ?>

      <?= reveal_open('', 200) ?>
        <a <?= attrs(booking_link_attrs()) ?> data-reticle="Book"
           class="group inline-flex items-center gap-2.5 rounded-full border border-bone/15 px-7 py-3.5 text-[0.9rem] font-medium text-bone transition-colors duration-400 hover:border-lime hover:bg-lime hover:text-ink">
          Book a Bay
          <span class="transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-x-1">→</span>
        </a>
      <?= reveal_close() ?>
    </div>
  </div>
</section>
