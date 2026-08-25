<?php declare(strict_types=1); ?>
<footer class="relative overflow-hidden border-t border-bone/10 pt-16">
  <div class="mx-auto max-w-[1440px] px-5 sm:px-8">
    <div class="grid gap-10 pb-16 sm:grid-cols-2 lg:grid-cols-4">
      <div class="lg:col-span-2">
        <img src="assets/img/dpx-bone.png" alt="<?= e(SITE['name']) ?>" width="695" height="443"
             loading="lazy" decoding="async" class="h-10 w-auto">
        <p class="mt-6 max-w-[24rem] text-[0.95rem] leading-relaxed text-bone/50">
          <?= e(SITE['descriptor']) ?> in <?= e(SITE['town']) ?>. <?= e(SITE['tagline']) ?>
        </p>
      </div>

      <div>
        <h4 class="data text-[10px] uppercase tracking-[0.2em] text-bone-dim">Explore</h4>
        <ul class="mt-5 flex flex-col gap-3">
          <?php foreach (NAV as $n): ?>
            <li>
              <a href="<?= e($n['href']) ?>" class="text-[0.95rem] text-bone/60 transition-colors duration-300 hover:text-lime"><?= e($n['label']) ?></a>
            </li>
          <?php endforeach; ?>
          <li>
            <a href="#book" class="text-[0.95rem] text-bone/60 transition-colors duration-300 hover:text-lime">Book a Bay</a>
          </li>
        </ul>
      </div>

      <div>
        <h4 class="data text-[10px] uppercase tracking-[0.2em] text-bone-dim">Visit</h4>
        <address class="mt-5 flex flex-col gap-3 text-[0.95rem] not-italic text-bone/60">
          <span class="leading-relaxed">
            <?= e(SITE['address']['line1']) ?><br>
            <?= e(SITE['address']['line2']) ?><br>
            <?= e(SITE['address']['line3']) ?><br>
            <?= e(SITE['address']['town']) ?> <span class="data"><?= e(SITE['address']['postcode']) ?></span>
          </span>

          <?php foreach (SITE['emails'] as $em): ?>
            <a href="mailto:<?= e($em) ?>" class="break-all transition-colors hover:text-lime"><?= e($em) ?></a>
          <?php endforeach; ?>

          <?php if (!empty(SITE['phone']) && tel_href()): ?>
            <a href="<?= e(tel_href()) ?>" class="data transition-colors hover:text-lime"><?= e(SITE['phone']) ?></a>
          <?php endif; ?>

          <span class="text-bone/40"><?= e(hours_label()) ?></span>
        </address>
      </div>
    </div>

    <!-- Oversized wordmark, cropped by the viewport edge -->
    <div aria-hidden="true" class="relative select-none">
      <p class="display whitespace-nowrap text-center leading-[0.8] text-bone/[0.055]" style="font-size:clamp(4rem, 19vw, 17rem)">
        DPX GOLF
      </p>
    </div>

    <div class="flex flex-col gap-3 border-t border-bone/10 py-7 sm:flex-row sm:items-center sm:justify-between">
      <p class="data text-[10px] uppercase tracking-[0.18em] text-bone/35">
        © <?= e(date('Y')) ?> <?= e(SITE['name']) ?>
      </p>
      <p class="data text-[10px] uppercase tracking-[0.18em] text-bone/35">
        TrackMan is a trademark of its respective owner
      </p>
    </div>
  </div>
</footer>
