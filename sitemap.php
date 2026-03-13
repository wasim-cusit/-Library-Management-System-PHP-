<?php
/**
 * Dynamic sitemap: uses SITE_BASE_URL so it works on local and live.
 * Served as sitemap.xml via .htaccess rewrite.
 */
require_once __DIR__ . '/config/app.php';

$base = rtrim(SITE_BASE_URL, '/');

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$urls = [
    ['loc' => $base . '/', 'changefreq' => 'daily', 'priority' => '1.0'],
    ['loc' => $base . '/about.php', 'changefreq' => 'monthly', 'priority' => '0.6'],
    ['loc' => $base . '/books/', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['loc' => $base . '/themes/', 'changefreq' => 'weekly', 'priority' => '0.8'],
    ['loc' => $base . '/auth/login.php', 'changefreq' => 'yearly', 'priority' => '0.2'],
    ['loc' => $base . '/auth/register.php', 'changefreq' => 'yearly', 'priority' => '0.2'],
];

try {
    require_once __DIR__ . '/config/database.php';
    $pdo = getDb();
    $themes = $pdo->query('SELECT slug, created_at FROM themes ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($themes as $t) {
        $urls[] = [
            'loc' => $base . '/themes/view.php?slug=' . urlencode($t['slug']),
            'changefreq' => 'weekly',
            'priority' => '0.7',
            'lastmod' => !empty($t['created_at']) ? date('c', strtotime($t['created_at'])) : null,
        ];
    }
    $books = $pdo->query('SELECT id, updated_at FROM books ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($books as $b) {
        $urls[] = [
            'loc' => $base . '/books/detail.php?id=' . (int) $b['id'],
            'changefreq' => 'monthly',
            'priority' => '0.6',
            'lastmod' => !empty($b['updated_at']) ? date('c', strtotime($b['updated_at'])) : null,
        ];
    }
} catch (Throwable $e) {
    // no DB: only static URLs
}

foreach ($urls as $u) {
    echo '  <url>' . "\n";
    echo '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";
    if (!empty($u['lastmod'])) {
        echo '    <lastmod>' . htmlspecialchars($u['lastmod'], ENT_XML1, 'UTF-8') . '</lastmod>' . "\n";
    }
    echo '    <changefreq>' . htmlspecialchars($u['changefreq'] ?? 'weekly', ENT_XML1, 'UTF-8') . '</changefreq>' . "\n";
    echo '    <priority>' . htmlspecialchars($u['priority'] ?? '0.5', ENT_XML1, 'UTF-8') . '</priority>' . "\n";
    echo '  </url>' . "\n";
}

echo '</urlset>';
