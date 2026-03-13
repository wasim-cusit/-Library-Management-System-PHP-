<?php
/**
 * GET /api/v1/reviews.php?book_id=1 - list reviews for a book (paginated)
 * POST /api/v1/reviews.php - add review. Body: {"book_id":1,"rating":5,"comment":"..."} (Bearer required)
 */
require_once __DIR__ . '/config.php';

$pdo = getDb();
$bookId = (int) ($_GET['book_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($bookId < 1) api_error('book_id required', 400);
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = min(50, max(1, (int) ($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE book_id = ?');
    $stmt->execute([$bookId]);
    $total = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT r.id, r.user_id, r.rating, r.comment, r.created_at, r.updated_at, u.username FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.book_id = ? ORDER BY r.created_at DESC LIMIT ' . $limit . ' OFFSET ' . $offset);
    $stmt->execute([$bookId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    api_json([
        'data' => $reviews,
        'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => (int) ceil($total / $limit)],
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = api_require_user();
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $bookId = (int) ($input['book_id'] ?? 0);
    $rating = (int) ($input['rating'] ?? 0);
    $comment = trim($input['comment'] ?? '');
    if ($bookId < 1 || $rating < 1 || $rating > 5) api_error('book_id and rating (1-5) required', 400);
    $stmt = $pdo->prepare('SELECT id FROM books WHERE id = ?');
    $stmt->execute([$bookId]);
    if (!$stmt->fetch()) api_error('Book not found', 404);
    $stmt = $pdo->prepare('INSERT INTO reviews (book_id, user_id, rating, comment) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), updated_at = NOW()');
    $stmt->execute([$bookId, $user['id'], $rating, $comment ?: null]);
    $stmt = $pdo->prepare('SELECT id, user_id, rating, comment, created_at, updated_at FROM reviews WHERE book_id = ? AND user_id = ?');
    $stmt->execute([$bookId, $user['id']]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);
    $review['username'] = $user['username'];
    http_response_code(201);
    api_json($review);
}

api_error('Method not allowed', 405);
