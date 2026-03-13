<?php
/**
 * Global helper functions
 */

function e(?string $s): string {
    return $s === null ? '' : htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url, int $code = 302): void {
    header('Location: ' . $url, true, $code);
    exit;
}

function base_url(string $path = ''): string {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

function asset(string $path): string {
    return base_url('assets/' . ltrim($path, '/'));
}

function slugify(string $s): string {
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
    return strtolower(trim($s, '-'));
}

function pagination(int $total, int $page, int $perPage, string $baseUrl): array {
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $page,
        'total_pages' => $totalPages,
        'base_url'    => $baseUrl,
    ];
}

function get_pagination_link(string $baseUrl, int $page): string {
    $sep = strpos($baseUrl, '?') !== false ? '&' : '?';
    return $baseUrl . $sep . 'page=' . $page;
}

function format_date(?string $date): string {
    if (!$date) return '—';
    $t = strtotime($date);
    return $t ? date('M j, Y', $t) : '—';
}

function csrf_field(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="_csrf" value="' . e($_SESSION['_csrf']) . '">';
}

function csrf_verify(): bool {
    $token = $_POST['_csrf'] ?? '';
    return !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}
