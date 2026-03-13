<?php
/**
 * POST /api/v1/books/download.php - get temporary download URL (or trigger download). Body: {"book_id":1}
 * Returns: {"download_url":"...","expires_in":300}
 */
require_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') api_error('Method not allowed', 405);

$user = api_require_user();
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$bookId = (int) ($input['book_id'] ?? $_GET['book_id'] ?? 0);
if ($bookId < 1) api_error('book_id required', 400);

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, title, file_url, is_downloadable, is_free FROM books WHERE id = ?');
$stmt->execute([$bookId]);
$book = $stmt->fetch();
if (!$book || !$book['is_downloadable'] || !$book['file_url']) api_error('Download not available', 403);
if (!can_user_access_book($user['id'], $book)) api_error('Access required for this paid book', 403);

$path = UPLOAD_BOOKS . '/' . $book['file_url'];
if (!is_file($path)) api_error('File not found', 404);

$stmt = $pdo->prepare('INSERT INTO download_history (user_id, book_id) VALUES (?, ?)');
$stmt->execute([$user['id'], $bookId]);
$stmt = $pdo->prepare('UPDATE books SET download_count = download_count + 1 WHERE id = ?');
$stmt->execute([$bookId]);

$expires = time() + 300; // 5 min
$payload = $user['id'] . ':' . $bookId . ':' . $expires;
$sig = hash_hmac('sha256', $payload, JWT_SECRET);
$token = base64_encode($payload . ':' . $sig);
$downloadUrl = rtrim(SITE_BASE_URL, '/') . '/api/v1/books/download-file.php?token=' . urlencode($token);

api_json([
    'download_url' => $downloadUrl,
    'expires_in' => 300,
]);