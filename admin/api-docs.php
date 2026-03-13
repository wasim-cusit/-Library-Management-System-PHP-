<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$baseUrl = rtrim(SITE_BASE_URL, '/') . '/api/v1';
$assetsBase = rtrim(SITE_BASE_URL, '/');
$pageTitle = 'API docs for developers';
$pageRobots = 'noindex, nofollow';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="admin-api-docs">
  <h1>API documentation for developers</h1>
  <p class="api-docs-intro">Share this with mobile or front-end developers. All endpoints return JSON. Use the <strong>Base URL</strong> below and send <code>Authorization: Bearer &lt;token&gt;</code> for protected endpoints.</p>

  <section class="api-docs-section">
    <h2>Base URL &amp; authentication</h2>
    <div class="api-docs-block">
      <p><strong>Base URL</strong> (use in your app):</p>
      <pre class="api-pre"><?= e($baseUrl) ?>/</pre>
      <p>Example: <code><?= e($baseUrl) ?>/books.php</code></p>
    </div>
    <div class="api-docs-block">
      <p><strong>How to authenticate</strong></p>
      <ol>
        <li>Call <strong>POST</strong> <code>/auth/login.php</code> with email and password (see example below).</li>
        <li>You receive a <code>token</code> (JWT) in the response.</li>
        <li>For every protected request, add header: <code>Authorization: Bearer YOUR_TOKEN</code></li>
      </ol>
    </div>
  </section>

  <section class="api-docs-section">
    <h2>App info (no auth)</h2>
    <div class="api-endpoint">
      <span class="api-method api-method-get">GET</span>
      <code>/app-info.php</code>
      <p>Returns site name, logo URL, and app icon URL. Use for splash screen and app icon.</p>
      <p><strong>Response example:</strong></p>
      <pre class="api-pre">{
  "site_name": "Library",
  "site_tagline": "Read, download & discover books",
  "logo_url": "<?= e($assetsBase) ?>/assets/uploads/site/logo.png",
  "app_icon_url": "<?= e($assetsBase) ?>/assets/uploads/site/app-icon.png",
  "favicon_url": "<?= e($assetsBase) ?>/assets/uploads/site/app-icon.png"
}</pre>
    </div>
  </section>

  <section class="api-docs-section">
    <h2>Authentication</h2>

    <div class="api-endpoint">
      <span class="api-method api-method-post">POST</span>
      <code>/auth/register.php</code>
      <p>Register a new user.</p>
      <p><strong>Request body (JSON):</strong></p>
      <pre class="api-pre">{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "secret123",
  "full_name": "John Doe",
  "register_as_author": false
}</pre>
      <p><strong>Response (201):</strong></p>
      <pre class="api-pre">{
  "message": "User registered successfully",
  "user_id": 123
}</pre>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-post">POST</span>
      <code>/auth/login.php</code>
      <p>Login and get JWT token. Use this token in <code>Authorization: Bearer &lt;token&gt;</code> for other requests.</p>
      <p><strong>Request body (JSON):</strong></p>
      <pre class="api-pre">{
  "email": "john@example.com",
  "password": "secret123"
}</pre>
      <p><strong>Response (200):</strong></p>
      <pre class="api-pre">{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 123,
    "username": "johndoe",
    "email": "john@example.com",
    "full_name": "John Doe",
    "avatar": null,
    "role": "user"
  }
}</pre>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-get">GET</span>
      <code>/auth/profile.php</code>
      <span class="api-auth-badge">Requires Bearer token</span>
      <p>Get current user profile.</p>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-put">PUT</span>
      <code>/auth/profile.php</code>
      <span class="api-auth-badge">Requires Bearer token</span>
      <p>Update profile.</p>
      <p><strong>Request body (JSON):</strong></p>
      <pre class="api-pre">{
  "full_name": "John Doe"
}</pre>
    </div>
  </section>

  <section class="api-docs-section">
    <h2>Books</h2>

    <div class="api-endpoint">
      <span class="api-method api-method-get">GET</span>
      <code>/books.php</code>
      <p>List books with pagination and filters.</p>
      <p><strong>Query parameters:</strong></p>
      <ul>
        <li><code>page</code> (default 1)</li>
        <li><code>limit</code> (default 20, max 50)</li>
        <li><code>q</code> – search term (title, description)</li>
        <li><code>category</code> – theme slug (e.g. <code>fiction</code>)</li>
        <li><code>sort</code> – <code>title</code>, <code>-views</code>, <code>-downloads</code>, <code>-rating</code>, <code>-created</code></li>
      </ul>
      <p><strong>Example:</strong> <code>GET <?= e($baseUrl) ?>/books.php?page=1&limit=10&sort=-created</code></p>
      <p><strong>Response (200):</strong></p>
      <pre class="api-pre">{
  "data": [
    {
      "id": 1,
      "title": "The Great Gatsby",
      "description": "...",
      "cover_url": "https://.../covers/abc.jpg",
      "is_free": true,
      "is_downloadable": true,
      "view_in_web": true,
      "view_in_app": true,
      "views": 100,
      "downloads": 20,
      "theme_name": "Fiction",
      "author_name": "author1",
      "average_rating": 4.5,
      "favorites": 15
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 10,
    "total": 50,
    "total_pages": 5
  }
}</pre>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-get">GET</span>
      <code>/books.php?id=1</code>
      <p>Get a single book by ID. Includes <code>is_favorited</code> if request has Bearer token. <code>file_url</code> is never returned; use download endpoint to get a temporary URL.</p>
      <p><strong>Response (200):</strong> Full book object with <code>average_rating</code>, <code>total_reviews</code>, <code>views</code>, <code>downloads</code>, <code>is_favorited</code>.</p>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-get">GET</span>
      <code>/categories.php</code>
      <p>List all themes/categories.</p>
      <p><strong>Response (200):</strong></p>
      <pre class="api-pre">{
  "data": [
    {
      "id": 1,
      "name": "Fiction",
      "slug": "fiction",
      "description": "Novels and fiction",
      "book_count": 12
    }
  ]
}</pre>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-post">POST</span>
      <code>/books/read.php</code>
      <span class="api-auth-badge">Requires Bearer token</span>
      <p>Record that the user viewed the book (increments view count). For paid books, user must have access.</p>
      <p><strong>Request body (JSON):</strong></p>
      <pre class="api-pre">{
  "book_id": 1
}</pre>
      <p><strong>Response (200):</strong></p>
      <pre class="api-pre">{
  "message": "View recorded",
  "total_views": 101
}</pre>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-post">POST</span>
      <code>/books/download.php</code>
      <span class="api-auth-badge">Requires Bearer token</span>
      <p>Get a temporary download URL for the book file. For paid books, user must have access. Use the returned URL to download the file (valid 5 minutes).</p>
      <p><strong>Request body (JSON):</strong></p>
      <pre class="api-pre">{
  "book_id": 1
}</pre>
      <p><strong>Response (200):</strong></p>
      <pre class="api-pre">{
  "download_url": "https://.../api/v1/books/download-file.php?token=...",
  "expires_in": 300
}</pre>
    </div>
  </section>

  <section class="api-docs-section">
    <h2>Favorites</h2>
    <p class="api-auth-note">All require <strong>Authorization: Bearer &lt;token&gt;</strong></p>

    <div class="api-endpoint">
      <span class="api-method api-method-get">GET</span>
      <code>/favorites.php</code>
      <p>List the current user's favorite books. Response: <code>{"data": [...]}</code></p>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-post">POST</span>
      <code>/favorites.php</code>
      <p>Add a book to favorites.</p>
      <p><strong>Request body:</strong> <code>{"book_id": 1}</code></p>
      <p><strong>Response (200):</strong> <code>{"message": "Book added to favorites", "favorites_count": 5}</code></p>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-delete">DELETE</span>
      <code>/favorites.php?book_id=1</code>
      <p>Remove a book from favorites.</p>
    </div>
  </section>

  <section class="api-docs-section">
    <h2>Reviews</h2>

    <div class="api-endpoint">
      <span class="api-method api-method-get">GET</span>
      <code>/reviews.php?book_id=1&page=1&limit=20</code>
      <p>List reviews for a book. No auth required.</p>
      <p><strong>Response:</strong> <code>{"data": [...], "pagination": {...}}</code></p>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-post">POST</span>
      <code>/reviews.php</code>
      <span class="api-auth-badge">Requires Bearer token</span>
      <p>Add or update your review for a book.</p>
      <p><strong>Request body (JSON):</strong></p>
      <pre class="api-pre">{
  "book_id": 1,
  "rating": 5,
  "comment": "Excellent book!"
}</pre>
      <p><strong>Response (201):</strong> Created review object with <code>id</code>, <code>user_id</code>, <code>rating</code>, <code>comment</code>, <code>created_at</code>, <code>username</code>.</p>
    </div>
  </section>

  <section class="api-docs-section">
    <h2>User history</h2>
    <p class="api-auth-note">Requires <strong>Authorization: Bearer &lt;token&gt;</strong></p>

    <div class="api-endpoint">
      <span class="api-method api-method-get">GET</span>
      <code>/user/history.php</code>
      <p>Reading history (books the user has viewed). Response: <code>{"data": [{"id", "title", "cover_url", "theme_name", "author_name", "viewed_at"}, ...]}</code></p>
    </div>

    <div class="api-endpoint">
      <span class="api-method api-method-get">GET</span>
      <code>/user/downloads.php</code>
      <p>Download history. Response: <code>{"data": [{"id", "title", "cover_url", "downloaded_at"}, ...]}</code></p>
    </div>
  </section>

  <section class="api-docs-section">
    <h2>Errors</h2>
    <p>Failed requests return JSON and an HTTP status code.</p>
    <p><strong>Error response body:</strong></p>
    <pre class="api-pre">{
  "error": "Error message for the user",
  "code": "ERROR_CODE"
}</pre>
    <p><strong>HTTP status codes:</strong></p>
    <ul class="api-status-list">
      <li><code>200</code> OK – Success</li>
      <li><code>201</code> Created – Resource created (e.g. register, review)</li>
      <li><code>400</code> Bad Request – Invalid input</li>
      <li><code>401</code> Unauthorized – Missing or invalid token</li>
      <li><code>403</code> Forbidden – No permission (e.g. paid book access required)</li>
      <li><code>404</code> Not Found – Resource not found</li>
      <li><code>405</code> Method Not Allowed – Wrong HTTP method</li>
      <li><code>500</code> Internal Server Error – Server error</li>
    </ul>
  </section>

  <section class="api-docs-section">
    <h2>Quick copy: cURL examples</h2>
    <p>Replace <code>BASE</code> and <code>TOKEN</code> with your base URL and JWT.</p>
    <div class="api-docs-block">
      <p><strong>Login (get token):</strong></p>
      <pre class="api-pre">curl -X POST "<?= e($baseUrl) ?>/auth/login.php" \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"secret123"}'</pre>
    </div>
    <div class="api-docs-block">
      <p><strong>Get books (no auth):</strong></p>
      <pre class="api-pre">curl "<?= e($baseUrl) ?>/books.php?page=1&limit=5"</pre>
    </div>
    <div class="api-docs-block">
      <p><strong>Get profile (with token):</strong></p>
      <pre class="api-pre">curl "<?= e($baseUrl) ?>/auth/profile.php" \
  -H "Authorization: Bearer YOUR_TOKEN"</pre>
    </div>
    <div class="api-docs-block">
      <p><strong>Add to favorites:</strong></p>
      <pre class="api-pre">curl -X POST "<?= e($baseUrl) ?>/favorites.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"book_id":1}'</pre>
    </div>
  </section>

  <p><a href="<?= base_url('admin/') ?>" class="btn btn-secondary">Back to dashboard</a></p>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
