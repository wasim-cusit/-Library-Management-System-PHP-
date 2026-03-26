-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 06:30 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookslibrary`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `theme_id` int(10) UNSIGNED NOT NULL,
  `author_id` int(10) UNSIGNED NOT NULL COMMENT 'User who is the author',
  `publisher_id` int(10) UNSIGNED DEFAULT NULL,
  `published_date` date DEFAULT NULL COMMENT 'When the book was published',
  `cover_url` varchar(255) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `is_free` tinyint(1) NOT NULL DEFAULT 1,
  `is_downloadable` tinyint(1) NOT NULL DEFAULT 1,
  `view_in_web` tinyint(1) NOT NULL DEFAULT 1,
  `view_in_app` tinyint(1) NOT NULL DEFAULT 1,
  `access_duration_days` int(10) UNSIGNED DEFAULT NULL COMMENT 'For paid: NULL=lifetime, else days until access expires',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `download_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `description`, `isbn`, `theme_id`, `author_id`, `publisher_id`, `published_date`, `cover_url`, `file_url`, `is_free`, `is_downloadable`, `view_in_web`, `view_in_app`, `access_duration_days`, `view_count`, `download_count`, `created_at`, `updated_at`) VALUES
(1, 'The Art of Clear Thinking', 'A practical guide to making better decisions and avoiding cognitive biases in everyday life.', '978-0-14-313434-1', 2, 2, 1, '2022-03-15', NULL, NULL, 1, 1, 1, 1, NULL, 124, 28, '2026-03-13 14:13:31', NULL),
(2, 'Stories from the Silk Road', 'A collection of tales from the ancient trade routes connecting East and West.', '978-0-06-289056-2', 1, 2, 2, '2021-08-20', NULL, NULL, 1, 1, 1, 1, NULL, 89, 15, '2026-03-13 14:13:31', NULL),
(3, 'Introduction to Web Development', 'Learn HTML, CSS, and JavaScript from scratch. Perfect for beginners.', '978-0-19-884462-3', 5, 2, 3, '2023-01-10', NULL, NULL, 1, 1, 1, 1, NULL, 256, 67, '2026-03-13 14:13:31', NULL),
(4, 'Digital Marketing Essentials', 'Paid course: strategies and tools for modern digital marketing.', NULL, 5, 2, 4, '2023-06-01', NULL, NULL, 0, 1, 1, 1, 365, 45, 12, '2026-03-13 14:13:31', NULL),
(5, 'The Last Empire: A History', 'An accessible history of the final century of imperial rule.', '978-0-14-312989-7', 3, 2, 1, '2020-11-12', NULL, NULL, 1, 1, 1, 1, NULL, 178, 34, '2026-03-13 14:13:31', NULL),
(6, 'Morning Habits of Successful People', 'Short guide to building a productive morning routine.', NULL, 4, 2, 2, '2022-09-05', NULL, NULL, 0, 1, 1, 1, 90, 312, 89, '2026-03-13 14:13:31', NULL),
(7, 'Ut elit adipisci en', 'Voluptate nisi est', 'Optio libero praese', 1, 1, 1, '1991-07-30', 'c69c4bf16f0c0a.png', 'b69c4bf16f0e55.pdf', 1, 1, 1, 1, NULL, 3, 1, '2026-03-26 10:07:34', '2026-03-26 10:08:36');

-- --------------------------------------------------------

--
-- Table structure for table `download_history`
--

CREATE TABLE `download_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `book_id` int(10) UNSIGNED NOT NULL,
  `downloaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `download_history`
--

INSERT INTO `download_history` (`id`, `user_id`, `book_id`, `downloaded_at`) VALUES
(1, 1, 7, '2026-03-26 10:08:01');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `book_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `book_id`, `created_at`) VALUES
(1, 3, 1, '2026-03-13 14:13:31'),
(2, 3, 2, '2026-03-13 14:13:31'),
(3, 3, 5, '2026-03-13 14:13:31'),
(4, 1, 7, '2026-03-26 10:08:05');

-- --------------------------------------------------------

--
-- Table structure for table `publishers`
--

CREATE TABLE `publishers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `publishers`
--

