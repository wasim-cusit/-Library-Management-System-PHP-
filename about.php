<?php
require_once __DIR__ . '/includes/bootstrap.php';

$siteName = get_setting('site_name', 'Library');
$themes = getDb()->query('SELECT id, name, slug, description FROM themes ORDER BY sort_order, name')->fetchAll();

$pageTitle = 'About ' . $siteName . ' – Digital library, themes & mobile app';
$pageDescription = 'About ' . $siteName . ': browse books by theme, read online, download when allowed. Web and mobile friendly.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-content page-about">
  <h1>About <?= e($siteName) ?></h1>

  <section class="about-block">
    <h2>What is this website?</h2>
    <p><?= e($siteName) ?> is a <strong>digital library</strong> where you can browse books by theme (category), read them online in your browser or in the mobile app, and download them when the book allows it. Books can be free or paid, and each book can be set as downloadable or view-only, and available on the web and/or in the app.</p>
  </section>

  <section class="about-block">
    <h2>For readers</h2>
    <ul>
      <li>Search and filter books by title, theme, and author.</li>
      <li>Read books online (web) when the book supports &quot;View on Web&quot;.</li>
      <li>Download books (when marked as downloadable) for offline reading.</li>
      <li>Add books to your <strong>favorites</strong> and see your reading history.</li>
      <li>Rate and review books with stars and comments.</li>
      <li>Browse by <strong>themes and courses</strong> to find content by category.</li>
    </ul>
  </section>

  <section class="about-block">
    <h2>For authors</h2>
    <p>If you register as an <strong>author</strong> (or an admin sets your role to author), you can:</p>
    <ul>
      <li>Add your own books with <strong>title</strong>, <strong>description</strong>, <strong>theme</strong>, <strong>publisher</strong>, and <strong>publish date</strong>.</li>
      <li>Upload a cover image and the book file (PDF/EPUB).</li>
      <li>Set each book as <strong>free or paid</strong>, <strong>downloadable or not</strong>, and whether it can be viewed on the <strong>web</strong> and/or in the <strong>app</strong>.</li>
      <li>Edit and manage your books from the &quot;My Books&quot; dashboard.</li>
    </ul>
  </section>

  <section class="about-block">
    <h2>Themes & courses</h2>
    <p>Books are organized by <strong>themes</strong> (categories). You can browse all themes from the <a href="<?= base_url('themes/') ?>">Themes</a> page. Current themes on the platform:</p>
    <?php if (empty($themes)): ?>
      <p>No themes have been added yet.</p>
    <?php else: ?>
      <ul class="about-themes-list">
        <?php foreach ($themes as $t): ?>
          <li><a href="<?= base_url('themes/view.php?slug=' . urlencode($t['slug'])) ?>"><?= e($t['name']) ?></a><?php if (!empty($t['description'])): ?> — <?= e($t['description']) ?><?php endif; ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <section class="about-block">
    <h2>Web & mobile</h2>
    <p>This website works on desktop and mobile browsers. A mobile app can connect to the same library; API documentation for developers is available in the <strong>Admin</strong> panel.</p>
  </section>

  <p><a href="<?= base_url() ?>" class="btn">Back to home</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
