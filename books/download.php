<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    redirect(base_url('books/'));
}

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, title, file_url, is_downloadable, is_free FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book || !$book['is_downloadable'] || empty($book['file_url'])) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Download not available for this book.';
    exit;
}
if (!can_user_access_book(current_user()['id'], $book)) {
    $_SESSION['error'] = 'You do not have access to this paid book. Get access from the book page.';
    redirect(base_url('books/detail.php?id=' . $id));
}

$user = current_user();
$stmt = $pdo->prepare('INSERT INTO download_history (user_id, book_id) VALUES (?, ?)');
$stmt->execute([$user['id'], $id]);
$stmt = $pdo->prepare('UPDATE books SET download_count = download_count + 1 WHERE id = ?');
$stmt->execute([$id]);

$path = UPLOAD_BOOKS . '/' . $book['file_url'];
if (!is_file($path)) {
    header('HTTP/1.1 404 Not Found');
    echo 'File not found.';
    exit;
}

$name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $book['title']) . '.' . pathinfo($book['file_url'], PATHINFO_EXTENSION);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
