<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_author();

$pdo = getDb();
$themes = $pdo->query('SELECT id, name FROM themes ORDER BY sort_order, name')->fetchAll();
$publishers = $pdo->query('SELECT id, name FROM publishers ORDER BY name')->fetchAll();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid request.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isbn = trim($_POST['isbn'] ?? '');
        $theme_id = (int) ($_POST['theme_id'] ?? 0);
        $publisher_id = (int) ($_POST['publisher_id'] ?? 0);
        $publisher_new = trim($_POST['publisher_new'] ?? '');
        $published_date = trim($_POST['published_date'] ?? '') ?: null;
        $is_free = !empty($_POST['is_free']);
        $is_downloadable = !empty($_POST['is_downloadable']);
        $view_in_web = !empty($_POST['view_in_web']);
        $view_in_app = !empty($_POST['view_in_app']);
        $access_duration = trim($_POST['access_duration'] ?? '');
        $access_duration_custom = (int) ($_POST['access_duration_custom'] ?? 0);
        $access_duration_days = null;
        if (!$is_free) {
            if ($access_duration === 'custom' && $access_duration_custom > 0) {
                $access_duration_days = $access_duration_custom;
            } elseif (in_array($access_duration, ['30', '90', '180', '365'], true)) {
                $access_duration_days = (int) $access_duration;
            }
        }

        if ($title === '') {
            $error = 'Title is required.';
        } elseif ($theme_id < 1) {
            $error = 'Please select a theme.';
        } else {
            if ($publisher_new !== '') {
                $slug = slugify($publisher_new);
                $stmt = $pdo->prepare('INSERT IGNORE INTO publishers (name, slug) VALUES (?, ?)');
                $stmt->execute([$publisher_new, $slug]);
                if ($publisher_id < 1) {
                    $publisher_id = (int) $pdo->lastInsertId();
                    if ($publisher_id === 0) {
                        $stmt = $pdo->prepare('SELECT id FROM publishers WHERE slug = ?');
                        $stmt->execute([$slug]);
                        $publisher_id = (int) $stmt->fetchColumn();
                    }
                }
            }

            $cover_name = null;
            $file_name = null;

            if (!empty($_FILES['cover']['tmp_name']) && is_uploaded_file($_FILES['cover']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
                    $cover_name = uniqid('c') . '.' . $ext;
                    move_uploaded_file($_FILES['cover']['tmp_name'], UPLOAD_COVERS . '/' . $cover_name);
                }
            }
            if (!empty($_FILES['book_file']['tmp_name']) && is_uploaded_file($_FILES['book_file']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['book_file']['name'], PATHINFO_EXTENSION));
                if (is_allowed_book_extension($ext)) {
                    $file_name = uniqid('b') . '.' . $ext;
                    move_uploaded_file($_FILES['book_file']['tmp_name'], UPLOAD_BOOKS . '/' . $file_name);
                }
            }

            $stmt = $pdo->prepare('
              INSERT INTO books (title, description, isbn, theme_id, author_id, publisher_id, published_date, cover_url, file_url, is_free, is_downloadable, view_in_web, view_in_app, access_duration_days)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $title, $description ?: null, $isbn ?: null, $theme_id, current_user()['id'],
                $publisher_id ?: null, $published_date, $cover_name, $file_name,
                $is_free ? 1 : 0, $is_downloadable ? 1 : 0, $view_in_web ? 1 : 0, $view_in_app ? 1 : 0,
                $access_duration_days
            ]);
            $success = true;
        }
    }
}

$pageTitle = 'Add book';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>Add book</h1>
<?php if ($success): ?>
  <p class="success">Book added. <a href="<?= base_url('author/') ?>">Back to my books</a> or <a href="<?= base_url('author/add.php') ?>">Add another</a>.</p>
