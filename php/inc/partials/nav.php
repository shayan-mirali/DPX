<?php declare(strict_types=1); ?>
<header id="nav" class="fixed inset-x-0 top-0 z-50 border-b border-transparent bg-transparent transition-all duration-500" style="transition-timing-function:var(--ease-out-expo)">
  <nav class="mx-auto flex h-[var(--nav-h)] max-w-[1440px] items-center justify-between px-5 sm:px-8">
    <a href="#top" aria-label="<?= e(SITE['name']) ?> — home" class="relative z-10 flex items-center">
      <img src="assets/img/dpx-bone.png" alt="<?= e(SITE['name']) ?>" width="695" height="443" class="h-8 w-auto sm:h-9">
    </a>

    <ul class="hidden items-center gap-9 lg:flex">
      <?php foreach (NAV as $item): ?>
        <li>
          <a href="<?= e($item['href']) ?>" data-reticle="Go"
             class="group relative text-[0.9rem] text-bone/70 transition-colors duration-300 hover:text-bone">
            <?= e($item['label']) ?>
            <span class="absolute -bottom-1.5 left-0 h-px w-full origin-right scale-x-0 bg-lime transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:origin-left group-hover:scale-x-100"></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="flex items-center gap-3">
      <!-- The wrapper owns the responsive display. Putting `hidden` on the
           magnetic element itself would collide with its inline-block. -->
      <div class="hidden sm:block">
        <span class="magnetic inline-block will-change-transform transition-transform duration-[600ms] [transition-timing-function:var(--ease-out-expo)]" data-strength="0.24">
          <a <?= attrs(booking_link_attrs()) ?> data-reticle="Book"
             class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-lime px-5 py-2.5 text-[0.85rem] font-semibold text-ink transition-colors duration-300">
            <span class="relative z-10">Book a Bay</span>
            <span class="relative z-10 transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-x-1">→</span>
            <span class="absolute inset-0 translate-y-full bg-bone transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-y-0"></span>
          </a>
        </span>
      </div>

      <button id="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-sheet" aria-label="Open menu"
              class="relative z-10 flex h-10 w-10 items-center justify-center rounded-full hairline lg:hidden">
        <span class="relative block h-3 w-4">
          <span data-bar="top" class="absolute left-0 block h-px w-full bg-bone transition-all duration-400" style="top:1px"></span>
          <span data-bar="bottom" class="absolute left-0 block h-px w-full bg-bone transition-all duration-400" style="top:10px"></span>
        </span>
      </button>
    </div>
  </nav>
</header>

<!-- Mobile sheet -->
<div id="nav-sheet" class="fixed inset-0 z-40 bg-ink-2 transition-[clip-path] duration-[850ms] lg:hidden" aria-hidden="true"
     style="transition-timing-function:var(--ease-in-out-quint);clip-path:inset(0 0 100% 0);pointer-events:none">
  <div class="flex h-full flex-col justify-between px-6 pb-10 pt-[calc(var(--nav-h)+3rem)]">
    <ul class="flex flex-col gap-2">
      <?php foreach (NAV as $i => $item): ?>
        <li class="overflow-hidden">
          <a href="<?= e($item['href']) ?>" data-sheet-link
             class="display block translate-y-[110%] py-2 text-[2.6rem] leading-tight text-bone transition-transform duration-700"
             style="transition-timing-function:var(--ease-out-expo);--i:<?= (int) $i ?>">
            <?= e($item['label']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <a <?= attrs(booking_link_attrs()) ?> data-sheet-link
       class="flex items-center justify-between rounded-full bg-lime px-7 py-4 text-base font-semibold text-ink">
      Book a Bay <span>→</span>
    </a>
  </div>
</div>
