<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/settings.php';
require_admin();

$pdo = getDb();
$success = '';
$error = '';

// Ensure settings table exists (migration)
try {
    $pdo->query('SELECT 1 FROM settings LIMIT 1');
} catch (PDOException $e) {
    $pdo->exec("CREATE TABLE settings (`key` VARCHAR(80) NOT NULL PRIMARY KEY, `value` TEXT NULL, updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP)");
    $pdo->exec("INSERT INTO settings (`key`, `value`) VALUES ('site_name', 'Library'), ('site_tagline', 'Read, download & discover books'), ('logo_file', NULL), ('app_icon_file', NULL), ('favicon_file', NULL)");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $site_name = trim($_POST['site_name'] ?? '');
    $site_tagline = trim($_POST['site_tagline'] ?? '');
    if ($site_name === '') {
        $error = 'Site name is required.';
    } else {
        update_setting('site_name', $site_name);
        update_setting('site_tagline', $site_tagline);

        $allowed_img = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'];
        if (!empty($_FILES['logo']['tmp_name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed_img, true)) {
                $name = 'logo.' . $ext;
                move_uploaded_file($_FILES['logo']['tmp_name'], UPLOAD_SITE . '/' . $name);
                update_setting('logo_file', $name);
            }
        }
        if (!empty($_FILES['app_icon']['tmp_name']) && is_uploaded_file($_FILES['app_icon']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['app_icon']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed_img, true)) {
                $name = 'app-icon.' . $ext;
                move_uploaded_file($_FILES['app_icon']['tmp_name'], UPLOAD_SITE . '/' . $name);
                update_setting('app_icon_file', $name);
                update_setting('favicon_file', $name);
            }
        }
        if (!empty($_FILES['favicon']['tmp_name']) && is_uploaded_file($_FILES['favicon']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['ico', 'png', 'gif'], true)) {
                $name = 'favicon.' . $ext;
                move_uploaded_file($_FILES['favicon']['tmp_name'], UPLOAD_SITE . '/' . $name);
                update_setting('favicon_file', $name);
            }
        }

        clear_settings_cache();
        $success = 'Settings saved. Logo and app icon are used for the website and can be used as mobile app icons.';
    }
}

$site_name = get_setting('site_name', 'Library');
$site_tagline = get_setting('site_tagline', '');
$logo_url = site_logo_url();
$app_icon_url = site_app_icon_url();
$favicon_url = site_favicon_url();

$pageTitle = 'Site settings';
$pageRobots = 'noindex, nofollow';
$currentNav = 'admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<h1>Site settings</h1>
<p>Edit site name, tagline, website logo, and mobile app icon. The app icon is also used as favicon if no separate favicon is uploaded.</p>
<?php if ($success): ?><p class="success"><?= e($success) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="container">
  <?= csrf_field() ?>
  <label>Site name <input type="text" name="site_name" value="<?= e($site_name) ?>" required></label>
  <label>Tagline <input type="text" name="site_tagline" value="<?= e($site_tagline) ?>" placeholder="e.g. Read, download & discover books"></label>

  <h3>Website logo</h3>
  <?php if ($logo_url): ?>
    <p><img src="<?= e($logo_url) ?>" alt="Logo" style="max-height:60px; max-width:200px;"></p>
  <?php endif; ?>
  <label>Upload new logo <input type="file" name="logo" accept=".jpg,.jpeg,.png,.gif,.webp,.svg"></label>

  <h3>Mobile app icon</h3>
  <p>Use this icon for your iOS/Android app (e.g. 512×512 PNG recommended).</p>
  <?php if ($app_icon_url): ?>
    <p><img src="<?= e($app_icon_url) ?>" alt="App icon" style="width:96px; height:96px; object-fit:contain;"></p>
  <?php endif; ?>
  <label>Upload app icon <input type="file" name="app_icon" accept=".jpg,.jpeg,.png,.gif,.webp"></label>

  <h3>Favicon (optional)</h3>
  <label>Upload favicon <input type="file" name="favicon" accept=".ico,.png,.gif"></label>

  <button type="submit" class="btn">Save settings</button>
</form>

<p><a href="<?= base_url('admin/') ?>" class="btn btn-secondary">Back to dashboard</a></p>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
