<?php
/**
 * GET /api/v1/categories.php - list themes/categories
 */
require_once __DIR__ . '/config.php';

$pdo = getDb();
$stmt = $pdo->query('SELECT t.id, t.name, t.slug, t.description, COUNT(b.id) AS book_count FROM themes t LEFT JOIN books b ON b.theme_id = t.id GROUP BY t.id ORDER BY t.sort_order, t.name');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($categories as &$c) {
    $c['book_count'] = (int) $c['book_count'];
}
api_json(['data' => $categories]);
