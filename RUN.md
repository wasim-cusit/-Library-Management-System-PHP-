# How to run the Library project

## 1. Requirements

- **XAMPP** (or any Apache + PHP 7.4+ + MySQL)
- PHP with **PDO MySQL** extension enabled

## 2. Run setup (one time)

1. Start **Apache** and **MySQL** in XAMPP.
2. Open in browser:
   ```
   http://localhost/Bookslibrary/setup.php
   ```
3. Enter your MySQL details (default: host `localhost`, user `root`, password empty, database `bookslibrary`).
4. Click **Run setup**.
5. After success, you will see **login credentials**. Use them to sign in.

## 3. Login credentials (after setup)

| Role   | Email                 | Password   |
|--------|------------------------|------------|
| Admin  | `admin@library.local`  | `Admin@123` |
| Author | `author@library.local` | `Author@123` |
| User   | `user@library.local`  | `User@123`  |

- **Admin**: Site settings, themes, users, API docs.
- **Author**: Add/edit books (title, description, theme, publisher, free/paid, access duration).
- **User**: Browse, read, download, favorites, reviews.

## 4. Open the site

- **Home**: http://localhost/Bookslibrary/
- **Login**: http://localhost/Bookslibrary/auth/login.php

## 5. Config (optional)

- **Database**: Edit `config/database.php` if your MySQL user/password differ.
- **Base URL**: Edit `config/app.php` and set `BASE_URL` if the project is not in `/Bookslibrary/`.

## 6. After setup

- Delete or restrict access to `setup.php` in production.
- Change the default passwords after first login.

## Sample data included

- **Themes**: Fiction, Science, History, Biography, Education.
- **Users**: Admin, one Author (Sarah Chen), one Reader (John Smith).
- **Publishers**: Penguin Random House, HarperCollins, Oxford University Press, Tech Publications Inc.
- **Books**: 6 sample books (titles, descriptions, themes, free/paid). Upload your own PDF/EPUB/TXT etc. from Author dashboard to enable read/download.
- **Reviews and favorites** for the sample reader.
