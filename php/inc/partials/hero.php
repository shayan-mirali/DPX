<?php
declare(strict_types=1);

/* The sample readout under the headline. Separate from METRICS because
 * it is a four-up summary, not the full eight-parameter panel. */
$readout = [
    ['k' => 'Ball Speed', 'v' => '167.4', 'u' => 'mph'],
    ['k' => 'Carry',      'v' => '289',   'u' => 'yds'],
    ['k' => 'Launch',     'v' => '12.8',  'u' => 'deg'],
    ['k' => 'Spin',       'v' => '2540',  'u' => 'rpm'],
];
?>
<section id="top" class="relative isolate min-h-[100svh] overflow-hidden">
  <!-- Venue plate. Parallaxed by JS; `will-change` keeps it on its own layer. -->
  <div id="hero-plate" class="absolute inset-0 -z-20 will-change-transform">
    <img src="assets/img/venue-wide.webp"
         alt="The simulator bays at DPX Golf, Burton upon Trent"
         fetchpriority="high" decoding="async"
         class="h-full w-full scale-105 object-cover object-[30%_center] opacity-90">
  </div>

  <!-- Grade. A single flat scrim would kill the room, so this is three
       targeted passes: a soft top so the nav clears, a left-weighted
       scrim carrying the headline, and a floor that hands off to the page
       background. The bright corridor on the right is left alone — it's
       the only real depth the photograph has. -->
  <div class="absolute inset-0 -z-10" style="background:linear-gradient(180deg, rgba(6,10,9,.85) 0%, rgba(6,10,9,.25) 26%, rgba(6,10,9,.45) 58%, rgba(6,10,9,.94) 88%, var(--color-ink) 100%)"></div>
  <div class="absolute inset-0 -z-10" style="background:linear-gradient(96deg, rgba(6,10,9,.93) 0%, rgba(6,10,9,.72) 34%, rgba(6,10,9,.18) 62%, transparent 85%)"></div>
  <div class="absolute inset-0 -z-10" style="background:radial-gradient(135% 95% at 42% 45%, transparent 42%, rgba(6,10,9,.7) 100%)"></div>

  <!-- Tracers. Held back on phones, where the arcs cross the body copy
       rather than sitting in clear space beside it as they do on desktop. -->
  <canvas id="shot-tracer" aria-hidden="true"
          class="absolute inset-0 -z-[5] h-full w-full opacity-45 [mix-blend-mode:screen] sm:opacity-100"></canvas>

  <div class="relative mx-auto flex min-h-[100svh] max-w-[1440px] flex-col justify-end px-5 pb-10 pt-[var(--nav-h)] sm:px-8 sm:pb-14">
    <div class="max-w-[62rem]">
      <p class="eyebrow mb-7 flex flex-wrap items-center gap-x-3 gap-y-1 text-lime"
         style="animation:hero-fade .9s var(--ease-out-expo) .05s both">
        <span class="inline-block h-1.5 w-1.5 rounded-full bg-lime"></span>
        <?= e(SITE['descriptor']) ?>
        <span class="text-bone/30">/</span>
        <?= e(SITE['town']) ?>
      </p>

      <h1 class="display t-hero">
        <span class="block overflow-hidden">
          <span class="block" style="animation:hero-line 1.15s var(--ease-out-expo) .15s both">Your next round</span>
        </span>
        <span class="block overflow-hidden">
          <span class="block" style="animation:hero-line 1.15s var(--ease-out-expo) .27s both">is <span class="text-gradient-lime">always on.</span></span>
        </span>
      </h1>

      <p class="t-lead mt-7 max-w-[46rem] text-bone/65"
         style="animation:hero-fade 1s var(--ease-out-expo) .55s both">
        Rain or shine, summer or winter. TrackMan-powered bays in the heart of
        <?= e(SITE['town']) ?> — play the world&rsquo;s great courses, practise against
        tour-level data, or just pull up a chair with friends.
      </p>

      <div class="mt-10 flex flex-wrap items-center gap-4"
           style="animation:hero-fade 1s var(--ease-out-expo) .7s both">
        <span class="magnetic inline-block will-change-transform transition-transform duration-[600ms] [transition-timing-function:var(--ease-out-expo)]" data-strength="0.24">
          <a <?= attrs(booking_link_attrs()) ?> data-reticle="Book a bay"
             class="group relative inline-flex items-center gap-3 overflow-hidden rounded-full bg-lime px-8 py-4 text-base font-semibold text-ink">
            <span class="relative z-10">Book a Bay</span>
            <span class="relative z-10 transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-x-1.5">→</span>
            <span class="absolute inset-0 translate-y-full bg-bone transition-transform duration-[600ms] [transition-timing-function:var(--ease-out-expo)] group-hover:translate-y-0"></span>
          </a>
        </span>

        <a href="#venue" data-reticle="Look inside"
           class="group inline-flex items-center gap-3 rounded-full px-7 py-4 text-base text-bone/80 hairline transition-colors duration-300 hover:border-bone/30 hover:text-bone">
          See the venue
          <span class="inline-block h-1.5 w-1.5 rounded-full bg-lime transition-transform duration-500 group-hover:scale-150"></span>
        </a>
      </div>
    </div>

    <!-- Live readout strip -->
    <div class="mt-14 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-bone/10 bg-bone/[0.06] sm:grid-cols-4"
         style="animation:hero-fade 1s var(--ease-out-expo) .9s both">
      <?php foreach ($readout as $r): ?>
        <div class="bg-ink/70 px-5 py-4 backdrop-blur-md">
          <div class="data text-[10px] uppercase tracking-[0.2em] text-bone-dim"><?= e($r['k']) ?></div>
          <div class="mt-1.5 flex items-baseline gap-1.5">
            <span class="data text-2xl text-bone sm:text-[1.75rem]"><?= e($r['v']) ?></span>
            <span class="data text-[11px] text-lime"><?= e($r['u']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="data mt-3 text-[10px] uppercase tracking-[0.18em] text-bone/25">Sample TrackMan readout</p>
  </div>
</section>
