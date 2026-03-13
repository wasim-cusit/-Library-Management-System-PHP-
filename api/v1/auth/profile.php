<?php
/**
 * GET /api/v1/auth/profile.php - get current user (Bearer required)
 * PUT /api/v1/auth/profile.php - update profile (Bearer required)
 */
require_once dirname(__DIR__) . '/config.php';

$user = api_require_user();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user['avatar'] = $user['avatar'] ? (rtrim(SITE_BASE_URL, '/') . '/assets/uploads/covers/' . $user['avatar']) : null;
    api_json($user);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $full_name = trim($input['full_name'] ?? '');
    $pdo = getDb();
    $stmt = $pdo->prepare('UPDATE users SET full_name = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$full_name ?: null, $user['id']]);
    $stmt = $pdo->prepare('SELECT id, username, email, full_name, avatar, role FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $u = $stmt->fetch();
    $u['avatar'] = $u['avatar'] ? (rtrim(SITE_BASE_URL, '/') . '/assets/uploads/covers/' . $u['avatar']) : null;
    api_json($u);
}

api_error('Method not allowed', 405);
