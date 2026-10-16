<?php
/**
 * Tourism Management System - Configuration
 * Centralized configuration for the application
 */

// Application Settings
function configValue(string $key, mixed $default = null): mixed
{
    $environmentValue = getenv($key);
    if ($environmentValue !== false && $environmentValue !== '') {
        return $environmentValue;
    }

    static $localConfig = null;
    if ($localConfig === null) {
        $localConfigFile = __DIR__ . '/config.local.php';
        $localConfig = file_exists($localConfigFile) ? require $localConfigFile : [];
        if (!is_array($localConfig)) {
            throw new RuntimeException('Local configuration must return an array.');
        }
    }

    return $localConfig[$key] ?? $default;
}

define('APP_NAME', configValue('APP_NAME', 'Tourism MS'));
define('APP_VERSION', '1.2.0');
$configuredAppUrl = trim((string) configValue('APP_URL', ''));
if ($configuredAppUrl === '' && isset($_SERVER['HTTP_HOST'])) {
    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    $scheme = $forwardedProto !== '' ? trim(explode(',', $forwardedProto)[0]) : 'http';
    $configuredAppUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
}
define('APP_URL', rtrim($configuredAppUrl ?: 'http://localhost:8000', '/'));

// Database Configuration
define('DB_HOST', configValue('DB_HOST', 'localhost'));
define('DB_PORT', configValue('DB_PORT', '3306'));
define('DB_NAME', configValue('DB_NAME', 'tourism_db'));
define('DB_USER', configValue('DB_USER', 'tourism_user'));
define('DB_PASS', configValue('DB_PASS', 'password'));
define('DB_CHARSET', 'utf8mb4');
define('DB_SSL_CA', configValue('DB_SSL_CA', ''));
define('DB_SSL_VERIFY', filter_var(configValue('DB_SSL_VERIFY', 'false'), FILTER_VALIDATE_BOOLEAN));

// Admin Credentials
define('ADMIN_USERNAME', configValue('ADMIN_USERNAME', 'admin'));
define('ADMIN_PASSWORD', configValue('ADMIN_PASSWORD', 'admin123'));

// Session & Security Settings
define('SESSION_LIFETIME', 7200); // 2 hours
define('PASSWORD_MIN_LENGTH', 8);
define('CSRF_TOKEN_NAME', 'csrf_token');

// Pagination
define('ITEMS_PER_PAGE', 10);

// File Uploads
define('UPLOAD_DIR', __DIR__ . '/../../public/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Logging
define('LOG_PATH', __DIR__ . '/../../logs/app.log');
