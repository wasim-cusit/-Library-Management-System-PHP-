<?php
/**
 * Auth helpers: current user, login, logout, require auth/role
 */

function current_user(): ?array {
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) return null;
    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT id, username, email, full_name, avatar, role, bio FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect(base_url('auth/login.php'));
    }
}

function require_role(string ...$roles): void {
    require_login();
    $user = current_user();
    if (!in_array($user['role'], $roles, true)) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Access denied.';
        exit;
    }
}

function require_admin(): void {
    require_role('admin');
}

function require_author(): void {
    require_role('author', 'admin');
}

function can_edit_book(int $bookId): bool {
    $user = current_user();
    if (!$user) return false;
    if ($user['role'] === 'admin') return true;
    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT author_id FROM books WHERE id = ?');
    $stmt->execute([$bookId]);
    $row = $stmt->fetch();
    return $row && (int) $row['author_id'] === (int) $user['id'];
}
