<?php
require_once __DIR__ . '/../../src/helpers/helpers.php';
initSession();
requireAdminAuth();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    // Verify CSRF token if coming from form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            redirectWithMessage('index.php', 'Invalid request.', 'error');
        }
    }
    
    $pdo = getDbConnection();
    // Delete bookings associated with package first
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE package_id = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->execute([$id]);
}

redirectWithMessage('index.php', 'Package deleted successfully!', 'success');