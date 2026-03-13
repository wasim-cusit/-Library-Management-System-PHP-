<?php
/**
 * POST /api/v1/auth/register.php
 * Body: {"username":"...","email":"...","password":"...","full_name":"...","register_as_author":false}
 * Returns: {"message":"...","user_id":123}
 */
require_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Method not allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$full_name = trim($input['full_name'] ?? '');
$register_as_author = !empty($input['register_as_author']);

if (strlen($username) < 2) api_error('Username must be at least 2 characters', 400);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) api_error('Invalid email', 400);
if (strlen($password) < 6) api_error('Password must be at least 6 characters', 400);

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
$stmt->execute([$email, $username]);
if ($stmt->fetch()) api_error('Email or username already registered', 400);

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = $register_as_author ? 'author' : 'user';
$stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$username, $email, $hash, $full_name ?: null, $role]);
$userId = (int) $pdo->lastInsertId();

http_response_code(201);
api_json(['message' => 'User registered successfully', 'user_id' => $userId]);
