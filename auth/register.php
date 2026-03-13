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

$pageTitle = 'Create account';
$pageDescription = 'Register for a free account to read books online, download, and save favorites. Authors can publish books.';
$authLayout = true;
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="auth-page-header">
    <div class="auth-page-hero" aria-hidden="true">
        <?php if (!empty($siteLogoUrl)): ?>
            <img src="<?= e($siteLogoUrl) ?>" alt="" class="auth-hero-img">
        <?php else: ?>
            <svg class="auth-hero-img auth-hero-svg" viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 8h20v52H20V8z" fill="var(--border)"/><path d="M25 15h10v38H25V15z" fill="var(--accent)" opacity="0.6"/><path d="M50 14h20v54H50V14z" fill="var(--border)"/><path d="M55 20h10v42H55V20z" fill="var(--accent)" opacity="0.5"/><path d="M80 10h20v58H80V10z" fill="var(--border)"/><path d="M85 18h10v44H85V18z" fill="var(--accent)" opacity="0.7"/><path d="M15 62l12-6 13 6 12-6 13 6 12-6 13 6 12-6" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
        <?php endif; ?>
    </div>
    <a href="<?= base_url() ?>" class="auth-site-brand">
        <?php if (!empty($siteLogoUrl)): ?>
            <img src="<?= e($siteLogoUrl) ?>" alt="" class="auth-site-logo">
        <?php else: ?>
            <span class="auth-site-icon" aria-hidden="true">📚</span>
        <?php endif; ?>
        <span class="auth-site-name"><?= e($siteName) ?></span>
    </a>
</div>
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
        <p class="auth-link">
            <a href="<?= base_url('auth/login.php') ?>">Login</a>
            <span class="auth-link-sep">|</span>
            <a href="<?= base_url() ?>">Home</a>
        </p>
    <?php endif; ?>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
