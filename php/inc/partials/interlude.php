<?php
declare(strict_types=1);

/* "Better", "More" and "Differently" are the promise; accent those
 * rather than whatever an every-nth-word rule happens to land on. */
$words = explode(' ', SITE['tagline']);
?>
<section id="interlude" class="relative overflow-hidden border-y border-bone/10 py-20 sm:py-28">
  <div id="interlude-track" class="mx-auto max-w-[1180px] px-5 will-change-transform">
    <p class="display text-center text-[clamp(2rem,6.4vw,5.4rem)] leading-[0.95]">
      <?php foreach ($words as $i => $w):
        $hot = (bool) preg_match('/^(Better|More|Differently)/i', $w); ?>
        <span class="inline-block <?= $hot ? 'text-gradient-lime' : 'text-bone' ?>"><?= e($w) ?><?= $i < count($words) - 1 ? ' ' : '' ?></span>
      <?php endforeach; ?>
    </p>
  </div>

  <div class="mx-auto mt-16 max-w-[1440px] px-5 sm:px-8">
    <div class="grid gap-8 sm:grid-cols-12 sm:items-start">
      <?= reveal_open('sm:col-span-4') ?>
        <p class="eyebrow flex items-center gap-3 text-lime">
          <span class="h-px w-8 bg-lime/50"></span>
          Food &amp; Drink
        </p>
      <?= reveal_close() ?>
      <?= reveal_open('sm:col-span-8', 120) ?>
        <p class="t-lead max-w-[44rem] text-bone/60">
          A selection of refreshments while you play. Competitive round,
          social evening or just catching up with friends — the room is built
          to sit in as much as it is to swing in.
        </p>
      <?= reveal_close() ?>
    </div>
  </div>
</section>
