<?php
/**
 * POST /api/v1/books/read.php - record a view (increment view count). Body: {"book_id":1} or book_id in query
 */
require_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') api_error('Method not allowed', 405);

$user = api_require_user();
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$bookId = (int) ($input['book_id'] ?? $_GET['book_id'] ?? 0);
if ($bookId < 1) api_error('book_id required', 400);

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, view_in_web, view_in_app, is_free FROM books WHERE id = ?');
$stmt->execute([$bookId]);
$book = $stmt->fetch();
if (!$book) api_error('Book not found', 404);
if (!can_user_access_book($user['id'], $book)) api_error('Access required for this paid book', 403);

$stmt = $pdo->prepare('INSERT INTO reading_history (user_id, book_id) VALUES (?, ?)');
$stmt->execute([$user['id'], $bookId]);
$stmt = $pdo->prepare('UPDATE books SET view_count = view_count + 1 WHERE id = ?');
$stmt->execute([$bookId]);
$stmt = $pdo->prepare('SELECT view_count FROM books WHERE id = ?');
$stmt->execute([$bookId]);
$totalViews = (int) $stmt->fetchColumn();

api_json(['message' => 'View recorded', 'total_views' => $totalViews]);
