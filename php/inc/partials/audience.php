<?php declare(strict_types=1); ?>
<!-- Five audiences as an expanding register. Pointer users get it on hover,
     everyone gets it on click or keyboard — the open row is real state, not
     a CSS-only hover trick a keyboard user can never reach.

     Rows animate with grid-template-rows 0fr -> 1fr, which is the only way
     to transition to auto height without measuring anything. -->
<section id="who" class="relative py-24 sm:py-36">
  <div class="mx-auto max-w-[1440px] px-5 sm:px-8">
    <div class="grid gap-10 lg:grid-cols-12">
      <div class="lg:col-span-4">
        <?= reveal_open() ?>
          <p class="eyebrow flex items-center gap-3 text-lime">
            <span class="h-px w-8 bg-lime/50"></span>
            Who It&rsquo;s For
          </p>
        <?= reveal_close() ?>
        <?= reveal_lines([
            'Everyone',
            'who <span class="text-gradient-lime">swings.</span>',
        ], 'display t-h2 mt-7') ?>
        <?= reveal_open('', 220) ?>
          <p class="mt-7 max-w-[26rem] text-[0.95rem] leading-relaxed text-bone/55">
            Low handicapper or complete beginner, a team away-day or a
            birthday — the bay adapts to whoever is standing in it.
          </p>
        <?= reveal_close() ?>
      </div>

      <div class="lg:col-span-8">
        <ul id="audience" class="border-t border-bone/12">
          <?php foreach (AUDIENCES as $i => $a): $open = $i === 0; ?>
            <li class="border-b border-bone/12" data-audience-row data-open="<?= $open ? 'true' : 'false' ?>">
              <button type="button" aria-expanded="<?= $open ? 'true' : 'false' ?>"
                      data-reticle="<?= e($a['title']) ?>"
                      class="group flex w-full items-center gap-5 py-6 text-left sm:py-7">
                <span data-audience-num class="data shrink-0 text-[11px] tracking-[0.2em] transition-colors duration-500">
                  <?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?>
                </span>

                <span data-audience-title
                      class="display flex-1 text-[1.7rem] leading-none transition-all duration-[700ms] sm:text-[2.4rem]"
                      style="transition-timing-function:var(--ease-out-expo)">
                  <?= e($a['title']) ?>
                </span>

                <span data-audience-dot class="shrink-0 transition-all duration-[700ms]"
                      style="transition-timing-function:var(--ease-out-expo)">
                  <span class="block h-2 w-2 rounded-full bg-lime"></span>
                </span>
              </button>

              <div data-audience-panel class="grid transition-[grid-template-rows] duration-[700ms]"
                   style="transition-timing-function:var(--ease-out-expo)">
                <div class="overflow-hidden">
                  <div data-audience-body class="flex flex-col gap-3 pb-7 pl-11 pr-4 transition-opacity duration-500 sm:flex-row sm:items-end sm:justify-between sm:gap-10">
                    <p class="max-w-[38rem] text-[0.98rem] leading-relaxed text-bone/60"><?= e($a['body']) ?></p>
                    <span class="data shrink-0 text-[10px] uppercase tracking-[0.18em] text-lime/70"><?= e($a['note']) ?></span>
                  </div>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>
