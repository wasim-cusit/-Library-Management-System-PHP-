<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$bookId = (int) ($_GET['book_id'] ?? 0);
$pdo = getDb();
$stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND book_id = ?');
$stmt->execute([current_user()['id'], $bookId]);
$back = $_GET['back'] ?? '';
if ($back === 'favorites') {
    redirect(base_url('user/favorites.php'));
}
redirect($bookId ? base_url('books/detail.php?id=' . $bookId) : base_url('user/favorites.php'));