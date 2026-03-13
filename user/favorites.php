<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$pdo = getDb();
$user = current_user();
$stmt = $pdo->prepare('
  SELECT b.id, b.title, b.cover_url, b.view_count, b.download_count, b.is_free, b.is_downloadable, b.view_in_web, b.view_in_app,
         t.name AS theme_name, u.username AS author_name
  FROM favorites f
  JOIN books b ON b.id = f.book_id
  JOIN themes t ON t.id = b.theme_id
  JOIN users u ON u.id = b.author_id
  WHERE f.user_id = ?
  ORDER BY f.created_at DESC
');
$stmt->execute([$user['id']]);
$books = $stmt->fetchAll();

$pageTitle = 'My Favorites';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>My Favorites</h1>
<?php if (empty($books)): ?>
  <p>No favorite books yet. <a href="<?= base_url('books/') ?>">Browse books</a> and add some.</p>
<?php else: ?>
  <div class="books-grid">
    <?php foreach ($books as $b): ?>
      <article class="book-card">
        <a href="<?= base_url('books/detail.php?id=' . $b['id']) ?>">
          <?php if (!empty($b['cover_url'])): ?><img class="book-cover" src="<?= e(COVER_URL . '/' . $b['cover_url']) ?>" alt=""><?php else: ?><div class="book-cover placeholder"></div><?php endif; ?>
          <div class="info">
            <h3 class="title"><?= e($b['title']) ?></h3>
            <p class="meta"><?= e($b['author_name']) ?> · <?= e($b['theme_name']) ?></p>
            <div class="book-badges">
              <span class="badge <?= $b['is_free'] ? 'free' : 'paid' ?>"><?= $b['is_free'] ? 'Free' : 'Paid' ?></span>
              <?php if ($b['is_downloadable']): ?><span class="badge downloadable">Download</span><?php endif; ?>
            </div>
          </div>
        </a>
        <div class="info" style="padding-top:0;">
          <a href="<?= base_url('user/favorite-remove.php?book_id=' . $b['id'] . '&back=favorites') ?>" class="btn btn-secondary" style="font-size:0.85rem;">Remove</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
