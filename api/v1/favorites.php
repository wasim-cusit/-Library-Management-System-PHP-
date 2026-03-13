<?php
/**
 * GET /api/v1/favorites.php - list user's favorite books (Bearer required)
 * POST /api/v1/favorites.php - add favorite. Body: {"book_id":1} or query book_id
 * DELETE /api/v1/favorites.php?book_id=1 - remove favorite
 */
require_once __DIR__ . '/config.php';

$user = api_require_user();
$pdo = getDb();
$baseUrl = rtrim(SITE_BASE_URL, '/');
$coverBase = $baseUrl . '/assets/uploads/covers';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('
      SELECT b.id, b.title, b.description, b.cover_url, b.is_free, b.is_downloadable, b.view_in_web, b.view_in_app,
        b.view_count AS views, b.download_count AS downloads, t.name AS theme_name, u.username AS author_name,
        (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.book_id = b.id) AS avg_rating
      FROM favorites f
      JOIN books b ON b.id = f.book_id
      JOIN themes t ON t.id = b.theme_id
      JOIN users u ON u.id = b.author_id
      WHERE f.user_id = ?
      ORDER BY f.created_at DESC
    ');
    $stmt->execute([$user['id']]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($books as &$b) {
        $b['cover_url'] = $b['cover_url'] ? $coverBase . '/' . $b['cover_url'] : null;
        $b['views'] = (int) $b['views'];
        $b['downloads'] = (int) $b['downloads'];
        $b['average_rating'] = round((float) $b['avg_rating'], 1);
        unset($b['avg_rating']);
    }
    api_json(['data' => $books]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $bookId = (int) ($input['book_id'] ?? $_GET['book_id'] ?? 0);
    if ($bookId < 1) api_error('book_id required', 400);
    $stmt = $pdo->prepare('SELECT id FROM books WHERE id = ?');
    $stmt->execute([$bookId]);
    if (!$stmt->fetch()) api_error('Book not found', 404);
    $pdo->prepare('INSERT IGNORE INTO favorites (user_id, book_id) VALUES (?, ?)')->execute([$user['id'], $bookId]);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM favorites WHERE book_id = ?');
    $stmt->execute([$bookId]);
    api_json(['message' => 'Book added to favorites', 'favorites_count' => (int) $stmt->fetchColumn()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $bookId = (int) ($_GET['book_id'] ?? 0);
    if ($bookId < 1) api_error('book_id required', 400);
    $stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND book_id = ?');
    $stmt->execute([$user['id'], $bookId]);
    api_json(['message' => 'Removed from favorites']);
}

api_error('Method not allowed', 405);
