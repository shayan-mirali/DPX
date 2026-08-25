<?php declare(strict_types=1); ?>
<!-- The eight parameters, presented as an instrument panel rather than a
     feature list. Each figure scrambles then locks, which is what a launch
     monitor actually looks like acquiring a shot. -->
<section id="tech" class="relative overflow-hidden py-24 sm:py-36">
  <!-- Faint measurement grid -->
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.055]"
       style="background-image:linear-gradient(var(--color-bone) 1px, transparent 1px), linear-gradient(90deg, var(--color-bone) 1px, transparent 1px);background-size:72px 72px;-webkit-mask-image:radial-gradient(80% 60% at 50% 40%, black, transparent 78%);mask-image:radial-gradient(80% 60% at 50% 40%, black, transparent 78%)"></div>

  <div class="relative mx-auto max-w-[1440px] px-5 sm:px-8">
    <div class="grid gap-12 lg:grid-cols-12 lg:gap-8">
      <div class="lg:col-span-5">
        <?= reveal_open() ?>
          <p class="eyebrow flex items-center gap-3 text-lime">
            <span class="h-px w-8 bg-lime/50"></span>
            Technology
          </p>
        <?= reveal_close() ?>

        <?= reveal_lines([
            'Measured,',
            'not <span class="text-gradient-lime">guessed.</span>',
        ], 'display t-h2 mt-7') ?>

        <?= reveal_open('', 200) ?>
          <p class="t-lead mt-8 max-w-[34rem] text-bone/60">
            Every shot is measured the moment it leaves the club. No guesswork,
            no &ldquo;that felt about right&rdquo; — just clear, accurate data on
            exactly what happens between the club face and the ball, on screen
            before it lands.
          </p>
        <?= reveal_close() ?>

        <?= reveal_open('', 300) ?>
          <div class="mt-10 flex items-center gap-5 rounded-2xl border border-bone/10 bg-bone/[0.03] p-5">
            <img src="assets/img/trackman-bone.png" alt="TrackMan" width="401" height="56"
                 loading="lazy" decoding="async" class="h-4 w-auto opacity-80">
            <p class="text-[0.82rem] leading-snug text-bone/45">
              Powered by TrackMan — trusted on tour, in coaching bays and by
              golfers worldwide.
            </p>
          </div>
        <?= reveal_close() ?>
      </div>

      <!-- Instrument panel -->
      <div class="lg:col-span-7">
        <div class="overflow-hidden rounded-3xl border border-bone/10 bg-ink-2/70 backdrop-blur-md">
          <div class="flex items-center justify-between border-b border-bone/10 px-5 py-3.5 sm:px-6">
            <span class="data text-[10px] uppercase tracking-[0.24em] text-bone/50">Shot Report</span>
            <span class="flex items-center gap-2">
              <span class="h-1.5 w-1.5 rounded-full bg-lime"></span>
              <span class="data text-[10px] uppercase tracking-[0.24em] text-lime">Tracking</span>
            </span>
          </div>

          <div class="grid grid-cols-2 gap-px bg-bone/[0.07] sm:grid-cols-4">
            <?php foreach (METRICS as $i => $m): ?>
              <?= reveal_open('group relative bg-ink-2 px-4 py-6 transition-colors duration-500 hover:bg-ink-3 sm:px-5 sm:py-7', $i * 55) ?>
                <div class="data text-[9.5px] uppercase leading-tight tracking-[0.16em] text-bone-dim"><?= e($m['key']) ?></div>
                <div class="mt-3 flex items-baseline gap-1">
                  <span class="counter data text-[1.55rem] leading-none text-bone transition-colors duration-500 group-hover:text-lime sm:text-[1.7rem]"
                        data-value="<?= e((string) $m['value']) ?>"
                        data-decimals="<?= (int) $m['decimals'] ?>"
                        data-duration="<?= 1300 + $i * 90 ?>"><?= e(number_format(0, (int) $m['decimals'])) ?></span>
                  <span class="data text-[10px] text-lime/70"><?= e($m['unit']) ?></span>
                </div>
                <span class="absolute bottom-0 left-0 h-px w-full origin-left scale-x-0 bg-lime transition-transform duration-700 [transition-timing-function:var(--ease-out-expo)] group-hover:scale-x-100"></span>
              <?= reveal_close() ?>
            <?php endforeach; ?>
          </div>

          <div class="border-t border-bone/10 px-5 py-3.5 sm:px-6">
            <p class="data text-[10px] uppercase tracking-[0.16em] text-bone/25">
              Illustrative readout — your numbers are your own
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
