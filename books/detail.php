<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    redirect(base_url('books/'));
}

$pdo = getDb();
$stmt = $pdo->prepare('
  SELECT b.*, t.name AS theme_name, t.slug AS theme_slug,
         u.username AS author_username, u.full_name AS author_name,
         p.name AS publisher_name
  FROM books b
  JOIN themes t ON t.id = b.theme_id
  JOIN users u ON u.id = b.author_id
  LEFT JOIN publishers p ON p.id = b.publisher_id
  WHERE b.id = ?
');
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) {
    header('HTTP/1.1 404 Not Found');
    echo 'Book not found.';
    exit;
}

$currentUser = current_user();
$isFavorited = false;
$hasAccess = (int) $book['is_free'] === 1;
$accessRow = null;
if ($currentUser) {
    $stmt = $pdo->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND book_id = ?');
    $stmt->execute([$currentUser['id'], $id]);
    $isFavorited = (bool) $stmt->fetch();
    $hasAccess = can_user_access_book($currentUser['id'], $book);
    $accessRow = get_user_access_row($currentUser['id'], $id);
}
$accessExpired = $currentUser && !$book['is_free'] && $accessRow && $accessRow['expires_at'] !== null && strtotime($accessRow['expires_at']) <= time();
$book['access_duration_days'] = isset($book['access_duration_days']) ? $book['access_duration_days'] : null;

// Average rating and review count
$stmt = $pdo->prepare('SELECT COALESCE(AVG(rating), 0) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE book_id = ?');
$stmt->execute([$id]);
$ratingRow = $stmt->fetch();
$avgRating = (float) $ratingRow['avg_rating'];
$totalReviews = (int) $ratingRow['total_reviews'];

