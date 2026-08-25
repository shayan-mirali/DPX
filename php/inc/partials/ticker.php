<?php declare(strict_types=1); ?>
<!-- The eight tracked parameters. Duplicated once so the -50% keyframe loops seamlessly. -->
<div class="marquee relative overflow-hidden border-y border-bone/10 bg-ink-2/60 py-4">
  <div class="marquee-track" style="--dur:44s">
    <?php foreach (array_merge(TICKER, TICKER) as $label): ?>
      <span class="flex shrink-0 items-center gap-8 px-8">
        <span class="data text-[0.68rem] uppercase tracking-[0.26em] text-bone/55"><?= e($label) ?></span>
        <span class="h-1 w-1 rotate-45 bg-lime/60"></span>
      </span>
    <?php endforeach; ?>
  </div>

  <!-- Feather the ends so items enter and leave rather than pop. -->
  <div class="pointer-events-none absolute inset-y-0 left-0 w-24 bg-gradient-to-r from-ink to-transparent"></div>
  <div class="pointer-events-none absolute inset-y-0 right-0 w-24 bg-gradient-to-l from-ink to-transparent"></div>
</div>
