<?php
/**
 * Shared chrome for every admin page.
 *
 * A fixed sidebar on desktop, collapsing to a slide-over on narrow
 * screens. admin_head() opens the document and draws the shell;
 * admin_foot() closes it.
 *
 * Deliberately plain CSS and a dozen lines of inline JS rather than any
 * framework: the dashboard should never need a build step to change, and
 * nothing here can affect the public site.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/helpers.php';
require_once __DIR__ . '/store.php';

/** Nav, grouped. Each entry: href, label, icon. */
const ADMIN_NAV = [
    'Manage' => [
        ['href' => 'index.php',    'label' => 'Overview',      'icon' => 'grid'],
        ['href' => 'site.php',     'label' => 'Venue details', 'icon' => 'pin'],
        ['href' => 'pricing.php',  'label' => 'Pricing',       'icon' => 'tag'],
        ['href' => 'sections.php', 'label' => 'Page content',  'icon' => 'text'],
    ],
    'Inbox' => [
        ['href' => 'enquiries.php', 'label' => 'Enquiries', 'icon' => 'inbox'],
    ],
];

/**
 * Inline SVG icons. Stroke-based at 1.6px so they sit at the same visual
 * weight as the label text beside them.
 */
function admin_icon(string $name): string
{
    $paths = [
        'grid'  => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'pin'   => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'tag'   => '<path d="M20.6 13.4 12 22l-9-9V4h9l8.6 8.6a1.9 1.9 0 0 1 0 2.8Z"/><circle cx="7.5" cy="7.5" r="1.3"/>',
        'text'  => '<path d="M4 6h16M4 12h16M4 18h10"/>',
        'inbox' => '<path d="M3 12h5l2 3h4l2-3h5"/><path d="M4.5 5h15l1.5 7v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Z"/>',
        'out'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        'ext'   => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/>',
        'menu'  => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close' => '<path d="M18 6 6 18M6 6l12 12"/>',
    ];

    $d = $paths[$name] ?? $paths['grid'];

    return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
        . 'stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . $d . '</svg>';
}

function admin_head(string $title, string $current = ''): void
{
    $flash = admin_take_flash();
    $count = count(admin_enquiries());
    ?>
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> · <?= e(SITE['name']) ?> admin</title>
<link rel="icon" href="../assets/img/icon.png" type="image/png">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<!-- Mobile bar. Hidden once the sidebar becomes permanent. -->
<div class="mobilebar">
  <button type="button" id="menu-open" class="iconbtn" aria-label="Open menu" aria-expanded="false" aria-controls="sidebar">
    <?= admin_icon('menu') ?>
  </button>
  <span class="mobilebar-title"><?= e($title) ?></span>
  <a class="iconbtn" href="../index.php" target="_blank" rel="noopener" aria-label="View site">
    <?= admin_icon('ext') ?>
  </a>
</div>

<div class="shell">
  <div class="scrim" id="scrim" hidden></div>

  <aside class="sidebar" id="sidebar">
    <div class="side-top">
      <a class="brand" href="index.php">
        <img src="../assets/img/dpx-bone.png" alt="<?= e(SITE['name']) ?>" width="695" height="443">
        <span>Admin</span>
      </a>
      <button type="button" id="menu-close" class="iconbtn only-mobile" aria-label="Close menu">
        <?= admin_icon('close') ?>
      </button>
    </div>

    <nav class="side-nav">
      <?php foreach (ADMIN_NAV as $group => $items): ?>
        <p class="side-group"><?= e($group) ?></p>
        <?php foreach ($items as $item): ?>
          <a href="<?= e($item['href']) ?>" class="side-link <?= $current === $item['href'] ? 'on' : '' ?>"
             <?= $current === $item['href'] ? 'aria-current="page"' : '' ?>>
            <?= admin_icon($item['icon']) ?>
            <span><?= e($item['label']) ?></span>
            <?php if ($item['href'] === 'enquiries.php' && $count > 0): ?>
              <span class="pill"><?= (int) $count ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>

    <div class="side-foot">
      <a class="side-link quiet" href="../index.php" target="_blank" rel="noopener">
        <?= admin_icon('ext') ?><span>View site</span>
      </a>
      <a class="side-link quiet" href="logout.php">
        <?= admin_icon('out') ?><span>Log out</span>
      </a>
    </div>
  </aside>

  <main class="content">
    <div class="content-in">
<?php if ($flash): ?>
      <p class="flash <?= e($flash['kind']) ?>"><?= e($flash['msg']) ?></p>
<?php endif; ?>
<?php
}

function admin_foot(): void
{
    ?>
    </div>
    <footer class="foot">
      Changes save to <code>storage/content.json</code> and appear on the site
      immediately. The shipped copy in <code>inc/defaults.php</code> is never
      modified, so <em>Discard all edits</em> always works.
    </footer>
  </main>
</div>

<script>
/* Slide-over sidebar on narrow screens. Everything else is CSS. */
(function () {
  var side = document.getElementById('sidebar');
  var scrim = document.getElementById('scrim');
  var open = document.getElementById('menu-open');
  var close = document.getElementById('menu-close');
  if (!side || !scrim || !open) return;

  function set(state) {
    side.classList.toggle('open', state);
    scrim.hidden = !state;
    open.setAttribute('aria-expanded', state ? 'true' : 'false');
    document.body.style.overflow = state ? 'hidden' : '';
  }

  open.addEventListener('click', function () { set(true); });
  if (close) close.addEventListener('click', function () { set(false); });
  scrim.addEventListener('click', function () { set(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') set(false);
  });
})();
</script>
</body>
</html>
<?php
}

/** A labelled text input. */
function field(string $name, string $label, string $value, string $hint = '', string $type = 'text'): void
{
    $id = 'f_' . preg_replace('/[^a-z0-9]+/i', '_', $name);
    ?>
  <label class="field" for="<?= e($id) ?>">
    <span class="lab"><?= e($label) ?></span>
    <input type="<?= e($type) ?>" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e($value) ?>">
    <?php if ($hint !== ''): ?><span class="hint"><?= $hint ?></span><?php endif; ?>
  </label>
<?php
}

/** A labelled textarea. */
function field_area(string $name, string $label, string $value, string $hint = '', int $rows = 3): void
{
    $id = 'f_' . preg_replace('/[^a-z0-9]+/i', '_', $name);
    ?>
  <label class="field" for="<?= e($id) ?>">
    <span class="lab"><?= e($label) ?></span>
    <textarea id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= (int) $rows ?>"><?= e($value) ?></textarea>
    <?php if ($hint !== ''): ?><span class="hint"><?= $hint ?></span><?php endif; ?>
  </label>
<?php
}

/** The save bar at the foot of every form. */
function save_bar(string $note = ''): void
{
    ?>
  <div class="savebar">
    <button type="submit" class="btn">Save changes</button>
    <?php if ($note !== ''): ?><span class="hint"><?= $note ?></span><?php endif; ?>
  </div>
<?php
}

function csrf_input(): void
{
    echo '<input type="hidden" name="csrf" value="' . e(admin_csrf_token()) . '">';
}
