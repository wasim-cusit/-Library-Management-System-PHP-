<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$pdo = getDb();

$stats = [
    'users' => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'books' => $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn(),
    'themes' => $pdo->query('SELECT COUNT(*) FROM themes')->fetchColumn(),
    'views' => $pdo->query('SELECT COALESCE(SUM(view_count), 0) FROM books')->fetchColumn(),
    'downloads' => $pdo->query('SELECT COALESCE(SUM(download_count), 0) FROM books')->fetchColumn(),
];

$top_views = $pdo->query('SELECT b.id, b.title, b.view_count FROM books b ORDER BY b.view_count DESC LIMIT 5')->fetchAll();
$top_downloads = $pdo->query('SELECT b.id, b.title, b.download_count FROM books b ORDER BY b.download_count DESC LIMIT 5')->fetchAll();
$recent_users = $pdo->query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5')->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>Admin dashboard</h1>
<div class="toolbar">
  <a href="<?= base_url('admin/settings.php') ?>" class="btn">Site settings</a>
  <a href="<?= base_url('admin/themes.php') ?>" class="btn">Manage themes</a>
  <a href="<?= base_url('admin/users.php') ?>" class="btn">Manage users</a>
  <a href="<?= base_url('admin/api-docs.php') ?>" class="btn">API docs for developers</a>
</div>

<div class="admin-stats" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap:1rem; margin:1.5rem 0;">
  <div style="background:var(--card); padding:1rem; border-radius:8px;"><strong><?= $stats['users'] ?></strong><br>Users</div>
  <div style="background:var(--card); padding:1rem; border-radius:8px;"><strong><?= $stats['books'] ?></strong><br>Books</div>
  <div style="background:var(--card); padding:1rem; border-radius:8px;"><strong><?= $stats['themes'] ?></strong><br>Themes</div>
  <div style="background:var(--card); padding:1rem; border-radius:8px;"><strong><?= number_format($stats['views']) ?></strong><br>Total views</div>
  <div style="background:var(--card); padding:1rem; border-radius:8px;"><strong><?= number_format($stats['downloads']) ?></strong><br>Total downloads</div>
</div>

<div class="admin-two-col" style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem;">
  <div>
    <h3>Most viewed books</h3>
    <ul>
      <?php foreach ($top_views as $b): ?>
        <li><a href="<?= base_url('books/detail.php?id=' . $b['id']) ?>"><?= e($b['title']) ?></a> (<?= (int)$b['view_count'] ?>)</li>
      <?php endforeach; ?>
      <?php if (empty($top_views)): ?><li>—</li><?php endif; ?>
    </ul>
  </div>
  <div>
    <h3>Most downloaded</h3>
    <ul>
      <?php foreach ($top_downloads as $b): ?>
        <li><a href="<?= base_url('books/detail.php?id=' . $b['id']) ?>"><?= e($b['title']) ?></a> (<?= (int)$b['download_count'] ?>)</li>
      <?php endforeach; ?>
      <?php if (empty($top_downloads)): ?><li>—</li><?php endif; ?>
    </ul>
  </div>
</div>

<h3>Recent users</h3>
<div class="table-wrap">
<table>
  <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th>Action</th></tr></thead>
  <tbody>
    <?php foreach ($recent_users as $u): ?>
      <tr>
        <td><?= e($u['username']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td><?= e($u['role']) ?></td>
        <td><?= format_date($u['created_at']) ?></td>
        <td><a href="<?= base_url('admin/users.php?edit=' . $u['id']) ?>">Edit role</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
