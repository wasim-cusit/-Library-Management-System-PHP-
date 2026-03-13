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

$pageTitle = 'Login';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="auth-card">
    <h1>Login</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <form method="post" action="">
        <?= csrf_field() ?>
        <label>Email <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus></label>
        <label>Password <input type="password" name="password" required></label>
        <button type="submit">Login</button>
    </form>
    <p class="auth-link"><a href="<?= base_url('auth/register.php') ?>">Register</a> | <a href="<?= base_url() ?>">Home</a></p>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
