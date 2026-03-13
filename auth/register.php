<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect(base_url());
}

$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid request.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $register_as_author = !empty($_POST['register_as_author']);
        if (strlen($username) < 2) $error = 'Username must be at least 2 characters.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Invalid email.';
        elseif (strlen($password) < 6) $error = 'Password must be at least 6 characters.';
        else {
            $pdo = getDb();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                $error = 'Email or username already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = $register_as_author ? 'author' : 'user';
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$username, $email, $hash, $full_name ?: null, $role]);
                $success = true;
            }
        }
    }
}

$pageTitle = 'Register';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="auth-card">
    <h1>Register</h1>
    <?php if ($success): ?>
        <p class="success">Account created. You can now <a href="<?= base_url('auth/login.php') ?>">login</a>. <?= $register_as_author ? 'You are registered as an <strong>Author</strong> and can add books.' : '' ?></p>
    <?php else: ?>
        <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
        <form method="post" action="">
            <?= csrf_field() ?>
            <label>Username <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" required minlength="2"></label>
            <label>Email <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></label>
            <label>Full name <input type="text" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>"></label>
            <label>Password <input type="password" name="password" required minlength="6"></label>
            <label class="checkbox"><input type="checkbox" name="register_as_author" value="1" <?= !empty($_POST['register_as_author']) ? 'checked' : '' ?>> Register as <strong>Author</strong> (can add and manage your own books)</label>
            <button type="submit">Register</button>
        </form>
        <p class="auth-link"><a href="<?= base_url('auth/login.php') ?>">Login</a> | <a href="<?= base_url() ?>">Home</a></p>
    <?php endif; ?>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
