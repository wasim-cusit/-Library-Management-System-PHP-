<?php
/**
 * GET /api/v1/auth/profile.php - get current user (Bearer required)
 * PUT /api/v1/auth/profile.php - update profile (Bearer required)
 */
require_once dirname(__DIR__) . '/config.php';

$user = api_require_user();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user['avatar'] = $user['avatar'] ? (rtrim(SITE_BASE_URL, '/') . '/assets/uploads/' . ltrim($user['avatar'], '/')) : null;
    api_json($user);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $full_name = trim($input['full_name'] ?? '');
    $oldPassword = (string) ($input['old_password'] ?? '');
    $newPassword = (string) ($input['new_password'] ?? '');
    $confirmPassword = (string) ($input['confirm_password'] ?? '');
    $pdo = getDb();

    // Optional password change in same endpoint.
    if ($oldPassword !== '' || $newPassword !== '' || $confirmPassword !== '') {
        if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
            api_error('old_password, new_password and confirm_password are required to change password', 400, 'PASSWORD_FIELDS_REQUIRED');
        }
        if (strlen($newPassword) < 6) {
            api_error('New password must be at least 6 characters', 400, 'PASSWORD_TOO_SHORT');
        }
        if ($newPassword !== $confirmPassword) {
            api_error('New password and confirm password do not match', 400, 'PASSWORD_MISMATCH');
        }
        if ($oldPassword === $newPassword) {
            api_error('New password must be different from old password', 400, 'PASSWORD_SAME_AS_OLD');
        }

        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $currentHash = (string) $stmt->fetchColumn();
        if ($currentHash === '' || !password_verify($oldPassword, $currentHash)) {
            api_error('Old password is incorrect', 400, 'OLD_PASSWORD_INVALID');
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$newHash, $user['id']]);
    }

    $stmt = $pdo->prepare('UPDATE users SET full_name = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$full_name ?: null, $user['id']]);
    $stmt = $pdo->prepare('SELECT id, username, email, full_name, avatar, role FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $u = $stmt->fetch();
    $u['avatar'] = $u['avatar'] ? (rtrim(SITE_BASE_URL, '/') . '/assets/uploads/' . ltrim($u['avatar'], '/')) : null;
    api_json($u);
}

api_error('Method not allowed', 405);
