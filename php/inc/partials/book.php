<?php
declare(strict_types=1);

$maps_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(address_one_line());

/* Server-rendered status, for the no-JavaScript path. enquiry.php redirects
 * back here with ?sent=1 or ?error=..., so the form still works and still
 * reports honestly with scripting off. JS hides this and uses the live
 * region below instead. */
$sent      = isset($_GET['sent']);
$err_code  = isset($_GET['error']) ? (string) $_GET['error'] : '';

$field = 'w-full rounded-xl border border-bone/12 bg-bone/[0.04] px-4 py-3.5 text-[0.95rem] text-bone placeholder:text-bone/30 transition-colors duration-300 focus:border-lime/60 focus:bg-bone/[0.06] focus:outline-none';
?>
<section id="book" class="relative overflow-hidden py-24 sm:py-36">
  <div aria-hidden="true" class="pointer-events-none absolute inset-0"
       style="background:radial-gradient(70% 55% at 50% 100%, rgba(198,242,78,.07), transparent 70%)"></div>

  <div class="relative mx-auto max-w-[1440px] px-5 sm:px-8">
    <div class="grid gap-14 lg:grid-cols-12 lg:gap-10">
      <!-- Pitch -->
      <div class="lg:col-span-5">
        <?= reveal_open() ?>
          <p class="eyebrow flex items-center gap-3 text-lime">
            <span class="h-px w-8 bg-lime/50"></span>
            Book Your Bay
          </p>
        <?= reveal_close() ?>

        <?= reveal_lines([
            'Bring your clubs.',
            'We&rsquo;ll do <span class="text-gradient-lime">the rest.</span>',
        ], 'display t-h2 mt-7') ?>

        <?= reveal_open('', 220) ?>
          <p class="t-lead mt-8 max-w-[34rem] text-bone/60">
            Eighteen holes, an hour on the range or a first ever swing —
            tell us what you&rsquo;re after and we&rsquo;ll get you booked in.
          </p>
        <?= reveal_close() ?>

        <?= reveal_open('', 300) ?>
          <dl class="mt-12 flex flex-col gap-6 border-t border-bone/12 pt-8">
            <div class="flex items-start justify-between gap-6">
              <dt class="data shrink-0 text-[10px] uppercase tracking-[0.2em] text-bone-dim">Where</dt>
              <dd class="text-right text-[0.95rem] leading-relaxed text-bone/75">
                <address class="not-italic">
                  <a href="<?= e($maps_url) ?>" target="_blank" rel="noopener noreferrer"
                     data-reticle="Map" class="transition-colors hover:text-lime">
                    <?= e(SITE['address']['line1']) ?><br>
                    <?= e(SITE['address']['line2']) ?><br>
                    <?= e(SITE['address']['line3']) ?><br>
                    <?= e(SITE['address']['town']) ?> <span class="data"><?= e(SITE['address']['postcode']) ?></span>
                  </a>
                </address>
              </dd>
            </div>

            <div class="flex items-start justify-between gap-6">
              <dt class="data shrink-0 text-[10px] uppercase tracking-[0.2em] text-bone-dim">Email</dt>
              <dd class="flex flex-col items-end gap-1 text-[0.95rem]">
                <?php foreach (SITE['emails'] as $em): ?>
                  <a href="mailto:<?= e($em) ?>" data-reticle="Email"
                     class="break-all text-right text-bone/75 transition-colors hover:text-lime"><?= e($em) ?></a>
                <?php endforeach; ?>
              </dd>
            </div>

            <?php if (!empty(SITE['phone']) && tel_href()): ?>
              <div class="flex items-baseline justify-between gap-6">
                <dt class="data shrink-0 text-[10px] uppercase tracking-[0.2em] text-bone-dim">Call</dt>
                <dd class="text-right text-[0.95rem]">
                  <a href="<?= e(tel_href()) ?>" data-reticle="Call"
                     class="data whitespace-nowrap text-bone/75 transition-colors hover:text-lime"><?= e(SITE['phone']) ?></a>
                </dd>
              </div>
            <?php endif; ?>

            <div class="flex items-baseline justify-between gap-6">
              <dt class="data shrink-0 text-[10px] uppercase tracking-[0.2em] text-bone-dim">Opening hours</dt>
              <dd class="text-right text-[0.95rem] text-bone/75">
                <span class="data whitespace-nowrap"><?= e(SITE['hours']['opens']) ?> – <?= e(SITE['hours']['closes']) ?></span>
                <span class="mt-0.5 block text-[0.8rem] text-bone/40"><?= e(days_label()) ?></span>
              </dd>
            </div>
          </dl>
        <?= reveal_close() ?>
      </div>

      <!-- Form. A real POST target, so it works with scripting off; JS
           upgrades it to an inline submit without a page change. -->
      <div class="lg:col-span-7">
        <?= reveal_open('rounded-3xl border border-bone/10 bg-ink-2/60 p-6 backdrop-blur-md sm:p-9', 120) ?>
          <form id="enquiry-form" method="post" action="enquiry.php"
                data-mailto="<?= e(SITE['emails'][0]) ?>"
                class="flex flex-col gap-5">
            <div class="grid gap-5 sm:grid-cols-2">
              <label class="flex flex-col gap-2">
                <span class="data text-[10px] uppercase tracking-[0.18em] text-bone-dim">Name</span>
                <input name="name" required autocomplete="name" placeholder="Your name" class="<?= e($field) ?>">
              </label>

              <label class="flex flex-col gap-2">
                <span class="data text-[10px] uppercase tracking-[0.18em] text-bone-dim">Email</span>
                <input name="email" type="email" required autocomplete="email" placeholder="you@example.com" class="<?= e($field) ?>">
              </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
              <label class="flex flex-col gap-2">
                <span class="data text-[10px] uppercase tracking-[0.18em] text-bone-dim">
                  Phone <span class="text-bone/25">(optional)</span>
                </span>
                <input name="phone" type="tel" autocomplete="tel" placeholder="07…" class="<?= e($field) ?>">
              </label>

              <label class="flex flex-col gap-2">
                <span class="data text-[10px] uppercase tracking-[0.18em] text-bone-dim">I&rsquo;m interested in</span>
                <select name="interest" class="<?= e($field) ?> appearance-none">
                  <?php foreach (INTERESTS as $i): ?>
                    <option value="<?= e($i['value']) ?>" class="bg-ink-2"><?= e($i['label']) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
            </div>

            <label class="flex flex-col gap-2">
              <span class="data text-[10px] uppercase tracking-[0.18em] text-bone-dim">
                Anything else? <span class="text-bone/25">(optional)</span>
              </span>
              <textarea name="message" rows="4"
                        placeholder="Group size, preferred day, whether you've played before…"
                        class="<?= e($field) ?> resize-none"></textarea>
            </label>

            <!-- Bot trap — real people never fill this in. -->
            <input type="text" name="company" tabindex="-1" autocomplete="off" aria-hidden="true"
                   class="absolute left-[-9999px] h-0 w-0 opacity-0">

            <button type="submit" data-reticle="Send"
                    class="group relative mt-2 inline-flex w-full items-center justify-center gap-3 overflow-hidden rounded-full bg-lime px-8 py-4 text-base font-semibold text-ink transition-opacity disabled:opacity-60 sm:w-auto sm:self-start">
              <span class="relative z-10" data-submit-label>Send enquiry</span>
              <span class="relative z-10 transition-transform duration-500 [transition-timing-function:var(--ease-out-expo)] group-hover:translate-x-1.5">→</span>
              <span class="absolute inset-0 translate-y-full bg-bone transition-transform duration-[600ms] [transition-timing-function:var(--ease-out-expo)] group-hover:translate-y-0"></span>
            </button>

            <p id="form-status" aria-live="polite" class="min-h-[1.4rem] text-[0.875rem]">
              <?php if ($sent): ?>
                <span class="text-lime">Thanks — that&rsquo;s with us. We&rsquo;ll be in touch shortly.</span>
              <?php elseif ($err_code === 'validation'): ?>
                <span class="text-amber">Please check your name and email address, then try again.</span>
              <?php elseif ($err_code !== ''): ?>
                <span class="text-amber">
                  We couldn&rsquo;t send that automatically — but nothing you typed is lost.
                  Please call or email us using the details on this page.
                </span>
              <?php endif; ?>
            </p>
          </form>
        <?= reveal_close() ?>
      </div>
    </div>
  </div>
</section>
