<?php declare(strict_types=1); ?>
<section id="venue" class="relative py-24 sm:py-36">
  <div class="mx-auto max-w-[1440px] px-5 sm:px-8">
    <!-- Statement -->
    <div class="grid gap-10 lg:grid-cols-12">
      <div class="lg:col-span-5">
        <?= reveal_open() ?>
          <p class="eyebrow flex items-center gap-3 text-lime">
            <span class="h-px w-8 bg-lime/50"></span>
            The Venue
          </p>
        <?= reveal_close() ?>
      </div>

      <div class="lg:col-span-7">
        <?= reveal_lines([
            'Golf, indoors',
            'done <span class="text-gradient-lime">properly.</span>',
        ], 'display t-h2') ?>
        <?= reveal_open('', 220) ?>
          <p class="t-lead mt-8 max-w-[42rem] text-bone/60">
            Whether you&rsquo;re an experienced golfer chasing a number, a
            beginner picking up a club for the first time, or a group after a
            genuinely different night out — <?= e(SITE['name']) ?> was built for all of it.
          </p>
        <?= reveal_close() ?>
      </div>
    </div>

    <!-- The room -->
    <?= reveal_open('relative mt-16 sm:mt-24', 120, 'wipe') ?>
      <div class="relative aspect-[16/10] overflow-hidden rounded-3xl sm:aspect-[21/9]">
        <img src="assets/img/venue-wide.webp" loading="lazy" decoding="async"
             alt="Simulator bays, turf and lounge seating inside DPX Golf"
             class="h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-ink/75 via-transparent to-ink/25"></div>

        <!-- Annotation, in the language of the launch monitor -->
        <div class="absolute bottom-5 left-5 flex items-center gap-3 sm:bottom-8 sm:left-8">
          <span class="relative flex h-2.5 w-2.5">
            <span class="absolute inline-flex h-full w-full rounded-full bg-lime/60 [animation:pulse-ring_2.4s_ease-out_infinite]"></span>
            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-lime"></span>
          </span>
          <span class="data text-[10px] uppercase tracking-[0.22em] text-bone/80">
            <?= e(SITE['town']) ?> · Bays live
          </span>
        </div>
      </div>
    <?= reveal_close('wipe') ?>

    <!-- Reasons. The bento is deliberately uneven — a flat 3x2 grid of
         identical cards is the single most template-looking thing on the web. -->
    <div class="mt-6 grid auto-rows-[minmax(190px,auto)] grid-cols-1 gap-4 sm:mt-8 sm:grid-cols-2 lg:grid-cols-4">
      <?php foreach (FEATURES as $i => $f):
        $span = $f['span'] === 'wide' ? 'sm:col-span-2' : ($f['span'] === 'tall' ? 'lg:row-span-2' : ''); ?>
        <?= reveal_open('panel group relative flex flex-col justify-between overflow-hidden rounded-2xl p-6 sm:p-7 ' . $span, $i * 70) ?>
          <!-- Lime bloom that follows the card on hover -->
          <span class="pointer-events-none absolute -inset-px opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                style="background:radial-gradient(120% 100% at 50% 0%, rgba(198,242,78,.09), transparent 62%)"></span>
          <span class="data relative text-[11px] tracking-[0.2em] text-lime/70"><?= e($f['n']) ?></span>
          <div class="relative mt-10">
            <h3 class="display t-h3 leading-[1.05]"><?= e($f['title']) ?></h3>
            <p class="mt-3 max-w-[34rem] text-[0.95rem] leading-relaxed text-bone/55"><?= e($f['body']) ?></p>
          </div>
          <span class="absolute bottom-0 left-0 h-px w-full origin-left scale-x-0 bg-gradient-to-r from-lime to-transparent transition-transform duration-[900ms] [transition-timing-function:var(--ease-out-expo)] group-hover:scale-x-100"></span>
        <?= reveal_close() ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
