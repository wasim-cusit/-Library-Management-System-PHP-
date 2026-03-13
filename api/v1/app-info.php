<?php
/**
 * GET /api/v1/app-info.php
 * Returns site name, logo URL, app icon URL for mobile app (splash, icon, etc.)
 */
require_once __DIR__ . '/config.php';

$siteName = get_setting('site_name', 'Library');
$siteTagline = get_setting('site_tagline', '');
$logoFile = get_setting('logo_file');
$appIconFile = get_setting('app_icon_file');
$faviconFile = get_setting('favicon_file');

$base = rtrim(SITE_BASE_URL, '/') . '/assets/uploads/site';
api_json([
    'site_name' => $siteName,
    'site_tagline' => $siteTagline,
    'logo_url' => $logoFile ? $base . '/' . $logoFile : null,
    'app_icon_url' => $appIconFile ? $base . '/' . $appIconFile : null,
    'favicon_url' => $faviconFile ? $base . '/' . $faviconFile : null,
]);
