<?php
/**
 * Site settings (admin-editable). Keys: site_name, site_tagline, logo_file, app_icon_file, favicon_file
 */
function _settings_cache(bool $clear = false): ?array {
    static $cache = null;
    if ($clear) {
        $cache = null;
        return null;
    }
    if ($cache === null) {
        try {
            $pdo = getDb();
            $stmt = $pdo->query('SELECT `key`, `value` FROM settings');
            $cache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return $cache;
}

function clear_settings_cache(): void {
    _settings_cache(true);
}

function get_setting(string $key, ?string $default = null): ?string {
    $cache = _settings_cache();
    return $cache[$key] ?? $default;
}

function get_all_settings(): array {
    $pdo = getDb();
    $stmt = $pdo->query('SELECT `key`, `value` FROM settings');
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function update_setting(string $key, ?string $value): void {
    $pdo = getDb();
    $stmt = $pdo->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = NOW()');
    $stmt->execute([$key, $value]);
}

function site_logo_url(): ?string {
    $file = get_setting('logo_file');
    return $file ? (SITE_ASSETS_URL . '/' . $file) : null;
}

function site_app_icon_url(): ?string {
    $file = get_setting('app_icon_file');
    return $file ? (SITE_ASSETS_URL . '/' . $file) : null;
}

function site_favicon_url(): ?string {
    $file = get_setting('favicon_file');
    return $file ? (SITE_ASSETS_URL . '/' . $file) : null;
}
