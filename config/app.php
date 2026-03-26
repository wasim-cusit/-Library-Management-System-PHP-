<?php
/**
 * Application config: base URL, paths, uploads, etc.
 */
define('BASE_PATH', dirname(__DIR__));
// Auto-detect install path (works for localhost + shared hosting + subfolder deploy).
// Examples:
// - https://example.com/               => BASE_URL = ''
// - https://example.com/Bookslibrary/  => BASE_URL = '/Bookslibrary'
// - https://example.com/some/path/app/ => BASE_URL = '/some/path/app'
$__docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath((string) $_SERVER['DOCUMENT_ROOT']) : false;
$__basePath = realpath(BASE_PATH);
$__baseUrl = '';
if ($__docRoot && $__basePath) {
    $__docRootNorm = str_replace('\\', '/', rtrim($__docRoot, '\\/'));
    $__basePathNorm = str_replace('\\', '/', rtrim($__basePath, '\\/'));
    if ($__docRootNorm !== '' && str_starts_with($__basePathNorm, $__docRootNorm)) {
        $__rel = substr($__basePathNorm, strlen($__docRootNorm));
        $__rel = $__rel === false ? '' : $__rel;
        $__rel = '/' . ltrim((string) $__rel, '/');
        if ($__rel === '/') $__rel = '';
        $__baseUrl = $__rel;
    }
}
define('BASE_URL', $__baseUrl);
define('SITE_BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL);
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads');
define('UPLOAD_COVERS', UPLOAD_PATH . '/covers');
define('UPLOAD_BOOKS', UPLOAD_PATH . '/books');

define('COVER_URL', BASE_URL . '/assets/uploads/covers');
define('BOOK_URL', BASE_URL . '/assets/uploads/books');
define('UPLOAD_SITE', UPLOAD_PATH . '/site');
define('SITE_ASSETS_URL', BASE_URL . '/assets/uploads/site');

define('SESSION_NAME', 'LIBRARY_SESSION');
define('JWT_SECRET', 'change-this-secret-in-production'); // Use a long random string in production
define('JWT_EXPIRY_SECONDS', 86400 * 7); // 7 days
define('ITEMS_PER_PAGE', 12);

// Allowed book file extensions for upload (any of these can be read or downloaded)
define('BOOK_ALLOWED_EXTENSIONS', ['pdf', 'epub', 'txt', 'doc', 'docx', 'mobi', 'rtf']); // allowed for upload
define('BOOK_READ_IN_BROWSER_EXTENSIONS', ['pdf', 'epub', 'txt']); // formats we can display in-page
define('BOOK_MAX_TXT_DISPLAY_BYTES', 2 * 1024 * 1024); // 2MB max for inline TXT display

// Ensure upload dirs exist
foreach ([UPLOAD_PATH, UPLOAD_COVERS, UPLOAD_BOOKS, UPLOAD_SITE] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}
