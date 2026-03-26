<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$user = current_user();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid request.';
    } else {
        $pdo = getDb();
        $action = $_POST['action'] ?? 'profile';

        if ($action === 'password') {
            $oldPassword = $_POST['old_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
                $error = 'All password fields are required.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New password and confirm password do not match.';
            } elseif ($oldPassword === $newPassword) {
                $error = 'New password must be different from old password.';
            } else {
                $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
                $stmt->execute([$user['id']]);
                $hash = (string) $stmt->fetchColumn();

                if (!$hash || !password_verify($oldPassword, $hash)) {
                    $error = 'Old password is incorrect.';
                } else {
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
                    $stmt->execute([$newHash, $user['id']]);
                    $success = 'Password updated successfully.';
                }
            }
        } else {
            $full_name = trim($_POST['full_name'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $stmt = $pdo->prepare('UPDATE users SET full_name = ?, bio = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$full_name ?: null, $bio ?: null, $user['id']]);
            $success = 'Profile updated.';
        }

        $user = current_user();
    }
}

// Count user's books (if author), favorites, reviews
$pdo = getDb();
$stats = ['books' => 0, 'favorites' => 0, 'reviews' => 0];
if (in_array($user['role'], ['author', 'admin'], true)) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM books WHERE author_id = ?');
    $stmt->execute([$user['id']]);
    $stats['books'] = (int) $stmt->fetchColumn();
}
$stmt = $pdo->prepare('SELECT COUNT(*) FROM favorites WHERE user_id = ?');
$stmt->execute([$user['id']]);
$stats['favorites'] = (int) $stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$stmt->execute([$user['id']]);
$stats['reviews'] = (int) $stmt->fetchColumn();

$pageTitle = 'My profile';
$currentNav = 'profile';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="container">
    <h1>My Profile</h1>
    <?php if ($success): ?><p class="success"><?= e($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <div class="profile-stats">
        <span>Favorites: <?= $stats['favorites'] ?></span>
        <span>Reviews: <?= $stats['reviews'] ?></span>
        <?php if ($stats['books'] > 0): ?><span>My books: <?= $stats['books'] ?></span><?php endif; ?>
    </div>
    <form method="post" class="profile-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="profile">
        <label>Username <input type="text" value="<?= e($user['username']) ?>" disabled></label>
        <label>Email <input type="text" value="<?= e($user['email']) ?>" disabled></label>
        <label>Role <strong><?= e($user['role']) ?></strong></label>
        <label>Full name <input type="text" name="full_name" value="<?= e($user['full_name']) ?>"></label>
        <label>Bio <textarea name="bio" rows="4"><?= e($user['bio']) ?></textarea></label>
        <button type="submit">Update profile</button>
    </form>
    <form method="post" class="profile-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="password">
        <h2>Change password</h2>
        <label>Old password <input type="password" name="old_password" required></label>
        <label>New password <input type="password" name="new_password" minlength="6" required></label>
        <label>Confirm new password <input type="password" name="confirm_password" minlength="6" required></label>
        <button type="submit">Change password</button>
    </form>
    <?php if (in_array($user['role'], ['author', 'admin'], true)): ?>
        <p><a href="<?= base_url('author/') ?>">Manage my books (Author dashboard)</a></p>
    <?php endif; ?>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
