<?php
/**
 * GET /api/v1/user/downloads.php - download history (Bearer required)
 */
require_once dirname(__DIR__) . '/config.php';

$user = api_require_user();
$pdo = getDb();
$baseUrl = rtrim(SITE_BASE_URL, '/');
$coverBase = $baseUrl . '/assets/uploads/covers';

$stmt = $pdo->prepare('
  SELECT b.id, b.title, b.cover_url, t.name AS theme_name, u.username AS author_name, dh.downloaded_at
  FROM download_history dh
  JOIN books b ON b.id = dh.book_id
  JOIN themes t ON t.id = b.theme_id
  JOIN users u ON u.id = b.author_id
  WHERE dh.user_id = ?
  ORDER BY dh.downloaded_at DESC
  LIMIT 100
');
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($items as &$i) {
    $i['cover_url'] = $i['cover_url'] ? $coverBase . '/' . $i['cover_url'] : null;
}
api_json(['data' => $items]);
