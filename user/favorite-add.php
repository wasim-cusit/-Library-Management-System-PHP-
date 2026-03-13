<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$bookId = (int) ($_GET['book_id'] ?? 0);
if (!$bookId) {
    redirect(base_url('books/'));
}

$pdo = getDb();
$stmt = $pdo->prepare('INSERT IGNORE INTO favorites (user_id, book_id) VALUES (?, ?)');
$stmt->execute([current_user()['id'], $bookId]);
redirect(base_url('books/detail.php?id=' . $bookId));