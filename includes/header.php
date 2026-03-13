<?php
$pageTitle = $pageTitle ?? 'Library';
$currentUser = current_user();
$siteName = get_setting('site_name', 'Library');
$siteLogoUrl = site_logo_url();
$siteFaviconUrl = site_favicon_url();
$siteAppIconUrl = site_app_icon_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> – <?= e($siteName) ?></title>
    <?php if ($siteFaviconUrl): ?><link rel="icon" href="<?= e($siteFaviconUrl) ?>" type="image/x-icon"><?php endif; ?>
    <?php if ($siteAppIconUrl): ?><link rel="apple-touch-icon" href="<?= e($siteAppIconUrl) ?>"><?php endif; ?>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= base_url() ?>" class="logo">
            <?php if ($siteLogoUrl): ?>
                <img src="<?= e($siteLogoUrl) ?>" alt="<?= e($siteName) ?>" class="site-logo">
            <?php else: ?>
                <?= e($siteName) ?>
            <?php endif; ?>
        </a>
        <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-hidden="true">
        <label for="nav-toggle" class="nav-toggle-label" aria-label="Open menu"><span class="nav-toggle-icon"></span></label>
        <nav class="site-nav">
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
