<?php
$pageTitle = $pageTitle ?? 'Library';
$pageDescription = $pageDescription ?? null;
$pageRobots = $pageRobots ?? null; // e.g. "noindex, nofollow" for private pages
$pageCanonical = $pageCanonical ?? null;
$currentUser = current_user();
$siteName = get_setting('site_name', 'Library');
$siteLogoUrl = site_logo_url();
$siteFaviconUrl = site_favicon_url();
$siteAppIconUrl = site_app_icon_url();
$siteTagline = get_setting('site_tagline', 'Read, download & discover books');
$siteDescription = get_setting('site_description', '') ?: ($siteTagline ? ($siteTagline . ' Browse by theme, read online, and download books.') : '');
$siteKeywords = get_setting('site_keywords', 'library, books, reading, download, themes, courses');
$sitePublisher = get_setting('site_publisher', $siteName);

$seoTitle = trim((string) $pageTitle);
if ($seoTitle === '') {
    $seoTitle = $siteName;
} elseif (stripos($seoTitle, $siteName) === false) {
    $seoTitle .= ' – ' . $siteName;
}

$seoDescription = trim((string) ($pageDescription ?: $siteDescription));
$seoRobots = trim((string) ($pageRobots ?: 'index, follow'));
$seoCanonical = $pageCanonical ?: canonical_url();

if (!headers_sent()) {
    header('X-Robots-Tag: ' . $seoRobots);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($seoTitle) ?></title>
    <?php if ($seoDescription): ?><meta name="description" content="<?= e($seoDescription) ?>"><?php endif; ?>
    <meta name="robots" content="<?= e($seoRobots) ?>">
    <link rel="canonical" href="<?= e($seoCanonical) ?>">
    <?php if ($siteKeywords): ?><meta name="keywords" content="<?= e($siteKeywords) ?>"><?php endif; ?>
    <?php if ($sitePublisher): ?><meta name="publisher" content="<?= e($sitePublisher) ?>"><?php endif; ?>
    <?php if ($siteFaviconUrl): ?><link rel="icon" href="<?= e($siteFaviconUrl) ?>" type="image/x-icon"><?php endif; ?>
    <?php if ($siteAppIconUrl): ?><link rel="apple-touch-icon" href="<?= e($siteAppIconUrl) ?>"><?php endif; ?>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<?php if (empty($authLayout)): ?>
<header class="site-header" id="site-header">
    <div class="header-inner">
        <a href="<?= base_url() ?>" class="logo">
            <?php if ($siteLogoUrl): ?>
                <img src="<?= e($siteLogoUrl) ?>" alt="<?= e($siteName) ?>" class="site-logo">
            <?php else: ?>
                <?= e($siteName) ?>
            <?php endif; ?>
        </a>
        <button type="button" class="nav-toggle-label" id="nav-toggle-btn" aria-label="Toggle menu" aria-expanded="false">
            <span class="nav-toggle-icon"></span>
        </button>
        <nav class="site-nav" id="site-nav">
            <a href="<?= base_url() ?>">Home</a>
            <a href="<?= base_url('about.php') ?>">About</a>
            <a href="<?= base_url('books/') ?>">Books</a>
            <a href="<?= base_url('themes/') ?>">Themes</a>
            <?php if ($currentUser): ?>
                <a href="<?= base_url('user/favorites.php') ?>">Favorites</a>
                <?php if (in_array($currentUser['role'], ['author', 'admin'], true)): ?>
                    <a href="<?= base_url('author/') ?>">My Books</a>
                <?php endif; ?>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="<?= base_url('admin/') ?>">Admin</a>
                <?php endif; ?>
                <a href="<?= base_url('auth/profile.php') ?>">Profile</a>
                <a href="<?= base_url('auth/logout.php') ?>">Logout</a>
            <?php else: ?>
                <a href="<?= base_url('auth/login.php') ?>">Login</a>
                <a href="<?= base_url('auth/register.php') ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="main-content<?= !empty($isReaderPage) ? ' reader-main' : '' ?>">
<?php else: ?>
<div class="auth-page-wrap">
<?php endif; ?>
