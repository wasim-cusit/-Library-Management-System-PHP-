<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_author();

$user = current_user();
$pdo = getDb();
$stmt = $pdo->prepare('
  SELECT b.id, b.title, b.cover_url, b.view_count, b.download_count, b.is_free, b.is_downloadable, b.view_in_web, b.view_in_app, b.created_at, t.name AS theme_name
  FROM books b
  JOIN themes t ON t.id = b.theme_id
  WHERE b.author_id = ?
  ORDER BY b.created_at DESC
');
$stmt->execute([$user['id']]);
$books = $stmt->fetchAll();

$pageTitle = 'My books – Author dashboard';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>My books</h1>
<p>As an <strong>author</strong> you can add and edit your own books (title, description, theme, publisher, publish date, free/paid, downloadable, view on web/app).</p>
<p><a href="<?= base_url('author/add.php') ?>" class="btn">Add new book</a></p>

<?php if (empty($books)): ?>
  <p>You have not added any books yet.</p>
<?php else: ?>
  <div class="table-wrap">
  <table>
    <thead>
      <tr><th>Cover</th><th>Title</th><th>Theme</th><th>Views</th><th>Downloads</th><th>Options</th></tr>
    </thead>
    <tbody>
      <?php foreach ($books as $b): ?>
        <tr>
          <td>
            <?php if (!empty($b['cover_url'])): ?>
              <img src="<?= e(COVER_URL . '/' . $b['cover_url']) ?>" alt="" style="width:50px;height:75px;object-fit:cover;">
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td><a href="<?= base_url('books/detail.php?id=' . $b['id']) ?>"><?= e($b['title']) ?></a></td>
          <td><?= e($b['theme_name']) ?></td>
          <td><?= (int) $b['view_count'] ?></td>
          <td><?= (int) $b['download_count'] ?></td>
          <td>
            <a href="<?= base_url('author/edit.php?id=' . $b['id']) ?>" class="btn btn-secondary">Edit</a>
            <a href="<?= base_url('author/delete.php?id=' . $b['id']) ?>" class="btn btn-secondary" data-confirm="Delete this book?">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
<?php endif; ?>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
