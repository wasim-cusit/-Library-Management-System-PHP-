<?php
/**
 * Paid book access: check if user can access a paid book (has valid or lifetime access).
 */

function can_user_access_book(?int $userId, array $book): bool {
    if ((int) $book['is_free'] === 1) {
        return true;
    }
    if (!$userId) {
        return false;
    }
    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT expires_at FROM user_book_access WHERE user_id = ? AND book_id = ?');
    $stmt->execute([$userId, $book['id']]);
    $row = $stmt->fetch();
    if (!$row) {
        return false;
    }
    if ($row['expires_at'] === null) {
        return true; // lifetime
    }
    return strtotime($row['expires_at']) > time();
}

function get_user_access_row(?int $userId, int $bookId): ?array {
    if (!$userId) return null;
    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT accessed_at, expires_at FROM user_book_access WHERE user_id = ? AND book_id = ?');
    $stmt->execute([$userId, $bookId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function access_duration_label(?int $days): string {
    if ($days === null || $days <= 0) return 'Lifetime access';
    if ($days === 30) return '30 days';
    if ($days === 90) return '90 days';
    if ($days === 365) return '1 year';
    return (string) $days . ' days';
}
