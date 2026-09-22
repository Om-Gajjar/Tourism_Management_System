<?php
/**
 * Admin Header - Tourism Management System
 */

require_once __DIR__ . '/../../helpers/helpers.php';
initSession();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
    
    <title>Admin - <?= APP_NAME ?></title>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-shield-lock me-1"></i>Admin Panel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'fw-bold' : '' ?>" href="index.php">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'enquiries.php' ? 'fw-bold' : '' ?>" href="enquiries.php">
                        <i class="bi bi-chat-dots me-1"></i>Enquiries
                        <?php 
                        $pdo = getDbConnection();
                        $unread = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'unread'")->fetchColumn();
                        if ($unread > 0): ?>
                            <span class="badge bg-warning text-dark ms-1"><?= $unread ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item me-lg-2">
                    <a class="nav-link" href="../index.php" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i>View Site
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm" href="../logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>