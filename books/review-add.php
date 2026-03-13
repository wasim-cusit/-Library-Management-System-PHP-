<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('books/'));
}
if (!csrf_verify()) {
    $_SESSION['error'] = 'Invalid request.';
    redirect(base_url('books/detail.php?id=' . (int)$_POST['book_id']));
}

$bookId = (int) ($_POST['book_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
if ($bookId < 1 || $rating < 1 || $rating > 5) {
    $_SESSION['error'] = 'Invalid input.';
    redirect(base_url('books/detail.php?id=' . $bookId));
}

$pdo = getDb();
$userId = current_user()['id'];
$stmt = $pdo->prepare('INSERT INTO reviews (book_id, user_id, rating, comment) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), updated_at = NOW()');
$stmt->execute([$bookId, $userId, $rating, $comment ?: null]);
redirect(base_url('books/detail.php?id=' . $bookId));
