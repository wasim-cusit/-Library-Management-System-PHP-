<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$pdo = getDb();
$success = '';
$error = '';

// Update role (e.g. make user an author)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    if (csrf_verify()) {
        $userId = (int) $_POST['user_id'];
        $role = $_POST['role'] ?? '';
        if (in_array($role, ['user', 'author', 'admin'], true)) {
            $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
            $stmt->execute([$role, $userId]);
            $success = 'User role updated. New authors can add books from "My Books".';
        }
    }
}

$editId = (int) ($_GET['edit'] ?? 0);
$users = $pdo->query('SELECT id, username, email, full_name, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Manage users';
$pageRobots = 'noindex, nofollow';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>Manage users</h1>
<p>Set user role to <strong>author</strong> to allow them to add and manage their own books (title, description, theme, publisher, publish date, free/paid, downloadable, view on web/app).</p>
<?php if ($success): ?><p class="success"><?= e($success) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

<div class="table-wrap">
<table>
  <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th>Action</th></tr></thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= e($u['username']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td><?= e($u['role']) ?></td>
        <td><?= format_date($u['created_at']) ?></td>
        <td>
          <?php if ($editId === (int)$u['id']): ?>
            <form method="post" style="display:inline;">
              <?= csrf_field() ?>
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <select name="role">
                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="author" <?= $u['role'] === 'author' ? 'selected' : '' ?>>Author</option>
                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
              </select>
              <button type="submit" class="btn">Save</button>
            </form>
          <?php else: ?>
            <a href="<?= base_url('admin/users.php?edit=' . $u['id']) ?>">Edit role</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<p><a href="<?= base_url('admin/') ?>" class="btn btn-secondary">Back to dashboard</a></p>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