<?php else: ?>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="container">
    <?= csrf_field() ?>
    <label>Title * <input type="text" name="title" value="<?= e($_POST['title'] ?? '') ?>" required></label>
    <label>Description <textarea name="description" rows="5"><?= e($_POST['description'] ?? '') ?></textarea></label>
    <label>ISBN <input type="text" name="isbn" value="<?= e($_POST['isbn'] ?? '') ?>"></label>
    <label>Theme * <select name="theme_id" required>
      <option value="">Select theme</option>
      <?php foreach ($themes as $t): ?>
        <option value="<?= $t['id'] ?>" <?= (int)($_POST['theme_id'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
      <?php endforeach; ?>
    </select></label>
    <label>Publisher (existing) <select name="publisher_id">
      <option value="">—</option>
      <?php foreach ($publishers as $p): ?>
        <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
      <?php endforeach; ?>
    </select></label>
    <label>Or new publisher name <input type="text" name="publisher_new" value="<?= e($_POST['publisher_new'] ?? '') ?>" placeholder="Who published this book?"></label>
    <label>Published date <input type="date" name="published_date" value="<?= e($_POST['published_date'] ?? '') ?>"></label>
    <label>Cover image <input type="file" name="cover" accept=".jpg,.jpeg,.png,.gif,.webp"></label>
    <label>Book file (PDF, EPUB, TXT, Word, MOBI, RTF) <input type="file" name="book_file" accept="<?= e(implode(',', array_map(function($e){ return '.'.$e; }, get_allowed_book_extensions()))) ?>"></label>
    <label class="checkbox"><input type="checkbox" name="is_free" value="1" id="add_is_free" <?= empty($_POST['is_free']) ? 'checked' : '' ?>> Free</label>
    <div class="access-duration-row" id="add_access_duration_row" style="display:none;">
      <label>Paid: access duration (when user gets access)</label>
      <select name="access_duration" id="add_access_duration">
        <option value="">Lifetime access</option>
        <option value="30" <?= ($_POST['access_duration'] ?? '') === '30' ? 'selected' : '' ?>>30 days</option>
        <option value="90" <?= ($_POST['access_duration'] ?? '') === '90' ? 'selected' : '' ?>>90 days</option>
        <option value="180" <?= ($_POST['access_duration'] ?? '') === '180' ? 'selected' : '' ?>>180 days</option>
        <option value="365" <?= ($_POST['access_duration'] ?? '') === '365' ? 'selected' : '' ?>>1 year</option>
        <option value="custom" <?= ($_POST['access_duration'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom days</option>
      </select>
      <label id="add_custom_days_label" style="display:none;">Days <input type="number" name="access_duration_custom" id="add_access_duration_custom" min="1" max="3650" value="<?= (int)($_POST['access_duration_custom'] ?? 0) ?>"></label>
    </div>
    <label class="checkbox"><input type="checkbox" name="is_downloadable" value="1" <?= !empty($_POST['is_downloadable']) ? 'checked' : '' ?>> Downloadable</label>
    <label class="checkbox"><input type="checkbox" name="view_in_web" value="1" checked> View on Web</label>
    <label class="checkbox"><input type="checkbox" name="view_in_app" value="1" checked> View in App</label>
    <button type="submit" class="btn">Add book</button>
  </form>
  <script>
  (function() {
    var isFree = document.getElementById('add_is_free');
    var row = document.getElementById('add_access_duration_row');
    var sel = document.getElementById('add_access_duration');
    var customLabel = document.getElementById('add_custom_days_label');
    function toggle() {
      row.style.display = isFree && !isFree.checked ? 'block' : 'none';
      if (!row.style.display || row.style.display === 'none') customLabel.style.display = 'none';
      customLabel.style.display = sel && sel.value === 'custom' ? 'block' : 'none';
    }
    if (isFree) isFree.addEventListener('change', toggle);
    if (sel) sel.addEventListener('change', toggle);
    toggle();
  })();
  </script>
<?php endif; ?>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
