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
        $full_name = trim($_POST['full_name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $pdo = getDb();
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, bio = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$full_name ?: null, $bio ?: null, $user['id']]);
        $success = 'Profile updated.';
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

$pageTitle = 'My Profile';
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
        <label>Username <input type="text" value="<?= e($user['username']) ?>" disabled></label>
        <label>Email <input type="text" value="<?= e($user['email']) ?>" disabled></label>
        <label>Role <strong><?= e($user['role']) ?></strong></label>
        <label>Full name <input type="text" name="full_name" value="<?= e($user['full_name']) ?>"></label>
        <label>Bio <textarea name="bio" rows="4"><?= e($user['bio']) ?></textarea></label>
        <button type="submit">Update profile</button>
    </form>
    <?php if (in_array($user['role'], ['author', 'admin'], true)): ?>
        <p><a href="<?= base_url('author/') ?>">Manage my books (Author dashboard)</a></p>
    <?php endif; ?>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
