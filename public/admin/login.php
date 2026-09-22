<?php
require_once __DIR__ . '/../../src/helpers/helpers.php';
initSession();

// Rate limiting: track failed login attempts
define('ADMIN_MAX_LOGIN_ATTEMPTS', 5);
define('ADMIN_LOGIN_LOCKOUT_TIME', 900); // 15 minutes

function getLoginAttempts(): int {
    return $_SESSION['admin_login_attempts'] ?? 0;
}

function incrementLoginAttempts(): void {
    $_SESSION['admin_login_attempts'] = ($_SESSION['admin_login_attempts'] ?? 0) + 1;
    $_SESSION['admin_last_login_attempt'] = time();
}

function resetLoginAttempts(): void {
    unset($_SESSION['admin_login_attempts']);
    unset($_SESSION['admin_last_login_attempt']);
}

function isLockedOut(): bool {
    $attempts = getLoginAttempts();
    if ($attempts >= ADMIN_MAX_LOGIN_ATTEMPTS) {
        $lastAttempt = $_SESSION['admin_last_login_attempt'] ?? 0;
        if (time() - $lastAttempt < ADMIN_LOGIN_LOCKOUT_TIME) {
            return true;
        }
        resetLoginAttempts();
    }
    return false;
}

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

// Check lockout
if (isLockedOut()) {
    $remaining = ADMIN_LOGIN_LOCKOUT_TIME - (time() - ($_SESSION['admin_last_login_attempt'] ?? 0));
    $error = "Too many failed attempts. Please wait " . ceil($remaining / 60) . " minutes.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = "Invalid request. Please try again.";
        incrementLoginAttempts();
    } else {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];

        // Pre-compute admin password hash once per request
        static $adminPasswordHash = null;
        if ($adminPasswordHash === null) {
            $adminPasswordHash = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
        }
        
        if ($username === ADMIN_USERNAME && password_verify($password, $adminPasswordHash)) {
            $_SESSION['admin_logged'] = true;
            session_regenerate_id(true);
            resetLoginAttempts();
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid admin credentials!";
            incrementLoginAttempts();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-5">

<div class="container" style="max-width: 450px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-shield-lock-fill text-primary" style="font-size: 1.5rem;"></i>
                </div>
                <h1 class="fw-bold text-primary mb-1">Admin Portal</h1>
                <p class="text-muted">Sign in to manage the Tourism MS</p>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= e($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-medium">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Sign In</button>
            </form>

            <p class="mt-4 text-center text-muted small">
                <a href="../index.php" class="fw-medium"><i class="bi bi-arrow-left me-1"></i>Back to Website</a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>