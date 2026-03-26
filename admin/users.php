<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$pdo = getDb();
$success = '';
$error = '';

// Add new user (e.g. add an author)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (csrf_verify()) {
        $username = trim($_POST['new_username'] ?? '');
        $email = trim($_POST['new_email'] ?? '');
        $password = $_POST['new_password'] ?? '';
        $full_name = trim($_POST['new_full_name'] ?? '');
        $role = $_POST['new_role'] ?? 'user';
        if (strlen($username) < 2) $error = 'Username must be at least 2 characters.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Invalid email.';
        elseif (strlen($password) < 6) $error = 'Password must be at least 6 characters.';
        elseif (!in_array($role, ['user', 'author', 'admin'], true)) $error = 'Invalid role.';
        else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                $error = 'Email or username already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$username, $email, $hash, $full_name ?: null, $role]);
                $success = 'User added. ' . ($role === 'author' ? 'They can add books from "My Books".' : ($role === 'admin' ? 'They have admin access.' : ''));
            }
        }
    }
}

// Update role (e.g. make user an author)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && !isset($_POST['add_user'])) {
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
$currentNav = 'admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>Manage users</h1>
<p>Add new users (including <strong>authors</strong>) or change existing users’ roles. Authors can add and manage their own books from "My Books".</p>
<?php if ($success): ?><p class="success"><?= e($success) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

<section class="admin-add-user">
  <h2>Add user</h2>
  <form method="post" class="container">
    <?= csrf_field() ?>
    <input type="hidden" name="add_user" value="1">
    <label>Username <input type="text" name="new_username" value="<?= e($_POST['new_username'] ?? '') ?>" required minlength="2"></label>
    <label>Email <input type="email" name="new_email" value="<?= e($_POST['new_email'] ?? '') ?>" required></label>
    <label>Password <input type="password" name="new_password" required minlength="6"></label>
    <label>Full name <input type="text" name="new_full_name" value="<?= e($_POST['new_full_name'] ?? '') ?>"></label>
    <label>Role
      <select name="new_role">
        <option value="user" <?= ($_POST['new_role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
        <option value="author" <?= ($_POST['new_role'] ?? '') === 'author' ? 'selected' : '' ?>>Author</option>
        <option value="admin" <?= ($_POST['new_role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </label>
    <button type="submit" class="btn">Add user</button>
  </form>
</section>

<h2>Existing users</h2>
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
