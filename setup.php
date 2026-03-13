<?php
/**
 * One-time setup: creates database, tables, seed data, and sets login credentials.
 * Run in browser: http://localhost/Bookslibrary/setup.php
 * After success, delete or restrict access to this file.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = '';
$error = '';
$done = false;

// Credentials to set (shown at the end)
define('SETUP_ADMIN_EMAIL', 'admin@library.local');
define('SETUP_ADMIN_PASS', 'Admin@123');
define('SETUP_AUTHOR_EMAIL', 'author@library.local');
define('SETUP_AUTHOR_PASS', 'Author@123');
define('SETUP_USER_EMAIL', 'user@library.local');
define('SETUP_USER_PASS', 'User@123');

function runSqlFile(PDO $pdo, string $filepath, bool $useDb = true): void {
    if (!is_file($filepath)) return;
    $sql = file_get_contents($filepath);
    $dbName = 'bookslibrary';
    if (!$useDb) {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");
    }
    $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if ($stmt === '') continue;
        if (stripos($stmt, 'USE ') === 0) continue;
        if (strtoupper(substr($stmt, 0, 3)) === 'SET' && stripos($stmt, 'FOREIGN_KEY_CHECKS') === false) continue;
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                // ignore duplicate table/column
            } else {
                throw $e;
            }
        }
    }
    if (!$useDb) $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['run_setup'])) {
    try {
        $host = trim($_POST['db_host'] ?? 'localhost');
        $user = trim($_POST['db_user'] ?? 'root');
        $pass = $_POST['db_pass'] ?? '';
        $db   = trim($_POST['db_name'] ?? 'bookslibrary');

        $step = 'Connecting to MySQL...';
        $pdo = new PDO(
            "mysql:host=" . $host . ";charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $step = 'Creating database and schema...';
        runSqlFile($pdo, __DIR__ . '/sql/schema.sql', false);

        $pdo->exec("USE `$db`");

        $step = 'Running seed data...';
        runSqlFile($pdo, __DIR__ . '/sql/seed.sql', true);

        $step = 'Setting login credentials...';
        $st = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
        $st->execute([password_hash(SETUP_ADMIN_PASS, PASSWORD_DEFAULT), SETUP_ADMIN_EMAIL]);
        $st->execute([password_hash(SETUP_AUTHOR_PASS, PASSWORD_DEFAULT), SETUP_AUTHOR_EMAIL]);
        $st->execute([password_hash(SETUP_USER_PASS, PASSWORD_DEFAULT), SETUP_USER_EMAIL]);

        $done = true;
    } catch (Throwable $e) {
        $error = $step . ' ' . $e->getMessage();
    }
}

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname($_SERVER['SCRIPT_NAME']) === '\\' ? '' : dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = rtrim($baseUrl, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library – Setup</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 600px; margin: 2rem auto; padding: 0 1rem; }
        h1 { margin-top: 0; }
        .box { background: #f5f5f5; border: 1px solid #ddd; border-radius: 8px; padding: 1.25rem; margin: 1rem 0; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        label { display: block; margin: 0.5rem 0 0.2rem; }
        input { padding: 0.4rem; width: 100%; max-width: 280px; }
        button { padding: 0.5rem 1.5rem; margin-top: 1rem; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin: 0.5rem 0; }
        th, td { text-align: left; padding: 0.4rem 0.6rem; border-bottom: 1px solid #eee; }
        code { background: #eee; padding: 0.15rem 0.4rem; border-radius: 4px; }
        .cred { font-weight: 600; }
    </style>
</head>
<body>
<h1>Library – Project setup</h1>

<?php if ($done): ?>
    <div class="box success">
        <p><strong>Setup complete.</strong> The database and sample data are ready.</p>
        <p><a href="<?= e($baseUrl) ?>">Open the Library website →</a></p>
    </div>
    <div class="box">
        <h2>Login credentials</h2>
        <p>Use these to sign in (change passwords after first login in production):</p>
        <table>
            <tr><th>Role</th><th>Email</th><th>Password</th></tr>
            <tr><td>Admin</td><td class="cred"><?= e(SETUP_ADMIN_EMAIL) ?></td><td class="cred"><?= e(SETUP_ADMIN_PASS) ?></td></tr>
            <tr><td>Author</td><td class="cred"><?= e(SETUP_AUTHOR_EMAIL) ?></td><td class="cred"><?= e(SETUP_AUTHOR_PASS) ?></td></tr>
            <tr><td>User (reader)</td><td class="cred"><?= e(SETUP_USER_EMAIL) ?></td><td class="cred"><?= e(SETUP_USER_PASS) ?></td></tr>
        </table>
        <p><strong>Admin</strong> can manage themes, users, and site settings. <strong>Author</strong> can add/edit books. <strong>User</strong> can browse, read, download, and review.</p>
    </div>
    <p><small>For security, delete or protect <code>setup.php</code> after setup.</small></p>
<?php else: ?>
    <?php if ($error): ?><div class="box error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="box">
        <p>This will create the database <code>bookslibrary</code>, all tables, and sample data (themes, users, books, reviews).</p>
        <form method="post">
            <input type="hidden" name="run_setup" value="1">
            <label>MySQL host</label>
            <input type="text" name="db_host" value="localhost" required>
            <label>MySQL user</label>
            <input type="text" name="db_user" value="root" required>
            <label>MySQL password</label>
            <input type="password" name="db_pass" value="">
            <label>Database name</label>
            <input type="text" name="db_name" value="bookslibrary" required>
            <button type="submit">Run setup</button>
        </form>
    </div>
<?php endif; ?>
</body>
</html>
