<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pdo = getDb();
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$author = trim($_GET['author'] ?? '');
$sort = $_GET['sort'] ?? '-created';

$where = ['1=1'];
$params = [];
if ($q !== '') {
  $where[] = '(b.title LIKE ? OR b.description LIKE ?)';
  $params[] = '%' . $q . '%';
  $params[] = '%' . $q . '%';
}
if ($category !== '') {
  $where[] = 't.slug = ?';
  $params[] = $category;
}
if ($author !== '') {
  $where[] = 'u.username = ?';
  $params[] = $author;
}

$order = 'b.created_at DESC';
if ($sort === 'title') $order = 'b.title ASC';
elseif ($sort === '-views') $order = 'b.view_count DESC';
elseif ($sort === '-downloads') $order = 'b.download_count DESC';
elseif ($sort === '-rating') $order = 'avg_rating DESC, b.id';

$countSql = 'SELECT COUNT(*) FROM books b JOIN themes t ON t.id = b.theme_id WHERE ' . implode(' AND ', $where);
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$offset = ($page - 1) * $perPage;
$sql = '
  SELECT b.id, b.title, b.cover_url, b.view_count, b.download_count, b.is_free, b.is_downloadable, b.view_in_web, b.view_in_app,
         t.name AS theme_name, u.username AS author_name,
         (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.book_id = b.id) AS avg_rating
  FROM books b
  JOIN themes t ON t.id = b.theme_id
  JOIN users u ON u.id = b.author_id
  WHERE ' . implode(' AND ', $where) . '
  ORDER BY ' . $order . '
  LIMIT ' . $perPage . ' OFFSET ' . $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$themes = $pdo->query('SELECT id, name, slug FROM themes ORDER BY sort_order, name')->fetchAll();
$pagination = pagination($total, $page, $perPage, base_url('books/') . '?' . http_build_query(array_filter(['q' => $q, 'category' => $category, 'author' => $author, 'sort' => $sort])));

$pageTitle = 'Books – Browse, search & read online';
$pageDescription = 'Browse and search our book collection by theme and author. Read online or download. Free and paid books.';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>Books</h1>
<form method="get" class="toolbar" action="">
  <input type="hidden" name="category" value="<?= e($category) ?>">
  <input type="hidden" name="author" value="<?= e($author) ?>">
  <input type="hidden" name="sort" value="<?= e($sort) ?>">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search books…">
  <button type="submit" class="btn">Search</button>
</form>
<form method="get" class="toolbar">
  <input type="hidden" name="q" value="<?= e($q) ?>">
  <input type="hidden" name="author" value="<?= e($author) ?>">
  <label>Theme <select name="category">
    <option value="">All</option>
    <?php foreach ($themes as $t): ?>
      <option value="<?= e($t['slug']) ?>" <?= $category === $t['slug'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
    <?php endforeach; ?>
  </select></label>
  <label>Sort <select name="sort">
    <option value="-created" <?= $sort === '-created' ? 'selected' : '' ?>>Newest</option>
    <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title</option>
    <option value="-views" <?= $sort === '-views' ? 'selected' : '' ?>>Most views</option>
    <option value="-downloads" <?= $sort === '-downloads' ? 'selected' : '' ?>>Most downloads</option>
    <option value="-rating" <?= $sort === '-rating' ? 'selected' : '' ?>>Rating</option>
  </select></label>
  <button type="submit" class="btn">Apply</button>
</form>

<p class="books-count"><?= $total ?> book<?= $total !== 1 ? 's' : '' ?> found.</p>

<?php if (empty($books)): ?>
  <p>No books found.</p>
<?php else: ?>
  <div class="books-grid">
    <?php foreach ($books as $b): ?>
      <article class="book-card">
        <a href="<?= base_url('books/detail.php?id=' . $b['id']) ?>">
          <?php if (!empty($b['cover_url'])): ?><img class="book-cover" src="<?= e(COVER_URL . '/' . $b['cover_url']) ?>" alt="<?= e($b['title']) ?> cover"><?php else: ?><div class="book-cover placeholder" aria-hidden="true"></div><?php endif; ?>
          <div class="info">
            <h3 class="title"><?= e($b['title']) ?></h3>
            <p class="meta"><?= e($b['author_name']) ?> · <?= e($b['theme_name']) ?> · ★ <?= number_format((float)$b['avg_rating'], 1) ?></p>
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
      <?php if ($page > 1): ?>
        <a href="<?= get_pagination_link($pagination['base_url'], $page - 1) ?>">Previous</a>
      <?php endif; ?>
      <span class="current"><?= $page ?> / <?= $pagination['total_pages'] ?></span>
      <?php if ($page < $pagination['total_pages']): ?>
        <a href="<?= get_pagination_link($pagination['base_url'], $page + 1) ?>">Next</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
