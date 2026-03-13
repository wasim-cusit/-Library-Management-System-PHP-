-- Run this if you already have the database and need to add the settings table
USE bookslibrary;

CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(80) NOT NULL PRIMARY KEY,
  `value` TEXT NULL,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO settings (`key`, `value`) VALUES
('site_name', 'Library'),
('site_tagline', 'Read, download & discover books'),
('logo_file', NULL),
('app_icon_file', NULL),
('favicon_file', NULL);
