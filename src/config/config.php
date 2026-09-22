<?php
/**
 * Tourism Management System - Configuration
 * Centralized configuration for the application
 */

// Application Settings
define('APP_NAME', 'Tourism MS');
define('APP_VERSION', '1.2.0');
define('APP_URL', 'http://localhost:8000');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'tourism_db');
define('DB_USER', 'tourism_user');
define('DB_PASS', 'password');
define('DB_CHARSET', 'utf8mb4');

// Security Settings
define('SESSION_LIFETIME', 7200); // 2 hours
define('PASSWORD_MIN_LENGTH', 6);
define('CSRF_TOKEN_NAME', 'csrf_token');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Admin Credentials (in production, use database)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Should be hashed in production

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');