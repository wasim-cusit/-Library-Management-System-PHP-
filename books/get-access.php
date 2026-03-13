<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    $_SESSION['error'] = 'Invalid request.';
    redirect(base_url('books/'));
}

$bookId = (int) ($_POST['book_id'] ?? 0);
if (!$bookId) {
    $_SESSION['error'] = 'Book required.';
    redirect(base_url('books/'));
}

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, title, is_free, access_duration_days FROM books WHERE id = ?');
$stmt->execute([$bookId]);
$book = $stmt->fetch();
if (!$book) {
    $_SESSION['error'] = 'Book not found.';
    redirect(base_url('books/detail.php?id=' . $bookId));
}

if ((int) $book['is_free'] === 1) {
    redirect(base_url('books/detail.php?id=' . $bookId));
}

$userId = current_user()['id'];
$durationDays = $book['access_duration_days'] === null ? null : (int) $book['access_duration_days'];
$expiresAt = null;
if ($durationDays !== null && $durationDays > 0) {
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $durationDays . ' days'));
}

$stmt = $pdo->prepare('
  INSERT INTO user_book_access (user_id, book_id, accessed_at, expires_at)
  VALUES (?, ?, NOW(), ?)
  ON DUPLICATE KEY UPDATE accessed_at = NOW(), expires_at = VALUES(expires_at)
');
$stmt->execute([$userId, $bookId, $expiresAt]);

$_SESSION['success'] = $expiresAt
    ? 'Access granted until ' . format_date($expiresAt) . '. You can now read and download.'
    : 'Lifetime access granted. You can now read and download.';
redirect(base_url('books/detail.php?id=' . $bookId));
