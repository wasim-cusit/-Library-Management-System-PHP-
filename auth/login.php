<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect(base_url());
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid request.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) {
            $error = 'Email and password are required.';
        } else {
            $pdo = getDb();
            $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = (int) $user['id'];
                $redirect = $_SESSION['redirect_after_login'] ?? base_url();
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
            }
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Log in – Read books, download & favorites';
$pageDescription = 'Log in to read books online, download titles, and manage your favorites. Sign in to access the library.';
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
    <h1>Login</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <form method="post" action="">
        <?= csrf_field() ?>
        <label>Email <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus></label>
        <label>Password <input type="password" name="password" required></label>
        <button type="submit">Login</button>
    </form>
    <p class="auth-link">
        <a href="<?= base_url('auth/register.php') ?>">Register</a>
        <span class="auth-link-sep">|</span>
        <a href="<?= base_url() ?>">Home</a>
    </p>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
