<?php
/**
 * Simple API documentation page (for in-browser link from About page).
 */
$doc = __DIR__ . '/API.md';
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Documentation – Library</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 720px; margin: 0 auto; padding: 1.5rem; line-height: 1.6; }
    pre, code { background: #f0f0f0; padding: 0.2em 0.4em; border-radius: 4px; font-size: 0.9em; }
    pre { padding: 1rem; overflow-x: auto; }
    h1 { border-bottom: 1px solid #ccc; padding-bottom: 0.5rem; }
    h2 { margin-top: 1.5rem; font-size: 1.2rem; }
    a { color: #8b6914; }
    table { border-collapse: collapse; width: 100%; margin: 1rem 0; }
    th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
  </style>
</head>
<body>
<h1>Library API (for Mobile Developers)</h1>
<p>Base URL: <code>https://your-domain.com/Bookslibrary/api/v1/</code>. All responses are JSON. Use <code>Authorization: Bearer &lt;token&gt;</code> for protected endpoints.</p>

<h2>App info (no auth)</h2>
<p><strong>GET</strong> <code>/app-info.php</code> — Returns site name, logo URL, app icon URL.</p>

<h2>Authentication</h2>
<p><strong>POST</strong> <code>/auth/register.php</code> — Body: username, email, password, full_name, register_as_author</p>
<p><strong>POST</strong> <code>/auth/login.php</code> — Body: email, password. Returns token and user.</p>
<p><strong>GET / PUT</strong> <code>/auth/profile.php</code> — Get or update profile (Bearer).</p>

<h2>Books</h2>
<p><strong>GET</strong> <code>/books.php</code> — List (page, limit, q, category, sort) or single <code>?id=1</code>.</p>
<p><strong>GET</strong> <code>/categories.php</code> — Themes/categories.</p>
<p><strong>POST</strong> <code>/books/read.php</code> — Record view (Bearer). Body: book_id.</p>
<p><strong>POST</strong> <code>/books/download.php</code> — Get temporary download URL (Bearer). Body: book_id.</p>

<h2>Favorites (Bearer)</h2>
<p><strong>GET</strong> <code>/favorites.php</code> — List. <strong>POST</strong> — Add (book_id). <strong>DELETE</strong> <code>?book_id=1</code> — Remove.</p>

<h2>Reviews</h2>
<p><strong>GET</strong> <code>/reviews.php?book_id=1</code> — List. <strong>POST</strong> — Add (Bearer). Body: book_id, rating, comment.</p>

<h2>User (Bearer)</h2>
<p><strong>GET</strong> <code>/user/history.php</code> — Reading history. <strong>GET</strong> <code>/user/downloads.php</code> — Download history.</p>

<h2>Errors</h2>
<p>JSON: <code>{"error": "message", "code": "CODE"}</code>. HTTP: 200, 201, 400, 401, 403, 404, 405, 500.</p>

<p><a href="../about.php">Back to About</a></p>
</body>
</html>
