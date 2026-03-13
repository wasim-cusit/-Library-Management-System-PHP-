# Library API (for Mobile Developers)

Base URL: `https://your-domain.com/Bookslibrary/api/v1/`  
All responses are JSON. Authentication uses **JWT**: send `Authorization: Bearer <token>` for protected endpoints.

---

## App info (no auth)

**GET** `/app-info.php`  
Returns site name, logo URL, and app icon URL (use for splash screen and app icon).

Response:
```json
{
  "site_name": "Library",
  "site_tagline": "Read, download & discover books",
  "logo_url": "https://.../assets/uploads/site/logo.png",
  "app_icon_url": "https://.../assets/uploads/site/app-icon.png",
  "favicon_url": "https://.../assets/uploads/site/app-icon.png"
}
```

---

## Authentication

### Register  
**POST** `/auth/register.php`  
Body: `{"username":"...","email":"...","password":"...","full_name":"...","register_as_author":false}`  
Returns: `{"message":"User registered successfully","user_id":123}`

### Login  
**POST** `/auth/login.php`  
Body: `{"email":"...","password":"..."}`  
Returns: `{"token":"<JWT>","user":{...}}`

### Profile (Bearer required)  
**GET** `/auth/profile.php` – get current user  
**PUT** `/auth/profile.php` – update. Body: `{"full_name":"..."}`

---

## Books

### List books  
**GET** `/books.php?page=1&limit=20&q=...&category=<theme_slug>&sort=-created`  
Sort: `title`, `-views`, `-downloads`, `-rating`, `-created`  
Returns: `{"data":[...],"pagination":{...}}`

### Single book  
**GET** `/books.php?id=1`  
Returns full book object including `is_favorited` (if authenticated), `average_rating`, `total_reviews`, `views`, `downloads`.  
`file_url` is never exposed; use download endpoint.

### Categories (themes)  
**GET** `/categories.php`  
Returns: `{"data":[{"id", "name", "slug", "description", "book_count"},...]}`

### Record view (Bearer required)  
**POST** `/books/read.php`  
Body: `{"book_id":1}`  
Returns: `{"message":"View recorded","total_views":...}`

### Get download URL (Bearer required)  
**POST** `/books/download.php`  
Body: `{"book_id":1}`  
Returns: `{"download_url":"...","expires_in":300}`  
Use the temporary URL to download the file (valid 5 minutes).

---

## Favorites (Bearer required)

**GET** `/favorites.php` – list user's favorites  
**POST** `/favorites.php` – add. Body: `{"book_id":1}`  
**DELETE** `/favorites.php?book_id=1` – remove

---

## Reviews

**GET** `/reviews.php?book_id=1&page=1&limit=20` – list reviews for a book  
**POST** `/reviews.php` – add (Bearer). Body: `{"book_id":1,"rating":5,"comment":"..."}`

---

## User history (Bearer required)

**GET** `/user/history.php` – reading history  
**GET** `/user/downloads.php` – download history  

---

## Error format

```json
{"error": "Error message", "code": "ERROR_CODE"}
```

HTTP status: `200` OK, `201` Created, `400` Bad Request, `401` Unauthorized, `403` Forbidden, `404` Not Found, `405` Method Not Allowed, `500` Server Error.

---

## Logo & app icon

Configure in **Admin → Site settings**. Upload logo (website header) and app icon (e.g. 512×512 for mobile).  
`GET /api/v1/app-info.php` returns full URLs for use in your app.
