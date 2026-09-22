<?php
require_once __DIR__ . '/../../src/helpers/helpers.php';
initSession();
requireAdminAuth();

$id = (int)($_GET['id'] ?? 0);
$status = sanitizeInput($_GET['status'] ?? '');
$payment_status = sanitizeInput($_GET['payment_status'] ?? '');

$allowed_statuses = ['pending', 'confirmed', 'cancelled'];
$allowed_payment = ['pending', 'paid', 'refunded'];

if ($id) {
    $pdo = getDbConnection();
    
    if ($status && in_array($status, $allowed_statuses)) {
        // If confirming booking, also auto-mark payment as paid if still pending
        if ($status === 'confirmed') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ?, payment_status = IF(payment_status = 'pending', 'paid', payment_status) WHERE id = ?");
            $stmt->execute([$status, $id]);
        } elseif ($status === 'cancelled') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ?, payment_status = IF(payment_status = 'paid', 'refunded', payment_status) WHERE id = ?");
            $stmt->execute([$status, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        }
        redirectWithMessage('index.php', "Booking #$id status set to " . ucfirst($status), 'success');
    }

    if ($payment_status && in_array($payment_status, $allowed_payment)) {
        $stmt = $pdo->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
        $stmt->execute([$payment_status, $id]);
        redirectWithMessage('index.php', "Booking #$id payment status set to " . ucfirst($payment_status), 'success');
    }
}

redirectWithMessage('index.php', 'Invalid update request', 'error');
