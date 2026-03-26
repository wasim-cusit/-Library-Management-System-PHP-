<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$pdo = getDb();
$error = '';
$success = '';

// Add theme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_theme'])) {
    if (csrf_verify()) {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name !== '') {
            $slug = slugify($name);
            try {
                $stmt = $pdo->prepare('INSERT INTO themes (name, slug, description, sort_order) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $slug, $description ?: null, (int)($_POST['sort_order'] ?? 0)]);
                $success = 'Theme added.';
            } catch (PDOException $e) {
                $error = 'Slug already exists or invalid.';
            }
        } else {
            $error = 'Name required.';
        }
    }
}

// Update theme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_theme'])) {
    if (csrf_verify()) {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($id && $name !== '') {
            $stmt = $pdo->prepare('UPDATE themes SET name = ?, description = ?, sort_order = ? WHERE id = ?');
            $stmt->execute([$name, $description ?: null, (int)($_POST['sort_order'] ?? 0), $id]);
            $success = 'Theme updated.';
        }
    }
}

$themes = $pdo->query('SELECT t.*, COUNT(b.id) AS book_count FROM themes t LEFT JOIN books b ON b.theme_id = t.id GROUP BY t.id ORDER BY t.sort_order, t.name')->fetchAll();

$pageTitle = 'Manage themes';
$pageRobots = 'noindex, nofollow';
$currentNav = 'admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>Manage themes</h1>
<?php if ($success): ?><p class="success"><?= e($success) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

<form method="post" style="margin:1rem 0;">
  <?= csrf_field() ?>
  <input type="hidden" name="add_theme" value="1">
  <label>New theme name <input type="text" name="name" required></label>
  <label>Description <textarea name="description" rows="2"></textarea></label>
  <label>Sort order <input type="number" name="sort_order" value="0"></label>
  <button type="submit" class="btn">Add theme</button>
</form>

<div class="table-wrap">
<table>
  <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Description</th><th>Books</th><th>Sort</th></tr></thead>
  <tbody>
    <?php foreach ($themes as $t): ?>
      <tr>
        <td><?= $t['id'] ?></td>
        <td><?= e($t['name']) ?></td>
        <td><?= e($t['slug']) ?></td>
        <td><?= e(mb_substr($t['description'] ?? '', 0, 50)) ?></td>
        <td><?= (int) $t['book_count'] ?></td>
        <td><?= (int) $t['sort_order'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<p><a href="<?= base_url('admin/') ?>" class="btn btn-secondary">Back to dashboard</a></p>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
