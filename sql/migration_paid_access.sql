-- Paid course access: expiry and lifetime. Run if you already have the database.
USE bookslibrary;

-- Add column (ignore error if it already exists)
ALTER TABLE books ADD COLUMN access_duration_days INT UNSIGNED NULL COMMENT 'For paid: NULL=lifetime, else days' AFTER view_in_app;

CREATE TABLE IF NOT EXISTS user_book_access (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  book_id INT UNSIGNED NOT NULL,
  accessed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NULL,
  UNIQUE KEY uk_user_book (user_id, book_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_book_expires (book_id, expires_at)
) ENGINE=InnoDB;
