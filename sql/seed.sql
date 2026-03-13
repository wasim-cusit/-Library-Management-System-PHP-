-- Sample data for Library (run after schema.sql)
-- Login credentials are set here and shown after setup

USE bookslibrary;

-- Clear existing seed users if re-running (keep admin from schema)
DELETE FROM reviews WHERE user_id > 1;
DELETE FROM favorites WHERE user_id > 1;
DELETE FROM reading_history WHERE user_id > 1;
DELETE FROM download_history WHERE user_id > 1;
DELETE FROM user_book_access WHERE user_id > 1;
DELETE FROM books WHERE author_id > 1;
DELETE FROM users WHERE id > 1;

-- Sample users (passwords are set by setup.php after run)
-- Author: author@library.local / Author@123
-- Reader: user@library.local / User@123

INSERT INTO users (username, email, password_hash, full_name, role, bio) VALUES
('sarah_writer', 'author@library.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Chen', 'author', 'Fiction and non-fiction author.'),
('john_reader', 'user@library.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Smith', 'user', NULL);
-- Default password for both: password (setup will replace with proper hashes)

-- Publishers
INSERT INTO publishers (name, slug) VALUES
('Penguin Random House', 'penguin-random-house'),
('HarperCollins', 'harpercollins'),
('Oxford University Press', 'oxford-university-press'),
('Tech Publications Inc', 'tech-publications-inc');

-- Books (author_id 2 = sarah_writer); no file_url so "Read" will show fallback until you upload files
INSERT INTO books (title, description, isbn, theme_id, author_id, publisher_id, published_date, is_free, is_downloadable, view_in_web, view_in_app, access_duration_days, view_count, download_count) VALUES
('The Art of Clear Thinking', 'A practical guide to making better decisions and avoiding cognitive biases in everyday life.', '978-0-14-313434-1', 2, 2, 1, '2022-03-15', 1, 1, 1, 1, NULL, 124, 28),
('Stories from the Silk Road', 'A collection of tales from the ancient trade routes connecting East and West.', '978-0-06-289056-2', 1, 2, 2, '2021-08-20', 1, 1, 1, 1, NULL, 89, 15),
('Introduction to Web Development', 'Learn HTML, CSS, and JavaScript from scratch. Perfect for beginners.', '978-0-19-884462-3', 5, 2, 3, '2023-01-10', 1, 1, 1, 1, NULL, 256, 67),
('Digital Marketing Essentials', 'Paid course: strategies and tools for modern digital marketing.', NULL, 5, 2, 4, '2023-06-01', 0, 1, 1, 1, 365, 45, 12),
('The Last Empire: A History', 'An accessible history of the final century of imperial rule.', '978-0-14-312989-7', 3, 2, 1, '2020-11-12', 1, 1, 1, 1, NULL, 178, 34),
('Morning Habits of Successful People', 'Short guide to building a productive morning routine.', NULL, 4, 2, 2, '2022-09-05', 0, 1, 1, 1, 90, 312, 89);

-- Reviews (admin and john_reader)
INSERT INTO reviews (book_id, user_id, rating, comment) VALUES
(1, 1, 5, 'Very useful and well written.'),
(1, 3, 4, 'Helped me improve my decision making.'),
(2, 3, 5, 'Loved the stories and the historical context.'),
(3, 1, 4, 'Good for beginners.');

-- Favorites (john_reader)
INSERT INTO favorites (user_id, book_id) VALUES (3, 1), (3, 2), (3, 5);
