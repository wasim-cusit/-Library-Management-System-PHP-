<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_author();

$id = (int) ($_GET['id'] ?? 0);
if (!$id) redirect(base_url('author/'));

$pdo = getDb();
$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book || !can_edit_book($id)) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Not allowed.';
    exit;
}

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

            $cover_name = $book['cover_url'];
            $file_name = $book['file_url'];

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
              UPDATE books SET title=?, description=?, isbn=?, theme_id=?, publisher_id=?, published_date=?, cover_url=?, file_url=?, is_free=?, is_downloadable=?, view_in_web=?, view_in_app=?, access_duration_days=?, updated_at=NOW()
              WHERE id=?
            ');
            $stmt->execute([
                $title, $description ?: null, $isbn ?: null, $theme_id, $publisher_id ?: null, $published_date,
                $cover_name, $file_name, $is_free ? 1 : 0, $is_downloadable ? 1 : 0, $view_in_web ? 1 : 0, $view_in_app ? 1 : 0,
                $access_duration_days, $id
            ]);
            $book = array_merge($book, [
                'title' => $title, 'description' => $description, 'isbn' => $isbn, 'theme_id' => $theme_id,
                'publisher_id' => $publisher_id ?: null, 'published_date' => $published_date,
                'cover_url' => $cover_name, 'file_url' => $file_name,
                'is_free' => $is_free, 'is_downloadable' => $is_downloadable, 'view_in_web' => $view_in_web, 'view_in_app' => $view_in_app,
                'access_duration_days' => $access_duration_days
            ]);
            $success = true;
        }
    }
}

$pageTitle = 'Edit: ' . $book['title'];
$pageRobots = 'noindex, nofollow';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>Edit book</h1>
<?php if ($success): ?>
  <p class="success">Saved. <a href="<?= base_url('author/') ?>">My books</a> | <a href="<?= base_url('books/detail.php?id=' . $id) ?>">View book</a></p>
<?php else: ?>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="container">
    <?= csrf_field() ?>
    <label>Title * <input type="text" name="title" value="<?= e($book['title']) ?>" required></label>
    <label>Description <textarea name="description" rows="5"><?= e($book['description']) ?></textarea></label>
    <label>ISBN <input type="text" name="isbn" value="<?= e($book['isbn']) ?>"></label>
    <label>Theme * <select name="theme_id" required>
      <?php foreach ($themes as $t): ?>
        <option value="<?= $t['id'] ?>" <?= (int)$book['theme_id'] === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
      <?php endforeach; ?>
    </select></label>
    <label>Publisher <select name="publisher_id">
      <option value="">—</option>
      <?php foreach ($publishers as $p): ?>
        <option value="<?= $p['id'] ?>" <?= (int)$book['publisher_id'] === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
      <?php endforeach; ?>
    </select></label>
    <label>Or new publisher name <input type="text" name="publisher_new" placeholder="Who published this book?"></label>
    <label>Published date <input type="date" name="published_date" value="<?= e($book['published_date']) ?>"></label>
    <label>Cover image <input type="file" name="cover" accept=".jpg,.jpeg,.png,.gif,.webp"> <?php if ($book['cover_url']): ?>(current: <?= e($book['cover_url']) ?>)<?php endif; ?></label>
    <label>Book file (PDF, EPUB, TXT, Word, MOBI, RTF) <input type="file" name="book_file" accept="<?= e(implode(',', array_map(function($e){ return '.'.$e; }, get_allowed_book_extensions()))) ?>"> <?php if ($book['file_url']): ?>(current kept if not changed)<?php endif; ?></label>
    <label class="checkbox"><input type="checkbox" name="is_free" value="1" id="edit_is_free" <?= $book['is_free'] ? 'checked' : '' ?>> Free</label>
    <?php
    $ad = isset($book['access_duration_days']) ? $book['access_duration_days'] : null;
    $adSel = $ad === null ? '' : (in_array((int)$ad, [30,90,180,365], true) ? (string)(int)$ad : 'custom');
    ?>
    <div class="access-duration-row" id="edit_access_duration_row" style="display:<?= $book['is_free'] ? 'none' : 'block' ?>;">
      <label>Paid: access duration</label>
      <select name="access_duration" id="edit_access_duration">
        <option value="" <?= $adSel === '' ? 'selected' : '' ?>>Lifetime access</option>
        <option value="30" <?= $adSel === '30' ? 'selected' : '' ?>>30 days</option>
        <option value="90" <?= $adSel === '90' ? 'selected' : '' ?>>90 days</option>
        <option value="180" <?= $adSel === '180' ? 'selected' : '' ?>>180 days</option>
        <option value="365" <?= $adSel === '365' ? 'selected' : '' ?>>1 year</option>
        <option value="custom" <?= $adSel === 'custom' ? 'selected' : '' ?>>Custom days</option>
      </select>
      <label id="edit_custom_days_label" style="display:<?= $adSel === 'custom' ? 'block' : 'none' ?>;">Days <input type="number" name="access_duration_custom" id="edit_access_duration_custom" min="1" max="3650" value="<?= $adSel === 'custom' ? (int)$ad : '' ?>"></label>
    </div>
    <label class="checkbox"><input type="checkbox" name="is_downloadable" value="1" <?= $book['is_downloadable'] ? 'checked' : '' ?>> Downloadable</label>
    <label class="checkbox"><input type="checkbox" name="view_in_web" value="1" <?= $book['view_in_web'] ? 'checked' : '' ?>> View on Web</label>
    <label class="checkbox"><input type="checkbox" name="view_in_app" value="1" <?= $book['view_in_app'] ? 'checked' : '' ?>> View in App</label>
    <button type="submit" class="btn">Save</button>
  </form>
  <script>
  (function() {
    var isFree = document.getElementById('edit_is_free');
    var row = document.getElementById('edit_access_duration_row');
    var sel = document.getElementById('edit_access_duration');
    var customLabel = document.getElementById('edit_custom_days_label');
    function toggle() {
      if (row) row.style.display = isFree && !isFree.checked ? 'block' : 'none';
      if (customLabel) customLabel.style.display = sel && sel.value === 'custom' ? 'block' : 'none';
    }
    if (isFree) isFree.addEventListener('change', toggle);
    if (sel) sel.addEventListener('change', toggle);
  })();
  </script>
<?php endif; ?>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
