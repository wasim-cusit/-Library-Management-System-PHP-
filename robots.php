<?php
/**
 * Dynamic robots.txt: Sitemap URL uses current host so it works on local and live.
 * Served as robots.txt via .htaccess rewrite.
 */
require_once __DIR__ . '/config/app.php';

$sitemapUrl = rtrim(SITE_BASE_URL, '/') . '/sitemap.xml';

header('Content-Type: text/plain; charset=UTF-8');
echo "User-agent: *\n";
echo "Disallow:\n\n";
echo "Sitemap: " . $sitemapUrl . "\n";
