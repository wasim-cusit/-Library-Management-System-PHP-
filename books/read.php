<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    redirect(base_url('books/'));
}

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, title, file_url, view_in_web, is_free FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book || !$book['view_in_web'] || empty($book['file_url'])) {
    header('HTTP/1.1 404 Not Found');
    echo 'Book not found or not available for web reading.';
    exit;
}
if (!can_user_access_book(current_user()['id'], $book)) {
    $_SESSION['error'] = 'You do not have access to this paid book. Get access from the book page.';
    redirect(base_url('books/detail.php?id=' . $id));
}

$user = current_user();
$stmt = $pdo->prepare('INSERT INTO reading_history (user_id, book_id) VALUES (?, ?)');
$stmt->execute([$user['id'], $id]);
$stmt = $pdo->prepare('UPDATE books SET view_count = view_count + 1 WHERE id = ?');
$stmt->execute([$id]);

$fileUrl = BOOK_URL . '/' . $book['file_url'];
$fullBookUrl = rtrim(SITE_BASE_URL, '/') . '/assets/uploads/books/' . $book['file_url'];
$ext = strtolower(pathinfo($book['file_url'], PATHINFO_EXTENSION));
$filePath = UPLOAD_BOOKS . '/' . $book['file_url'];

// For TXT: read content safely (same directory, no path traversal, size limit)
$txtContent = null;
if ($ext === 'txt' && is_file($filePath)) {
    $realPath = realpath($filePath);
    $realBase = realpath(UPLOAD_BOOKS);
    if ($realPath && $realBase && strpos($realPath, $realBase) === 0) {
        $size = filesize($realPath);
        $maxBytes = defined('BOOK_MAX_TXT_DISPLAY_BYTES') ? BOOK_MAX_TXT_DISPLAY_BYTES : 2 * 1024 * 1024;
        if ($size <= $maxBytes) {
            $raw = file_get_contents($realPath);
            if ($raw !== false) {
                $txtContent = mb_convert_encoding($raw, 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252');
                if ($txtContent === false) $txtContent = $raw;
            }
        }
    }
}

$pageTitle = 'Reading: ' . $book['title'];
$isReaderPage = true;

require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="reader-page" id="reader-page">
  <div class="reader-toolbar">
    <div class="reader-toolbar-left">
      <a href="<?= base_url('books/detail.php?id=' . $id) ?>" class="reader-back" title="Back to book">← Back</a>
      <span class="reader-title" title="<?= e($book['title']) ?>"><?= e($book['title']) ?></span>
      <span class="reader-format-badge"><?= e(book_format_label($ext)) ?></span>
    </div>
    <div class="reader-toolbar-right">
      <?php if (in_array($ext, ['pdf', 'epub', 'txt'], true)): ?>
        <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener" class="reader-btn" title="Open in new tab">↗ New tab</a>
      <?php endif; ?>
      <button type="button" class="reader-btn reader-btn-fullscreen" id="reader-fullscreen-btn" title="Fullscreen reading" aria-label="Toggle fullscreen">⛶ Fullscreen</button>
    </div>
  </div>

  <div class="reader-frame-wrap">
    <div class="reader-frame">
      <?php if ($ext === 'pdf'): ?>
        <iframe id="reader-iframe" src="<?= e($fileUrl) ?>#toolbar=1&navpanes=1" title="<?= e($book['title']) ?>"></iframe>
      <?php elseif ($ext === 'txt' && $txtContent !== null): ?>
        <div class="reader-text-wrap">
          <pre class="reader-text-content"><?= e($txtContent) ?></pre>
        </div>
      <?php elseif ($ext === 'txt'): ?>
        <div class="reader-other">
          <p>This text file is too large to display or could not be loaded. You can open or download it instead.</p>
          <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener" class="btn">Open TXT in new tab</a>
          <a href="<?= base_url('books/download.php?id=' . $id) ?>" class="btn btn-secondary">Download</a>
        </div>
      <?php elseif ($ext === 'epub'): ?>
        <div id="epub-viewer" class="reader-epub-viewer"></div>
        <script src="https://cdn.jsdelivr.net/npm/epubjs@0.3.93/dist/epub.min.js"></script>
        <script>
        (function() {
          var viewer = document.getElementById('epub-viewer');
          if (!viewer) return;
          var bookUrl = <?= json_encode($fullBookUrl) ?>;
          try {
            var book = ePub(bookUrl);
            var rendition = book.renderTo(viewer, { width: '100%', height: '100%', spread: 'none', method: 'continuous' });
            rendition.display();
            rendition.themes.fontSize('100%');
            document.querySelector('.reader-toolbar-right').insertAdjacentHTML('afterbegin',
              '<button type="button" class="reader-btn" id="epub-prev" title="Previous">‹ Prev</button>' +
              '<button type="button" class="reader-btn" id="epub-next" title="Next">Next ›</button>');
            document.getElementById('epub-prev').addEventListener('click', function() { rendition.prev(); });
            document.getElementById('epub-next').addEventListener('click', function() { rendition.next(); });
          } catch (e) {
            viewer.innerHTML = '<div class="reader-other"><p>Could not load EPUB in browser. Try opening in a new tab or download.</p>' +
              '<a href="' + <?= json_encode($fileUrl) ?> + '" target="_blank" rel="noopener" class="btn">Open EPUB in new tab</a> ' +
              '<a href="<?= base_url('books/download.php?id=' . $id) ?>" class="btn btn-secondary">Download</a></div>';
          }
        })();
        </script>
      <?php else: ?>
        <div class="reader-other">
          <p><strong><?= e(book_format_label($ext)) ?></strong> files are best opened in an app or downloaded. You can open in a new tab (if your browser supports it) or download.</p>
          <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener" class="btn">Open <?= e(book_format_label($ext)) ?> in new tab</a>
          <a href="<?= base_url('books/download.php?id=' . $id) ?>" class="btn btn-secondary">Download</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function() {
  var page = document.getElementById('reader-page');
  var btn = document.getElementById('reader-fullscreen-btn');
  if (!page || !btn) return;
  btn.addEventListener('click', function() {
    document.body.classList.toggle('reader-fullscreen-on');
    btn.setAttribute('aria-pressed', document.body.classList.contains('reader-fullscreen-on'));
    btn.textContent = document.body.classList.contains('reader-fullscreen-on') ? '✕ Exit fullscreen' : '⛶ Fullscreen';
  });
})();
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
