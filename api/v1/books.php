<?php
/**
 * GET /api/v1/books.php - list books (paginated, filterable)
 * GET /api/v1/books.php?id=1 - single book detail
 * Query: page, limit, category (theme slug), q (search), sort (title, -views, -downloads, -rating, -created)
 */
require_once __DIR__ . '/config.php';

$pdo = getDb();
$baseUrl = rtrim(SITE_BASE_URL, '/');
$coverBase = $baseUrl . '/assets/uploads/covers';

// Single book
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare('
      SELECT b.*, t.name AS theme_name, t.slug AS theme_slug,
             u.username AS author_username, u.full_name AS author_name,
             p.name AS publisher_name,
             (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.book_id = b.id) AS average_rating,
             (SELECT COUNT(*) FROM reviews r WHERE r.book_id = b.id) AS total_reviews
      FROM books b
      JOIN themes t ON t.id = b.theme_id
      JOIN users u ON u.id = b.author_id
      LEFT JOIN publishers p ON p.id = b.publisher_id
      WHERE b.id = ?
    ');
    $stmt->execute([$id]);
    $book = $stmt->fetch();
    if (!$book) api_error('Book not found', 404);

    $book['cover_url'] = $book['cover_url'] ? $coverBase . '/' . $book['cover_url'] : null;
    $book['file_url'] = null; // never expose direct file URL; use download endpoint
    $book['average_rating'] = (float) $book['average_rating'];
    $book['total_reviews'] = (int) $book['total_reviews'];
    $book['views'] = (int) $book['view_count'];
    $book['downloads'] = (int) $book['download_count'];
    unset($book['view_count'], $book['download_count']);

    $user = api_get_user();
    $book['is_favorited'] = false;
    if ($user) {
        $st = $pdo->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND book_id = ?');
        $st->execute([$user['id'], $id]);
        $book['is_favorited'] = (bool) $st->fetch();
    }
    api_json($book);
}

// List
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = min(50, max(1, (int) ($_GET['limit'] ?? 20)));
$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? '-created';

$where = ['1=1'];
$params = [];
if ($q !== '') {
    $where[] = '(b.title LIKE ? OR b.description LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($category !== '') {
    $where[] = 't.slug = ?';
    $params[] = $category;
}

$order = 'b.created_at DESC';
if ($sort === 'title') $order = 'b.title ASC';
elseif ($sort === '-views') $order = 'b.view_count DESC';
elseif ($sort === '-downloads') $order = 'b.download_count DESC';
elseif ($sort === '-rating') $order = 'avg_rating DESC';

$stmt = $pdo->prepare('SELECT COUNT(*) FROM books b JOIN themes t ON t.id = b.theme_id WHERE ' . implode(' AND ', $where));
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();
$offset = ($page - 1) * $limit;

$sql = 'SELECT b.id, b.title, b.description, b.cover_url, b.is_free, b.is_downloadable, b.view_in_web, b.view_in_app,
  b.view_count AS views, b.download_count AS downloads, t.name AS theme_name, u.username AS author_name,
  (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.book_id = b.id) AS avg_rating,
  (SELECT COUNT(*) FROM favorites f WHERE f.book_id = b.id) AS favorites
  FROM books b JOIN themes t ON t.id = b.theme_id JOIN users u ON u.id = b.author_id
  WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $order . ' LIMIT ' . $limit . ' OFFSET ' . $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($books as &$b) {
    $b['cover_url'] = $b['cover_url'] ? $coverBase . '/' . $b['cover_url'] : null;
    $b['views'] = (int) $b['views'];
    $b['downloads'] = (int) $b['downloads'];
    $b['favorites'] = (int) $b['favorites'];
    $b['average_rating'] = round((float) $b['avg_rating'], 1);
    unset($b['avg_rating']);
}

api_json([
    'data' => $books,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'total_pages' => (int) ceil($total / $limit),
    ],
]);
