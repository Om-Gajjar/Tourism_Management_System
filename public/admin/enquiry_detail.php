<?php
require_once __DIR__ . '/../../src/helpers/helpers.php';
initSession();
requireAdminAuth();

header('Content-Type: text/html; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo '<div class="alert alert-danger">Invalid enquiry ID.</div>';
    exit;
}

$pdo = getDbConnection();
$stmt = $pdo->prepare("
    SELECT 
        e.*, 
        p.name AS package_name, 
        p.destination AS package_destination,
        p.price AS package_price,
        p.image AS package_image,
        u.name AS user_account_name,
        u.created_at AS user_joined_date
    FROM enquiries e 
    LEFT JOIN packages p ON e.package_id = p.id 
    LEFT JOIN users u ON e.user_id = u.id 
    WHERE e.id = ?
");
$stmt->execute([$id]);
$eq = $stmt->fetch();

if (!$eq) {
    echo '<div class="alert alert-danger">Enquiry not found.</div>';
    exit;
}
?>

<!-- Customer / Sender Profile Card -->
<div class="p-3 bg-light rounded-3 mb-4">
    <div class="d-flex align-items-center mb-3">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; font-size: 1.1rem; font-weight: bold;">
            <?= strtoupper(substr($eq['name'], 0, 1)) ?>
        </div>
        <div>
            <h6 class="fw-bold mb-0 text-dark"><?= e($eq['name']) ?></h6>
            <?php if ($eq['user_account_name']): ?>
                <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.7rem;"><i class="bi bi-shield-check me-1"></i>Registered Member</span>
            <?php else: ?>
                <span class="badge bg-secondary bg-opacity-10 text-muted" style="font-size: 0.7rem;">Guest Inquirer</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex flex-column gap-2 small text-muted">
        <div>
            <i class="bi bi-envelope text-primary me-2"></i>
            <a href="mailto:<?= e($eq['email']) ?>" class="text-decoration-none text-dark fw-medium"><?= e($eq['email']) ?></a>
        </div>
        <?php if (!empty($eq['phone'])): ?>
            <div>
                <i class="bi bi-telephone text-success me-2"></i>
                <a href="tel:<?= e($eq['phone']) ?>" class="text-decoration-none text-dark fw-medium"><?= e($eq['phone']) ?></a>
            </div>
        <?php endif; ?>
        <div>
            <i class="bi bi-calendar-event text-info me-2"></i>
            Received on <?= formatDate($eq['created_at'], 'M d, Y \a\t g:i A') ?>
        </div>
        <div>
            <i class="bi bi-info-circle text-warning me-2"></i>
            Current Status: <span class="badge <?= getStatusBadgeClass($eq['status']) ?>"><?= getStatusLabel($eq['status']) ?></span>
        </div>
    </div>
</div>

<!-- Linked Tour Package (if any) -->
<?php if (!empty($eq['package_name'])): ?>
    <div class="card border border-primary border-opacity-25 mb-4 shadow-sm bg-primary bg-opacity-10">
        <div class="card-body p-3">
            <small class="text-uppercase fw-bold text-primary d-block mb-1">Inquiring About Tour Package</small>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong class="text-dark"><?= e($eq['package_name']) ?></strong>
                    <div class="text-muted small"><i class="bi bi-geo-alt me-1 text-primary"></i><?= e($eq['package_destination']) ?></div>
                </div>
                <div class="text-end">
                    <span class="fw-bold text-primary"><?= formatPrice($eq['package_price']) ?></span>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Subject -->
<div class="mb-3">
    <label class="text-muted small fw-bold text-uppercase">Subject</label>
    <h5 class="fw-bold text-dark mb-0"><?= e($eq['subject']) ?></h5>
</div>

<!-- Message Body -->
<div class="mb-4">
    <label class="text-muted small fw-bold text-uppercase">Customer Message</label>
    <div class="p-3 bg-white border rounded-3 text-dark" style="white-space: pre-wrap; line-height: 1.7; font-size: 0.95rem;">
        <?= e($eq['message']) ?>
    </div>
</div>

<!-- Quick Status Actions -->
<div class="mb-4">
    <label class="text-muted small fw-bold text-uppercase mb-2 d-block">Update Status</label>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm <?= $eq['status'] === 'read' ? 'btn-secondary' : 'btn-outline-secondary' ?> rounded-pill px-3" onclick="updateEnquiryStatus(<?= $eq['id'] ?>, 'read')">
            <i class="bi bi-envelope-open me-1"></i>Mark Read
        </button>
        <button type="button" class="btn btn-sm <?= $eq['status'] === 'unread' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?> rounded-pill px-3" onclick="updateEnquiryStatus(<?= $eq['id'] ?>, 'unread')">
            <i class="bi bi-envelope me-1"></i>Mark Unread
        </button>
        <button type="button" class="btn btn-sm <?= $eq['status'] === 'replied' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill px-3" onclick="updateEnquiryStatus(<?= $eq['id'] ?>, 'replied')">
            <i class="bi bi-reply-fill me-1"></i>Mark Replied
        </button>
    </div>
</div>

<!-- Primary Action Button -->
<div class="d-grid">
    <a href="mailto:<?= e($eq['email']) ?>?subject=Re: <?= urlencode($eq['subject']) ?>" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm" onclick="updateEnquiryStatus(<?= $eq['id'] ?>, 'replied')">
        <i class="bi bi-reply-fill me-2"></i>Reply via Email
    </a>
</div>
