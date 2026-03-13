<?php
/**
 * Bootstrap: session, config, autoload
 */
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/app.php';

session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/auth.php';
require_once BASE_PATH . '/includes/settings.php';
require_once BASE_PATH . '/includes/access.php';
require_once BASE_PATH . '/includes/book_formats.php';
