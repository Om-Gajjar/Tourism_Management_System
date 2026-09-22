<?php
/**
 * Tourism Management System - Helper Functions
 * Common utility functions used across the application
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Get a PDO database connection instance
 * @return PDO
 */
function getDbConnection(): PDO
{
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            if (defined('DEBUG') && DEBUG) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            } else {
                // In production, log error and show generic message
                error_log("Database connection failed: " . $e->getMessage());
                die("Database connection failed. Please try again later.");
            }
        }
    }
    
    return $pdo;
}

/**
 * Start session with secure configuration
 */
function initSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Secure session settings
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '0'); // Set to 1 if using HTTPS
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Lax');
        session_set_cookie_params(SESSION_LIFETIME);
        session_start();
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCsrfToken(string $token): bool
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitize output for HTML context
 * @param string $string
 * @return string
 */
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user name
 * @return string
 */
function getCurrentUserName(): string
{
    return $_SESSION['user_name'] ?? 'Guest';
}

/**
 * Check if admin is logged in
 * @return bool
 */
function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
}

/**
 * Require user authentication
 */
function requireAuth(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit();
    }
}

/**
 * Require admin authentication
 */
function requireAdminAuth(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . APP_URL . '/admin/login.php');
        exit();
    }
}

/**
 * Redirect with message
 * @param string $url
 * @param string $message
 * @param string $type (success, error, info)
 */
function redirectWithMessage(string $url, string $message, string $type = 'success'): void
{
    $separator = parse_url($url, PHP_URL_QUERY) ? '&' : '?';
    header("Location: {$url}{$separator}msg=" . urlencode($message) . "&type=" . $type);
    exit();
}

/**
 * Get flash message from URL
 * @return array ['message' => '', 'type' => '']
 */
function getFlashMessage(): array
{
    $message = $_GET['msg'] ?? '';
    $type = $_GET['type'] ?? 'success';
    return ['message' => $message, 'type' => $type];
}

/**
 * Display flash message
 */
function displayFlashMessage(): void
{
    $flash = getFlashMessage();
    if (!empty($flash['message'])) {
        $alertClass = match($flash['type']) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'info' => 'alert-info',
            default => 'alert-primary'
        };
        echo "<div class=\"alert {$alertClass} alert-dismissible fade show\" role=\"alert\">";
        echo e($flash['message']);
        echo "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>";
        echo "</div>";
    }
}

/**
 * Validate email
 * @param string $email
 * @return bool
 */
function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize string input
 * @param string $input
 * @return string
 */
function sanitizeInput(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Format price
 * @param float $price
 * @return string
 */
function formatPrice(float $price): string
{
    return '$' . number_format($price, 2);
}

/**
 * Format date
 * @param string $dateString
 * @param string $format
 * @return string
 */
function formatDate(string $dateString, string $format = 'M d, Y h:i A'): string
{
    return date($format, strtotime($dateString));
}

/**
 * Get status badge class
 * @param string $status
 * @return string
 */
function getStatusBadgeClass(string $status): string
{
    return match($status) {
        'confirmed', 'paid' => 'bg-confirmed',
        'cancelled', 'refunded' => 'bg-cancelled',
        'pending' => 'bg-pending',
        'read' => 'bg-secondary',
        'unread' => 'bg-warning text-dark',
        default => 'bg-secondary'
    };
}

/**
 * Get status badge label
 * @param string $status
 * @return string
 */
function getStatusLabel(string $status): string
{
    return match($status) {
        'paid' => 'Paid',
        'refunded' => 'Refunded',
        default => ucfirst(strtolower($status))
    };
}

/**
 * Render star rating HTML
 * @param int|float $rating
 * @return string
 */
function renderStars(int|float $rating): string
{
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - $fullStars - $halfStar;
    
    $html = '<span class="text-warning">';
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="bi bi-star-fill"></i>';
    }
    if ($halfStar) {
        $html .= '<i class="bi bi-star-half"></i>';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="bi bi-star"></i>';
    }
    $html .= '</span>';
    return $html;
}