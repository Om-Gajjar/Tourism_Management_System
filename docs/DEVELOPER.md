# Tourism Management System - Developer Documentation

## Overview

This document provides technical details for developers working on or extending the Tourism Management System.

## Architecture

### Design Patterns Used

1. **Singleton Pattern** - Database connection (`getDbConnection()`)
2. **Front Controller (Partial)** - Each page handles its own logic but shares common includes
3. **Template Pattern** - Shared header/footer includes for consistent layout
4. **Helper Functions** - Centralized utility functions in `src/helpers/helpers.php`

### Request Flow

```
User Request (→ http://localhost:8000/…  ; docroot = public/)
    ↓
Public page (e.g., public/index.php)  or  Admin page (public/admin/…)
    ↓
require_once '../src/helpers/helpers.php'        (public page)
require_once '../../src/helpers/helpers.php'     (admin page)
    ↓
initSession() → Secure session config
    ↓
Page Logic (auth checks, DB queries, form handling)
    ↓
include '../src/views/public/header.php'  → HTML head + navbar
    ↓
Page Content (HTML + PHP templating)
    ↓
include '../src/views/public/footer.php'  → Footer + scripts
```

## Core Functions Reference

### Database

```php
// Get PDO instance (singleton)
$pdo = getDbConnection();

// Prepared statement example
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(); // Returns associative array
```

### Session Management

```php
// Initialize secure session (called automatically in src/helpers/helpers.php)
initSession();

// Session config applied:
// - cookie_httponly: true
// - cookie_secure: false (set true for HTTPS)
// - use_strict_mode: true
// - cookie_samesite: 'Lax'
// - lifetime: 7200 seconds (2 hours)
```

### Authentication

```php
// Check if user logged in
if (isLoggedIn()) { ... }

// Get current user ID
$userId = getCurrentUserId();

// Get current user name
$userName = getCurrentUserName();

// Protect page - redirects to login if not authenticated
requireAuth();

// Admin authentication
if (isAdminLoggedIn()) { ... }
requireAdminAuth(); // Redirects to admin/login.php
```

### CSRF Protection

```php
// In form:
<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">

// On form submission:
if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    $error = "Invalid request. Please try again.";
}
```

### Input/Output Safety

```php
// Output escaping (ALWAYS use for user data in HTML)
echo e($userInput); // htmlspecialchars($input, ENT_QUOTES, 'UTF-8')

// Input sanitization (for database/storage)
$clean = sanitizeInput($userInput); // trim + strip_tags

// Email validation
if (validateEmail($email)) { ... }
```

### Flash Messages

```php
// Set flash message and redirect
redirectWithMessage('index.php', 'Success message!', 'success');
// URL becomes: index.php?msg=Success+message!&type=success

// Display in template (place where you want message to appear)
<?php displayFlashMessage(); ?>
```

### Utility Functions

```php
// Format timestamp
echo formatDate('2024-01-15 14:30:00'); // "Jan 15, 2024 02:30 PM"

// Status badge CSS class
echo getStatusBadgeClass('confirmed'); // "bg-success"
echo getStatusBadgeClass('pending');   // "bg-warning text-dark"
echo getStatusBadgeClass('cancelled'); // "bg-danger"

// Status display label
echo getStatusLabel('confirmed'); // "Confirmed"
```

## Adding New Features

### 1. New Public Page

Create `public/new_page.php`:
```php
<?php
require_once '../src/helpers/helpers.php';
initSession();
// requireAuth(); // Uncomment if login required

// Page logic here
$pdo = getDbConnection();
$data = $pdo->query("SELECT * FROM table")->fetchAll();

include '../src/views/public/header.php';
?>

<!-- Page content -->
<div class="container my-5">
    <h1>New Page</h1>
    <!-- Content -->
</div>

<?php include '../src/views/public/footer.php'; ?>
```

### 2. New Admin Page

Create `public/admin/new_admin.php`:
```php
<?php
require_once '../../src/helpers/helpers.php';
initSession();
requireAdminAuth();

// Admin logic

include '../../src/views/admin/header.php';
?>

<div class="container my-4">
    <h2>New Admin Page</h2>
    <!-- Admin content -->
</div>

<?php include '../../src/views/admin/footer.php'; ?>
```

### 3. New Database Table

1. Add to `database/schema.sql`
2. Run migration: `sudo mysql tourism_db < database/schema.sql`
3. Use in code via `getDbConnection()`

### 4. New Configuration Option

Add to `src/config/config.php`:
```php
define('NEW_OPTION', 'value');
```

Access anywhere: `NEW_OPTION`

## Code Style Guidelines

### PHP
- Use procedural style (no classes)
- Functions in `src/helpers/helpers.php` use type hints and return types
- All output escaped with `e()`
- All SQL uses prepared statements
- All forms include CSRF token
- Indent: 4 spaces
- Opening brace on same line

### HTML/CSS
- Bootstrap 5 utility classes preferred
- Custom CSS in `public/css/style.css` using CSS variables
- Semantic HTML5 elements
- Mobile-first responsive design

### Security Checklist for New Code
- [ ] All SQL uses prepared statements
- [ ] All user output wrapped in `e()`
- [ ] All user input sanitized with `sanitizeInput()`
- [ ] Forms include CSRF token
- [ ] Auth checks (`requireAuth()` / `requireAdminAuth()`) on protected pages
- [ ] Passwords never logged or displayed
- [ ] File uploads validated (type, size, extension)

## Database Patterns

### Common Queries

