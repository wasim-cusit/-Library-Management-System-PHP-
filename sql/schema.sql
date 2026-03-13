-- Library Management System - Database Schema
-- Run this in phpMyAdmin or: mysql -u root < sql/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS bookslibrary DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookslibrary;

-- Users: readers, authors, and admins (authors are users who publish books)
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(120) NULL,
  avatar VARCHAR(255) NULL,
  role ENUM('user','author','admin') NOT NULL DEFAULT 'user',
  bio TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role)
) ENGINE=InnoDB;

-- Themes/Categories for books (e.g. Fiction, Science, History)
CREATE TABLE themes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  description TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_slug (slug)
) ENGINE=InnoDB;

-- Publishers (who published the book - can be company or person)
CREATE TABLE publishers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(200) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_publisher_slug (slug)
) ENGINE=InnoDB;

-- Books: author_id links to users (author is a user)
CREATE TABLE books (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  isbn VARCHAR(20) NULL,
  theme_id INT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL COMMENT 'User who is the author',
  publisher_id INT UNSIGNED NULL,
  published_date DATE NULL COMMENT 'When the book was published',
  cover_url VARCHAR(255) NULL,
  file_url VARCHAR(255) NULL,
  is_free TINYINT(1) NOT NULL DEFAULT 1,
  is_downloadable TINYINT(1) NOT NULL DEFAULT 1,
  view_in_web TINYINT(1) NOT NULL DEFAULT 1,
  view_in_app TINYINT(1) NOT NULL DEFAULT 1,
  access_duration_days INT UNSIGNED NULL COMMENT 'For paid: NULL=lifetime, else days until access expires',
  view_count INT UNSIGNED NOT NULL DEFAULT 0,
  download_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE RESTRICT,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL,
  INDEX idx_theme (theme_id),
  INDEX idx_author (author_id),
  INDEX idx_published (published_date),
  FULLTEXT idx_search (title, description)
) ENGINE=InnoDB;

-- Paid book access: when a user gets access to a paid book (expires_at NULL = lifetime)
CREATE TABLE user_book_access (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  book_id INT UNSIGNED NOT NULL,
  accessed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NULL COMMENT 'NULL = lifetime access',
  UNIQUE KEY uk_user_book (user_id, book_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_book_expires (book_id, expires_at)
) ENGINE=InnoDB;

-- Favorites (user <-> book)
CREATE TABLE favorites (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  book_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_user_book (user_id, book_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_book (book_id)
) ENGINE=InnoDB;

-- Reviews and ratings
CREATE TABLE reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_user_book_review (user_id, book_id),
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_book (book_id),
  INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Reading history (view tracking)
CREATE TABLE reading_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  book_id INT UNSIGNED NOT NULL,
  viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_book (book_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Download history
CREATE TABLE download_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  book_id INT UNSIGNED NOT NULL,
  downloaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_book (book_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Site settings (admin-editable: site name, logo, app icon)
CREATE TABLE settings (
  `key` VARCHAR(80) NOT NULL PRIMARY KEY,
  `value` TEXT NULL,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO settings (`key`, `value`) VALUES
('site_name', 'Library'),
('site_tagline', 'Read, download & discover books'),
('logo_file', NULL),
('app_icon_file', NULL),
('favicon_file', NULL);

SET FOREIGN_KEY_CHECKS = 1;

-- Seed default admin and theme
INSERT INTO users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@library.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');
-- Password: password

INSERT INTO themes (name, slug, description, sort_order) VALUES
('Fiction', 'fiction', 'Novels and fiction', 1),
('Science', 'science', 'Science and technology', 2),
('History', 'history', 'Historical books', 3),
('Biography', 'biography', 'Biographies and memoirs', 4),
('Education', 'education', 'Educational and textbooks', 5);
