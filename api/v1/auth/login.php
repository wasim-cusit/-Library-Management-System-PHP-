<?php
/**
 * POST /api/v1/auth/login.php
 * Body: {"email":"...","password":"..."}
 * Returns: {"token":"jwt","user":{...}}
 */
require_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Method not allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$password) {
    api_error('Email and password required', 400);
}

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, username, email, password_hash, full_name, avatar, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    api_error('Invalid email or password', 401);
}

unset($user['password_hash']);
$user['avatar'] = $user['avatar'] ? (rtrim(SITE_BASE_URL, '/') . '/assets/uploads/' . ltrim($user['avatar'], '/')) : null;

$token = jwt_encode(['sub' => (int) $user['id']]);

api_json([
    'token' => $token,
    'user' => $user,
]);