```php
// Count
$count = $pdo->query("SELECT COUNT(*) FROM table")->fetchColumn();

// Single row
$row = $pdo->prepare("SELECT * FROM table WHERE id = ?");
$row->execute([$id]);
$data = $row->fetch();

// Multiple rows
$rows = $pdo->query("SELECT * FROM table ORDER BY id DESC")->fetchAll();

// Insert with returning ID
$stmt = $pdo->prepare("INSERT INTO table (col1, col2) VALUES (?, ?)");
$stmt->execute([$val1, $val2]);
$newId = $pdo->lastInsertId();

// Update
$stmt = $pdo->prepare("UPDATE table SET col1 = ? WHERE id = ?");
$stmt->execute([$newVal, $id]);

// Delete
$stmt = $pdo->prepare("DELETE FROM table WHERE id = ?");
$stmt->execute([$id]);
```

### Transactions

```php
$pdo = getDbConnection();
try {
    $pdo->beginTransaction();
    
    $stmt1 = $pdo->prepare("INSERT INTO bookings ...");
    $stmt1->execute([...]);
    
    $stmt2 = $pdo->prepare("UPDATE packages SET spots = spots - 1 WHERE id = ?");
    $stmt2->execute([$packageId]);
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

## Error Handling

```php
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("...");
    $stmt->execute([...]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Duplicate entry
        $error = "Email already exists.";
    } else {
        error_log("Database error: " . $e->getMessage());
        $error = "Operation failed. Please try again.";
    }
}
```

## Extending the Design System

### Adding New Colors

In `public/css/style.css`:
```css
:root {
    --new-color: #hexvalue;
}
```

### Adding New Components

Follow existing pattern:
```css
/* Component name */
.component-name {
    /* styles using CSS variables */
}

.component-name:hover {
    /* hover state */
}
```

### Responsive Breakpoints

Uses Bootstrap 5 breakpoints:
- `sm`: ≥576px
- `md`: ≥768px
- `lg`: ≥992px
- `xl`: ≥1200px
- `xxl`: ≥1400px

## Testing Guidelines

### Manual Testing
1. Test all user flows (register, login, book, cancel, profile)
2. Test all admin flows (login, dashboard, CRUD operations)
3. Test error states (invalid login, duplicate email, etc.)
4. Test responsive design on mobile/tablet/desktop
5. Test with browser dev tools (console, network)

### Security Testing
1. Attempt SQL injection in all inputs
2. Attempt XSS in all outputs
3. Test CSRF by submitting form without token
4. Test auth bypass by accessing protected pages directly
5. Test session fixation (login, check session ID change)

## Performance Considerations

- Database connection reused via singleton
- Prepared statements cached by PDO
- Minimal PHP processing per request
- CSS/JS via CDN (cached by browser)
- Images from external CDN (Unsplash)

## Debugging

Enable debug mode in `src/config/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

Check logs:
- PHP: `/var/log/apache2/error.log` or `php_errors.log`
- MySQL: `/var/log/mysql/error.log`
- PHP Built-in Server: `logs/php_server.log` (when using start.sh)
- Browser: DevTools Console/Network tabs

## Starting the Server

### Using the Start Script (Recommended)

The project includes `start.sh` - a helper script that manages MariaDB, Apache (phpMyAdmin), and PHP server:

```bash
# Make executable (first time only)
chmod +x start.sh

# Start ALL services
./start.sh start

# Check status
./start.sh status

# Stop all services
./start.sh stop

# Restart services
./start.sh restart
```

**What it does:**
1. Starts MariaDB (database) - port 3306
2. Starts Apache (for phpMyAdmin) - http://localhost/phpmyadmin
3. Starts PHP built-in server (your website) - http://localhost:8000
4. Creates `logs/` directory for server logs
5. Logs PHP server output to `logs/php_server.log`

**Service URLs:**
| Service | URL |
|---------|-----|
| Website | http://localhost:8000 |
| phpMyAdmin | http://localhost/phpmyadmin |
| Database | localhost:3306 |

**Manual start (alternative):**
```bash
# Start all services manually
sudo service mysql start
sudo service apache2 start
php -S localhost:8000
```

### Server Logs

When using `start.sh`, PHP server logs are saved to:
- `logs/php_server.log` - All PHP server output (requests, errors)

View live logs:
```bash
tail -f logs/php_server.log
```

## Common Issues

| Issue | Solution |
|-------|----------|
| "Database connection failed" | Check `src/config/config.php` DB credentials |
| "Access denied for user" | Verify MySQL user permissions |
| CSRF "Invalid request" | Ensure session started, token matches |
| Styles not loading | Check `public/css/style.css` path in header |
| Redirect loops | Check `requireAuth()` logic, session config |

## Version History

| Version | Date | Key Changes |
|---------|------|-------------|
| 1.2.0 | 2026-09-21 | Database modernization: users phone, bookings travel_date/total_price/payment_status, reviews table, rating system, performance indexes |
| 1.1.1 | 2026-09-20 | Enquiry detail drawer: slide-out panel with full message, mark read/unread, reply via email |
| 1.1.0 | 2026-09-12 | Foldering restructure: `public/` web root + private `src/` layer; docs updated; E2E test suite added |
| 1.0.0 | 2024-09-12 | Initial release with all core features |

## Future Enhancements

- [ ] Email verification for registration
- [ ] Password reset functionality
- [ ] Image upload for packages
- [ ] Payment integration
- [ ] Multi-language support
- [ ] API endpoints for mobile app
- [ ] Unit tests with PHPUnit
- [ ] Docker configuration