// Recent reviews
$stmt = $pdo->prepare('SELECT r.id, r.rating, r.comment, r.created_at, u.username FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.book_id = ? ORDER BY r.created_at DESC LIMIT 10');
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$pageTitle = $book['title'];
$pageDescription = mb_substr(strip_tags($book['description'] ?? ''), 0, 160) ?: ($book['title'] . ' – ' . ($book['author_name'] ?: $book['author_username']) . '. ' . ($book['theme_name'] ?? ''));
$currentNav = 'books';
require_once dirname(__DIR__) . '/includes/header.php';
$flashError = $_SESSION['error'] ?? null;
if (isset($_SESSION['error'])) unset($_SESSION['error']);
?>
<?php if ($flashError): ?><p class="error"><?= e($flashError) ?></p><?php endif; ?>
<?php if (!empty($_SESSION['success'])): ?><p class="success"><?= e($_SESSION['success']) ?></p><?php unset($_SESSION['success']); endif; ?>
<div class="book-detail">
  <div class="cover-col">
    <?php if (!empty($book['cover_url'])): ?>
      <img class="cover" src="<?= e(COVER_URL . '/' . $book['cover_url']) ?>" alt="<?= e($book['title']) ?> cover">
    <?php else: ?>
      <div class="book-cover placeholder" aria-hidden="true"></div>
    <?php endif; ?>
  </div>
  <div class="info-col">
    <h1><?= e($book['title']) ?></h1>
    <ul class="meta-list">
      <li><strong>Author</strong> <a href="<?= base_url('books/?author=' . urlencode($book['author_username'])) ?>"><?= e($book['author_name'] ?: $book['author_username']) ?></a></li>
      <li><strong>Theme</strong> <a href="<?= base_url('themes/view.php?slug=' . urlencode($book['theme_slug'])) ?>"><?= e($book['theme_name']) ?></a></li>
      <?php if (!empty($book['publisher_name'])): ?>
        <li><strong>Publisher</strong> <?= e($book['publisher_name']) ?></li>
      <?php endif; ?>
      <?php if (!empty($book['published_date'])): ?>
        <li><strong>Published</strong> <?= format_date($book['published_date']) ?></li>
      <?php endif; ?>
      <?php if (!empty($book['isbn'])): ?>
        <li><strong>ISBN</strong> <?= e($book['isbn']) ?></li>
      <?php endif; ?>
      <li><strong>Views</strong> <?= (int) $book['view_count'] ?> · <strong>Downloads</strong> <?= (int) $book['download_count'] ?></li>
      <li><strong>Rating</strong> ★ <?= number_format($avgRating, 1) ?> (<?= $totalReviews ?> reviews)</li>
    </ul>
    <div class="book-badges" style="margin:0.5rem 0;">
      <span class="badge <?= $book['is_free'] ? 'free' : 'paid' ?>"><?= $book['is_free'] ? 'Free' : 'Paid' ?></span>
      <?php if (!$book['is_free']): ?>
        <span class="badge badge-access"><?= e(access_duration_label($book['access_duration_days'] ?? null)) ?></span>
      <?php endif; ?>
      <?php if ($book['is_downloadable']): ?><span class="badge downloadable">Downloadable</span><?php endif; ?>
      <?php if ($book['view_in_web']): ?><span class="badge web">View on Web</span><?php endif; ?>
      <?php if ($book['view_in_app']): ?><span class="badge app">View in App</span><?php endif; ?>
    </div>
    <?php if ($book['description']): ?>
      <div class="description"><h3>Description</h3><p><?= nl2br(e($book['description'])) ?></p></div>
    <?php endif; ?>
    <div class="book-actions">
      <?php if ($currentUser): ?>
        <?php if ($hasAccess): ?>
          <?php if ($book['view_in_web'] && !empty($book['file_url'])): ?>
            <a href="<?= base_url('books/read.php?id=' . $book['id']) ?>" class="btn">Read online (Web)</a>
          <?php endif; ?>
          <?php if ($book['is_downloadable'] && !empty($book['file_url'])): ?>
            <a href="<?= base_url('books/download.php?id=' . $book['id']) ?>" class="btn btn-secondary">Download</a>
          <?php endif; ?>
          <?php if ($accessRow && $accessRow['expires_at'] === null): ?>
            <span class="access-note">✓ Lifetime access</span>
          <?php elseif ($accessRow && $accessRow['expires_at']): ?>
            <?php $exp = strtotime($accessRow['expires_at']); ?>
            <span class="access-note">✓ Access until <?= date('M j, Y', $exp) ?></span>
          <?php endif; ?>
        <?php elseif (!$book['is_free']): ?>
          <div class="paid-access-box">
            <p class="paid-access-msg"><?= $accessExpired ? 'Your access has expired.' : 'This is a paid book.' ?> Get access to read online and download.</p>
            <p class="paid-access-duration"><strong><?= e(access_duration_label($book['access_duration_days'])) ?></strong></p>
            <form method="post" action="<?= base_url('books/get-access.php') ?>" style="margin-top:0.75rem;">
              <?= csrf_field() ?>
              <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
              <button type="submit" class="btn"><?= $accessExpired ? 'Renew access' : 'Get access' ?></button>
            </form>
          </div>
        <?php endif; ?>
        <?php if ($book['is_free'] || $hasAccess): ?>
          <?php if ($isFavorited): ?>
            <a href="<?= base_url('user/favorite-remove.php?book_id=' . $book['id']) ?>" class="btn btn-secondary">Remove from favorites</a>
          <?php else: ?>
            <a href="<?= base_url('user/favorite-add.php?book_id=' . $book['id']) ?>" class="btn btn-secondary">Add to favorites</a>
          <?php endif; ?>
        <?php endif; ?>
      <?php else: ?>
        <p><a href="<?= base_url('auth/login.php') ?>">Login</a> to read online, download, or add to favorites.</p>
      <?php endif; ?>
    </div>
    <?php if ($currentUser): ?>
      <section class="reviews-section">
        <h3>Reviews</h3>
        <?php
        $stmt = $pdo->prepare('SELECT id FROM reviews WHERE book_id = ? AND user_id = ?');
        $stmt->execute([$id, $currentUser['id']]);
        $myReview = $stmt->fetch();
        if (!$myReview && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
          <form method="post" action="<?= base_url('books/review-add.php') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
            <label>Your rating <select name="rating" required>
              <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?= $i ?>"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option><?php endfor; ?>
            </select></label>
            <label>Comment <textarea name="comment" rows="3"></textarea></label>
            <button type="submit" class="btn">Submit review</button>
          </form>
        <?php endif; ?>
        <?php foreach ($reviews as $r): ?>
          <div class="review-item">
            <strong><?= e($r['username']) ?></strong> ★ <?= $r['rating'] ?> · <?= format_date($r['created_at']) ?>
            <p><?= nl2br(e($r['comment'])) ?></p>
          </div>
        <?php endforeach; ?>
        <?php if (empty($reviews)): ?><p>No reviews yet.</p><?php endif; ?>
      </section>
    <?php endif; ?>
  </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
