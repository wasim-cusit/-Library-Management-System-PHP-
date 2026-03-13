<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pdo = getDb();
$stmt = $pdo->query('
  SELECT b.id, b.title, b.cover_url, b.view_count, b.download_count, b.is_free, b.is_downloadable, b.view_in_web, b.view_in_app,
         t.name AS theme_name, u.username AS author_name
  FROM books b
  JOIN themes t ON t.id = b.theme_id
  JOIN users u ON u.id = b.author_id
  ORDER BY b.created_at DESC
  LIMIT 12
');
$books = $stmt->fetchAll();

$themes = $pdo->query('SELECT t.id, t.name, t.slug, t.description, COUNT(b.id) AS book_count FROM themes t LEFT JOIN books b ON b.theme_id = t.id GROUP BY t.id ORDER BY t.sort_order, t.name')->fetchAll();

$siteName = get_setting('site_name', 'Library');
$siteTagline = get_setting('site_tagline', 'Read, download & discover books');

$pageTitle = $siteTagline ?: 'Online library';
$pageDescription = $siteTagline
  ? ($siteTagline . ' Browse by theme, read online on web or app, and download books when allowed.')
  : ('Browse by theme, read online on web or app, and download books when allowed.');
require_once __DIR__ . '/includes/header.php';
?>

<!-- Landing: Hero -->
<section class="landing-hero">
  <div class="landing-hero-inner">
    <h1 class="landing-hero-title"><?= e($siteName) ?></h1>
    <p class="landing-hero-tagline"><?= e($siteTagline) ?></p>
    <p class="landing-hero-desc">Browse by theme, read online on the web or in the app, download when allowed, and build your reading list with favorites and reviews.</p>
    <div class="landing-hero-actions">
      <a href="<?= base_url('books/') ?>" class="btn btn-hero">Browse books</a>
      <a href="<?= base_url('auth/register.php') ?>" class="btn btn-secondary">Register</a>
      <a href="<?= base_url('themes/') ?>" class="btn btn-outline">All themes & courses</a>
    </div>
  </div>
</section>

<!-- About the website -->
<section class="landing-section" id="about">
  <div class="landing-section-inner">
    <h2 class="landing-section-title">About this website</h2>
    <p class="landing-lead"><?= e($siteName) ?> is a digital library where you can discover books by theme, read them online, and download them when permitted. Whether you're a reader or an author, the platform is built for easy browsing and learning.</p>
    <ul class="landing-about-list">
      <li><strong>Readers</strong> — Search and filter by theme, read online (web or app), download books, add favorites, and leave reviews.</li>
      <li><strong>Authors</strong> — Register as an author to publish your own books with title, description, theme, publisher, and publish date. Mark books as free or paid, downloadable or view-only, and available on web and/or app.</li>
      <li><strong>Admins</strong> — Manage themes, users, and site settings (including the website logo and app icon).</li>
    </ul>
    <p><a href="<?= base_url('about.php') ?>" class="btn btn-secondary">Read full about page</a></p>
  </div>
</section>

<!-- Features -->
<section class="landing-section landing-section-alt">
  <div class="landing-section-inner">
    <h2 class="landing-section-title">What you can do</h2>
    <div class="landing-features">
      <div class="landing-feature">
        <span class="landing-feature-icon">📚</span>
        <h3>Browse by theme</h3>
        <p>Explore books by categories and themes (Fiction, Science, History, and more).</p>
      </div>
      <div class="landing-feature">
        <span class="landing-feature-icon">🖥️</span>
        <h3>Read on web</h3>
        <p>Read books directly in your browser. Available when the book allows &quot;View on Web&quot;.</p>
      </div>
      <div class="landing-feature">
        <span class="landing-feature-icon">📱</span>
        <h3>Use the app</h3>
        <p>Use our mobile app and view books that support &quot;View in App&quot;.</p>
      </div>
      <div class="landing-feature">
        <span class="landing-feature-icon">⬇️</span>
        <h3>Download</h3>
        <p>Download books when they are marked as downloadable (free or paid).</p>
      </div>
      <div class="landing-feature">
        <span class="landing-feature-icon">❤️</span>
        <h3>Favorites & reviews</h3>
        <p>Save favorites and rate books with stars and comments.</p>
      </div>
    </div>
  </div>
</section>

<!-- Themes / Courses -->
<section class="landing-section" id="themes">
  <div class="landing-section-inner">
    <h2 class="landing-section-title">Themes & courses</h2>
    <p class="landing-lead">All categories available on the platform. Click a theme to see its books.</p>
    <?php if (empty($themes)): ?>
      <p>No themes yet. Check back later.</p>
    <?php else: ?>
      <div class="landing-themes">
        <?php foreach ($themes as $t): ?>
          <a href="<?= base_url('themes/view.php?slug=' . urlencode($t['slug'])) ?>" class="landing-theme-card">
            <h3><?= e($t['name']) ?></h3>
            <?php if (!empty($t['description'])): ?><p><?= e(mb_substr($t['description'], 0, 120)) ?><?= mb_strlen($t['description']) > 120 ? '…' : '' ?></p><?php endif; ?>
            <span class="landing-theme-count"><?= (int) $t['book_count'] ?> book<?= (int)$t['book_count'] !== 1 ? 's' : '' ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <p><a href="<?= base_url('themes/') ?>" class="btn">View all themes</a></p>
    <?php endif; ?>
  </div>
</section>

<!-- Recent books -->
<section class="landing-section landing-section-alt">
  <div class="landing-section-inner">
    <h2 class="landing-section-title">Recent books</h2>
    <?php if (empty($books)): ?>
      <p>No books yet. <a href="<?= base_url('auth/register.php') ?>">Register as an author</a> to add books.</p>
    <?php else: ?>
      <div class="books-grid">
        <?php foreach ($books as $b): ?>
          <article class="book-card">
            <a href="<?= base_url('books/detail.php?id=' . $b['id']) ?>">
              <?php if (!empty($b['cover_url'])): ?><img class="book-cover" src="<?= e(COVER_URL . '/' . $b['cover_url']) ?>" alt="<?= e($b['title']) ?> cover"><?php else: ?><div class="book-cover placeholder" aria-hidden="true"></div><?php endif; ?>
              <div class="info">
                <h3 class="title"><?= e($b['title']) ?></h3>
                <p class="meta"><?= e($b['author_name']) ?> · <?= e($b['theme_name']) ?></p>
                <div class="book-badges">
                  <span class="badge <?= $b['is_free'] ? 'free' : 'paid' ?>"><?= $b['is_free'] ? 'Free' : 'Paid' ?></span>
                  <?php if ($b['is_downloadable']): ?><span class="badge downloadable">Download</span><?php endif; ?>
                  <?php if ($b['view_in_web']): ?><span class="badge web">Web</span><?php endif; ?>
                  <?php if ($b['view_in_app']): ?><span class="badge app">App</span><?php endif; ?>
                </div>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
      <p><a href="<?= base_url('books/') ?>" class="btn">View all books</a></p>
    <?php endif; ?>
  </div>
</section>

<!-- CTA -->
<section class="landing-cta">
  <div class="landing-cta-inner">
    <h2>Get started</h2>
    <p>Create a free account to read online, download, and save favorites.</p>
    <a href="<?= base_url('auth/register.php') ?>" class="btn btn-hero">Register now</a>
    <a href="<?= base_url('books/') ?>" class="btn btn-outline-light">Browse books</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
