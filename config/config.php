<?php
/**
 * Application Configuration
 * Site-wide settings and constants
 */

// Site settings
define('SITE_NAME', 'LocalTechFix');
define('SITE_URL', 'http://localhost/Proj2');
define('BASE_PATH', dirname(__DIR__));

// Directory paths
define('UPLOAD_DIR', BASE_PATH . '/public/assets/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'log']);

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
session_start();

// Timezone
date_default_timezone_set('Europe/Budapest');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CSRF Token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
