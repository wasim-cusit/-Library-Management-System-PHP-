<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    redirect(base_url('themes/'));
}

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, name, slug, description FROM themes WHERE slug = ?');
$stmt->execute([$slug]);
$theme = $stmt->fetch();
if (!$theme) {
    header('HTTP/1.1 404 Not Found');
    echo 'Theme not found.';
    exit;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$stmt = $pdo->prepare('SELECT COUNT(*) FROM books WHERE theme_id = ?');
$stmt->execute([$theme['id']]);
$total = (int) $stmt->fetchColumn();
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare('
  SELECT b.id, b.title, b.cover_url, b.view_count, b.download_count, b.is_free, b.is_downloadable, b.view_in_web, b.view_in_app, u.username AS author_name,
         (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.book_id = b.id) AS avg_rating
  FROM books b
  JOIN users u ON u.id = b.author_id
  WHERE b.theme_id = ?
  ORDER BY b.created_at DESC
  LIMIT ' . $perPage . ' OFFSET ' . $offset
);
$stmt->execute([$theme['id']]);
$books = $stmt->fetchAll();
$pagination = pagination($total, $page, $perPage, base_url('themes/view.php?slug=' . urlencode($slug)));

$pageTitle = $theme['name'] . ' – Books';
$pageDescription = !empty($theme['description']) ? mb_substr($theme['description'], 0, 160) : ('Books in ' . $theme['name']);
$currentNav = 'themes';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1><?= e($theme['name']) ?></h1>
<?php if (!empty($theme['description'])): ?><p class="page-lead"><?= e($theme['description']) ?></p><?php endif; ?>
<p><?= $total ?> book(s).</p>

<?php if (empty($books)): ?>
  <p>No books in this theme yet.</p>
<?php else: ?>
  <div class="books-grid">
    <?php foreach ($books as $b): ?>
      <article class="book-card">
        <a href="<?= base_url('books/detail.php?id=' . $b['id']) ?>" class="book-card-link">
          <?php if (!empty($b['cover_url'])): ?><img class="book-cover" src="<?= e(COVER_URL . '/' . $b['cover_url']) ?>" alt="<?= e($b['title']) ?> cover"><?php else: ?><div class="book-cover placeholder" aria-hidden="true"></div><?php endif; ?>
          <div class="info">
            <h3 class="title"><?= e($b['title']) ?></h3>
            <p class="meta"><?= e($b['author_name']) ?> · ★ <?= number_format((float)$b['avg_rating'], 1) ?></p>
            <div class="book-badges">
              <span class="badge <?= $b['is_free'] ? 'free' : 'paid' ?>"><?= $b['is_free'] ? 'Free' : 'Paid' ?></span>
              <?php if ($b['is_downloadable']): ?><span class="badge downloadable">Download</span><?php endif; ?>
              <?php if ($b['view_in_web']): ?><span class="badge web">Web</span><?php endif; ?>
              <?php if ($b['view_in_app']): ?><span class="badge app">App</span><?php endif; ?>
            </div>
          </div>
        </a>
      </article>
    <?php endforeach; ?>
  </div>
  <?php if ($pagination['total_pages'] > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?><a href="<?= get_pagination_link($pagination['base_url'], $page - 1) ?>">Previous</a><?php endif; ?>
      <span class="current"><?= $page ?> / <?= $pagination['total_pages'] ?></span>
      <?php if ($page < $pagination['total_pages']): ?><a href="<?= get_pagination_link($pagination['base_url'], $page + 1) ?>">Next</a><?php endif; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
