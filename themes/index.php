<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pdo = getDb();
$stmt = $pdo->query('SELECT t.id, t.name, t.slug, t.description, COUNT(b.id) AS book_count FROM themes t LEFT JOIN books b ON b.theme_id = t.id GROUP BY t.id ORDER BY t.sort_order, t.name');
$themes = $stmt->fetchAll();

$pageTitle = 'Themes & courses – Browse books by category';
$pageDescription = 'Browse our book themes and categories. Find fiction, science, history and more.';
$currentNav = 'themes';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>Book themes</h1>
<p class="page-lead">Browse books by theme (category).</p>
<div class="themes-list">
  <?php foreach ($themes as $t): ?>
    <a href="<?= base_url('themes/view.php?slug=' . urlencode($t['slug'])) ?>">
      <?= e($t['name']) ?> (<?= (int) $t['book_count'] ?>)
    </a>
  <?php endforeach; ?>
</div>
<?php if (!empty($themes)): ?>
  <?php foreach ($themes as $t): ?>
    <?php if (!empty($t['description'])): ?>
      <div style="margin:1rem 0;">
        <strong><?= e($t['name']) ?></strong>: <?= e($t['description']) ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
<?php else: ?>
  <p>No themes yet.</p>
<?php endif; ?>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
