<?php
/**
 * Shared chrome for every admin page.
 *
 * admin_head() opens the document and draws the nav; admin_foot() closes
 * it. Deliberately plain CSS rather than Tailwind: the dashboard should
 * never need a build step to change, and a broken stylesheet here must
 * not be able to take the public site with it.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/helpers.php';
require_once __DIR__ . '/store.php';

const ADMIN_NAV = [
    ['href' => 'index.php',     'label' => 'Overview'],
    ['href' => 'site.php',      'label' => 'Venue details'],
    ['href' => 'pricing.php',   'label' => 'Pricing'],
    ['href' => 'sections.php',  'label' => 'Page content'],
    ['href' => 'enquiries.php', 'label' => 'Enquiries'],
];

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
<header class="topbar">
  <div class="wrap topbar-in">
    <a class="brand" href="index.php">
      <img src="../assets/img/dpx-bone.png" alt="" width="695" height="443">
      <span>Admin</span>
    </a>
    <div class="topbar-right">
      <a class="ghost" href="../index.php" target="_blank" rel="noopener">View site ↗</a>
      <a class="ghost" href="logout.php">Log out</a>
    </div>
  </div>
</header>

<nav class="tabs">
  <div class="wrap tabs-in">
    <?php foreach (ADMIN_NAV as $item): ?>
      <a href="<?= e($item['href']) ?>" class="<?= $current === $item['href'] ? 'on' : '' ?>">
        <?= e($item['label']) ?>
        <?php if ($item['href'] === 'enquiries.php' && $count > 0): ?>
          <span class="pill"><?= (int) $count ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</nav>

<main class="wrap">
<?php if ($flash): ?>
  <p class="flash <?= e($flash['kind']) ?>"><?= e($flash['msg']) ?></p>
<?php endif; ?>
<?php
}

function admin_foot(): void
{
    ?>
</main>
<footer class="foot">
  <div class="wrap">
    Changes save to <code>storage/content.json</code> and appear on the site immediately.
    The shipped copy in <code>inc/defaults.php</code> is never modified.
  </div>
</footer>
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
