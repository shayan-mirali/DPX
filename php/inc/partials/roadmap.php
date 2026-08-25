<?php declare(strict_types=1); ?>
<!-- Membership, Coaching and Competitions are all pre-launch. Three "coming
     soon" blocks would normally be dead weight, so they're framed as a
     development track with live status — the honest version is also the more
     interesting one, and every card converts into a signup rather than an
     apology. -->
<section id="coming" class="relative overflow-hidden py-24 sm:py-36">
  <!-- Sweep, like a radar pass over the track -->
  <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-1/2 h-px"
       style="background:linear-gradient(90deg, transparent, rgba(198,242,78,.35) 50%, transparent)"></div>

  <div class="relative mx-auto max-w-[1440px] px-5 sm:px-8">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <?= reveal_open() ?>
          <p class="eyebrow flex items-center gap-3 text-amber">
            <span class="h-px w-8 bg-amber/50"></span>
            In Development
          </p>
        <?= reveal_close() ?>
        <?= reveal_lines([
            'What&rsquo;s',
            'coming <span class="text-gradient-lime">next.</span>',
        ], 'display t-h2 mt-7') ?>
      </div>

      <?= reveal_open('', 180) ?>
        <p class="max-w-[26rem] text-[0.95rem] leading-relaxed text-bone/55">
          Three things are being built right now. Register your interest and
          you&rsquo;ll be first through the door when each one opens.
        </p>
      <?= reveal_close() ?>
    </div>

    <div class="mt-14 grid gap-4 lg:grid-cols-3">
      <?php foreach (ROADMAP as $i => $r): ?>
        <?= reveal_open('group relative flex flex-col overflow-hidden rounded-3xl border border-bone/10 bg-ink-2/60 p-7 backdrop-blur-md transition-colors duration-500 hover:border-lime/25 sm:p-8', $i * 110) ?>
          <!-- Status -->
          <div class="flex items-center justify-between">
            <span class="flex items-center gap-2.5">
              <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full rounded-full bg-amber/60 [animation:pulse-ring_2.6s_ease-out_infinite]"
                      style="animation-delay:<?= e((string) ($i * 0.5)) ?>s"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-amber"></span>
              </span>
              <span class="data text-[10px] uppercase tracking-[0.2em] text-amber">Coming soon</span>
            </span>
            <span class="data text-[11px] tracking-[0.2em] text-bone/25"><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
          </div>

          <h3 class="display mt-8 text-[1.9rem] leading-none sm:text-[2.2rem]"><?= e($r['title']) ?></h3>
          <p class="mt-3.5 text-[0.95rem] leading-relaxed text-bone/55"><?= e($r['lede']) ?></p>

          <ul class="mt-7 flex flex-1 flex-col gap-2.5">
            <?php foreach ($r['points'] as $pt): ?>
              <li class="flex items-start gap-3">
                <span class="mt-[7px] h-1 w-1 shrink-0 rotate-45 bg-lime/70"></span>
                <span class="text-[0.88rem] leading-snug text-bone/60"><?= e($pt) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>

          <button type="button" data-interest="<?= e($r['id']) ?>" data-reticle="Register"
                  class="mt-9 flex items-center justify-between rounded-full border border-bone/15 px-6 py-3.5 text-[0.9rem] font-medium text-bone transition-colors duration-400 hover:border-lime hover:bg-lime hover:text-ink">
            <?= e($r['cta']) ?>
            <span class="transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-x-1">→</span>
          </button>

          <span aria-hidden="true" class="pointer-events-none absolute -inset-px opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                style="background:radial-gradient(100% 70% at 50% 0%, rgba(198,242,78,.07), transparent 60%)"></span>
        <?= reveal_close() ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