INSERT INTO `publishers` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Penguin Random House', 'penguin-random-house', '2026-03-13 14:13:31'),
(2, 'HarperCollins', 'harpercollins', '2026-03-13 14:13:31'),
(3, 'Oxford University Press', 'oxford-university-press', '2026-03-13 14:13:31'),
(4, 'Tech Publications Inc', 'tech-publications-inc', '2026-03-13 14:13:31'),
(5, 'Et facere incididunt', 'et-facere-incididunt', '2026-03-26 10:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `reading_history`
--

CREATE TABLE `reading_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `book_id` int(10) UNSIGNED NOT NULL,
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reading_history`
--

INSERT INTO `reading_history` (`id`, `user_id`, `book_id`, `viewed_at`) VALUES
(1, 1, 7, '2026-03-26 10:07:48'),
(2, 1, 7, '2026-03-26 10:08:18'),
(3, 1, 7, '2026-03-26 10:08:36');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `book_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `book_id`, `user_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 5, 'Very useful and well written.', '2026-03-13 14:13:31', NULL),
(2, 1, 3, 4, 'Helped me improve my decision making.', '2026-03-13 14:13:31', NULL),
(3, 2, 3, 5, 'Loved the stories and the historical context.', '2026-03-13 14:13:31', NULL),
(4, 3, 1, 4, 'Good for beginners.', '2026-03-13 14:13:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(80) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`key`, `value`, `updated_at`) VALUES
('app_icon_file', NULL, NULL),
('favicon_file', NULL, NULL),
('logo_file', NULL, NULL),
('site_name', 'Library', NULL),
('site_tagline', 'Read, download & discover books', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `name`, `slug`, `description`, `sort_order`, `created_at`) VALUES
(1, 'Fiction', 'fiction', 'Novels and fiction', 1, '2026-03-13 14:13:31'),
(2, 'Science', 'science', 'Science and technology', 2, '2026-03-13 14:13:31'),
(3, 'History', 'history', 'Historical books', 3, '2026-03-13 14:13:31'),
(4, 'Biography', 'biography', 'Biographies and memoirs', 4, '2026-03-13 14:13:31'),
(5, 'Education', 'education', 'Educational and textbooks', 5, '2026-03-13 14:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(120) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('user','author','admin') NOT NULL DEFAULT 'user',
  `bio` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `avatar`, `role`, `bio`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@library.local', '$2y$10$RAfqOlS2zkipnFQ2wPJqoOzCUWcdTklBYDJWFvyeKddu07Jgg6OuK', 'Administrator', NULL, 'admin', NULL, '2026-03-13 14:13:31', '2026-03-13 14:13:31'),
(2, 'sarah_writer', 'author@library.local', '$2y$10$wsuxZw6XQEXiodczGmFg5.43NfgY.PvqSAbp5Pts6/pUnwg.pW8U2', 'Sarah Chen', NULL, 'author', 'Fiction and non-fiction author.', '2026-03-13 14:13:31', '2026-03-13 14:13:31'),
(3, 'john_reader', 'user@library.local', '$2y$10$G..BTwJegKlYiT0NLMJSne9JEumSS35j70X38/7C.6GYdN.tJZF1W', 'John Smith', NULL, 'user', NULL, '2026-03-13 14:13:31', '2026-03-13 14:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `user_book_access`
--

CREATE TABLE `user_book_access` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `book_id` int(10) UNSIGNED NOT NULL,
  `accessed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL COMMENT 'NULL = lifetime access'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `publisher_id` (`publisher_id`),
  ADD KEY `idx_theme` (`theme_id`),
  ADD KEY `idx_author` (`author_id`),
  ADD KEY `idx_published` (`published_date`);
ALTER TABLE `books` ADD FULLTEXT KEY `idx_search` (`title`,`description`);

--
-- Indexes for table `download_history`
--
ALTER TABLE `download_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_book` (`book_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_book` (`user_id`,`book_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_book` (`book_id`);

--
-- Indexes for table `publishers`
--
ALTER TABLE `publishers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_publisher_slug` (`slug`);

--
-- Indexes for table `reading_history`
--
ALTER TABLE `reading_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_book` (`book_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_book_review` (`user_id`,`book_id`),
  ADD KEY `idx_book` (`book_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `user_book_access`
--
ALTER TABLE `user_book_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_book` (`user_id`,`book_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_book_expires` (`book_id`,`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `download_history`
--
ALTER TABLE `download_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `publishers`
--
ALTER TABLE `publishers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reading_history`
--
ALTER TABLE `reading_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_book_access`
--
ALTER TABLE `user_book_access`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`),
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `books_ibfk_3` FOREIGN KEY (`publisher_id`) REFERENCES `publishers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `download_history`
--
ALTER TABLE `download_history`
  ADD CONSTRAINT `download_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `download_history_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reading_history`
--
ALTER TABLE `reading_history`
  ADD CONSTRAINT `reading_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reading_history_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_book_access`
--
ALTER TABLE `user_book_access`
  ADD CONSTRAINT `user_book_access_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_book_access_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
