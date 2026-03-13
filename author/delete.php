<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_author();

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    redirect(base_url('author/'));
}

if (!can_edit_book($id)) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$pdo = getDb();
$stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
$stmt->execute([$id]);
$_SESSION['message'] = 'Book deleted.';
redirect(base_url('author/'));
