<?php
/**
 * GET /api/v1/books/download-file.php?token=... - serve file for download (temporary URL from download endpoint)
 */
require_once dirname(__DIR__) . '/config.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}
$raw = base64_decode($token, true);
if ($raw === false) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}
$parts = explode(':', $raw);
if (count($parts) !== 4) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}
$payload = $parts[0] . ':' . $parts[1] . ':' . $parts[2];
$sig = hash_hmac('sha256', $payload, JWT_SECRET);
if (!hash_equals($sig, $parts[3])) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}
$expires = (int) $parts[2];
if ($expires < time()) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Link expired']);
    exit;
}
$userId = (int) $parts[0];
$bookId = (int) $parts[1];

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, title, file_url FROM books WHERE id = ?');
$stmt->execute([$bookId]);
$book = $stmt->fetch();
if (!$book || !$book['file_url']) {
    header('HTTP/1.1 404 Not Found');
    exit;
}

$path = UPLOAD_BOOKS . '/' . $book['file_url'];
if (!is_file($path)) {
    header('HTTP/1.1 404 Not Found');
    exit;
}

$name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $book['title']) . '.' . pathinfo($book['file_url'], PATHINFO_EXTENSION);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
