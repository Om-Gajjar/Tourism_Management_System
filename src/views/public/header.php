<?php
/**
 * Public Header - Tourism Management System
 * Includes all common head elements, CSS, and navigation
 */

require_once __DIR__ . '/../../helpers/helpers.php';
initSession();

$csrfToken = generateCsrfToken();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= APP_NAME ?> - Explore and book amazing tour packages">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <title><?= APP_NAME ?> - <?= ucfirst(str_replace('.php', '', $currentPage)) ?></title>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php">
            <i class="bi bi-globe2 me-1"></i><?= APP_NAME ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'fw-bold text-primary' : '' ?>" href="index.php">
                        <i class="bi bi-house-door me-1"></i>Packages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'contact.php' ? 'fw-bold text-primary' : '' ?>" href="contact.php">
                        <i class="bi bi-envelope me-1"></i>Contact
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="my_bookings.php">
                            <i class="bi bi-suitcase me-1"></i>My Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person-circle me-1"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-danger btn-sm" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3" href="register.php">
                            <i class="bi bi-person-plus me-1"></i>Sign Up
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item ms-lg-3 border-start ps-lg-3">
                    <a class="nav-link text-warning fw-semibold" href="../admin/login.php">
                        <i class="bi bi-shield-lock me-1"></i>Admin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- CSRF Token for AJAX forms -->
<input type="hidden" id="csrf-token" value="<?= e($csrfToken) ?>">