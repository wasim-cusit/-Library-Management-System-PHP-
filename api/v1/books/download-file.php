<?php
/**
 * GET /api/v1/books/download-file.php?token=... - serve file for download (temporary URL from download endpoint)
 */
require_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_error('Method not allowed', 405, 'METHOD_NOT_ALLOWED');
}

$token = $_GET['token'] ?? '';
if ($token === '') {
    api_error('Missing token', 403, 'MISSING_TOKEN');
}
$raw = base64_decode($token, true);
if ($raw === false) {
    api_error('Invalid token', 403, 'INVALID_TOKEN');
}
$parts = explode(':', $raw);
if (count($parts) !== 4) {
    api_error('Invalid token payload', 403, 'INVALID_TOKEN');
}
$payload = $parts[0] . ':' . $parts[1] . ':' . $parts[2];
$sig = hash_hmac('sha256', $payload, JWT_SECRET);
if (!hash_equals($sig, $parts[3])) {
    api_error('Invalid token signature', 403, 'INVALID_TOKEN');
}
$expires = (int) $parts[2];
if ($expires < time()) {
    api_error('Link expired', 403, 'LINK_EXPIRED');
}
$userId = (int) $parts[0];
$bookId = (int) $parts[1];

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, title, file_url FROM books WHERE id = ?');
$stmt->execute([$bookId]);
$book = $stmt->fetch();
if (!$book || !$book['file_url']) {
    api_error('Book file not found', 404, 'FILE_NOT_FOUND');
}

$path = UPLOAD_BOOKS . '/' . $book['file_url'];
if (!is_file($path)) {
    api_error('File not found', 404, 'FILE_NOT_FOUND');
}

$name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $book['title']) . '.' . pathinfo($book['file_url'], PATHINFO_EXTENSION);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
