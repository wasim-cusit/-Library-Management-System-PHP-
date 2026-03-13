# Library Management System (PHP)

A web-based library where users can browse books by **themes**, read online, download (when allowed), favorite, and review. Books have **title**, **description**, **theme**, **publisher**, **publish date**, and **author** (authors are users). Books can be **free** or **paid**, **downloadable** or not, and available for **view on Web** and/or **view in App**.

## Requirements

- PHP 7.4+ with PDO MySQL
- MySQL 5.7+ or MariaDB
- Apache (XAMPP) or similar

## Setup

1. **Database**
   - Open phpMyAdmin or MySQL CLI.
   - Run the SQL script: `sql/schema.sql`
   - This creates database `bookslibrary`, tables (users, themes, publishers, books, favorites, reviews, reading_history, download_history), and seeds an admin user and sample themes.
   - Default admin: **email** `admin@library.local`, **password** `password`.

2. **Config**
   - Edit `config/database.php` if needed (host, db name, user, password).
   - Edit `config/app.php`: set `BASE_URL` to your project URL (e.g. `/Bookslibrary` for XAMPP).

3. **Web**
   - Place the project under your web root (e.g. `C:\xampp\htdocs\Bookslibrary`).
   - Ensure `assets/uploads/covers` and `assets/uploads/books` are writable (created automatically if possible).

## Features

- **Users**: Register, login, profile. Roles: **user**, **author**, **admin**.
- **Authors**: Register as author or be set by admin. Authors can add/edit/delete their own books.
- **Books**: Title, description, ISBN, theme, publisher, published date, cover, file (PDF/EPUB), free/paid, downloadable, view on web, view in app.
- **Themes**: Categories (e.g. Fiction, Science). Admin can add themes.
- **Publishers**: “Who published this” – select existing or enter new when adding a book.
- **Favorites**: Logged-in users can add/remove favorites.
- **Reviews**: Rating (1–5) and comment per book.
- **Read online**: For books with `view_in_web` and a file, opens in browser (e.g. PDF).
- **Download**: For books with `is_downloadable` and a file; increments download count.
- **Admin**: Dashboard (stats, top views/downloads), manage themes, manage users (set role to author).

## Admin settings

- **Admin → Site settings**: Edit site name, tagline, **website logo**, and **mobile app icon**.
- Logo is shown in the header; app icon and favicon can be used by mobile apps (same icon or separate).
- Upload logo and app icon (e.g. 512×512 PNG for app store). These are stored under `assets/uploads/site/`.

## API for mobile developers

REST API under `/api/v1/` for iOS/Android apps. See **[api/API.md](api/API.md)** for full documentation.

- **Auth**: JWT. `POST auth/login.php`, `POST auth/register.php`; then send `Authorization: Bearer <token>`.
- **App info**: `GET app-info.php` – returns site name, logo URL, app icon URL (for splash/icon).
- **Books**: `GET books.php` (list/single), `GET categories.php`, `POST books/read.php`, `POST books/download.php` (returns temporary download URL).
- **Favorites**: `GET/POST/DELETE favorites.php`.
- **Reviews**: `GET/POST reviews.php`.
- **User**: `GET user/history.php`, `GET user/downloads.php`, `GET/PUT auth/profile.php`.

Base URL example: `http://localhost/Bookslibrary/api/v1/`. In production use HTTPS.

## URLs (relative to BASE_URL)

- `/` – Home
- `/books/` – List/search/filter books (theme, author, sort)
- `/books/detail.php?id=...` – Book detail (metadata, read, download, favorite, reviews)
- `/themes/` – List themes
- `/themes/view.php?slug=...` – Books in a theme
- `/auth/login.php`, `/auth/register.php`, `/auth/profile.php`, `/auth/logout.php`
- `/user/favorites.php` – My favorites
- `/author/` – Author dashboard (my books), `/author/add.php`, `/author/edit.php?id=...`
- `/admin/` – Admin dashboard
- `/admin/settings.php` – **Site settings** (name, logo, app icon)
- `/admin/themes.php`, `/admin/users.php`
- `/api/v1/` – **REST API** for mobile (see api/API.md)

## Security notes

- Change the default admin password after first login.
- In production use HTTPS and strong passwords; consider rate limiting and CSRF on all forms (CSRF is used on forms here).
