<?php
/**
 * API v1 bootstrap: CORS, JSON, DB. No session. Auth via JWT in Authorization header.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/jwt.php';
require_once dirname(__DIR__, 2) . '/includes/settings.php';
require_once dirname(__DIR__, 2) . '/includes/access.php';

function api_json($data): void {
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
}

function api_error(string $message, int $code = 400, ?string $errorCode = null): void {
    http_response_code($code);
    api_json(array_filter(['error' => $message, 'code' => $errorCode]));
    exit;
}

function api_get_user(): ?array {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(\S+)/', $auth, $m)) return null;
    $payload = jwt_decode($m[1]);
    if (!$payload || empty($payload['sub'])) return null;
    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT id, username, email, full_name, avatar, role FROM users WHERE id = ?');
    $stmt->execute([(int) $payload['sub']]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function api_require_user(): array {
    $user = api_get_user();
    if (!$user) api_error('Unauthorized', 401, 'UNAUTHORIZED');
    return $user;
}

function api_asset_url(string $path): string {
    return rtrim(SITE_BASE_URL, '/') . '/' . ltrim($path, '/');
}
