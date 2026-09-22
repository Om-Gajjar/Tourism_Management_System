<?php
require_once __DIR__ . '/../src/helpers/helpers.php';
initSession();

$success = '';
$error = '';

$pdo = getDbConnection();

// Check if inquiring about a specific package
$package_id = (int)($_GET['package_id'] ?? 0);
$linked_package = null;
if ($package_id > 0) {
    $stmt = $pdo->prepare("SELECT id, name, destination FROM packages WHERE id = ?");
    $stmt->execute([$package_id]);
    $linked_package = $stmt->fetch();
}

// Prefill user data if logged in
$prefill_name = '';
$prefill_email = '';
$prefill_phone = '';
$user_id = null;

if (isLoggedIn()) {
    $user_id = getCurrentUserId();
    $stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user) {
        $prefill_name = $user['name'];
        $prefill_email = $user['email'];
        $prefill_phone = $user['phone'] ?? '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');
        $post_pkg_id = (int)($_POST['package_id'] ?? 0);
        $final_package_id = $post_pkg_id > 0 ? $post_pkg_id : null;

        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error = "Name, email, subject, and message are required.";
        } elseif (!validateEmail($email)) {
            $error = "Please provide a valid email address.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO enquiries (user_id, package_id, name, email, phone, subject, message) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$user_id, $final_package_id, $name, $email, $phone ?: null, $subject, $message])) {
                redirectWithMessage('contact.php', 'Thank you! Your enquiry has been received. Our travel specialists will contact you shortly.', 'success');
            } else {
                $error = "An error occurred while submitting your enquiry. Please try again.";
            }
        }
    }
}

// Display flash message
$flash = getFlashMessage();
if (!empty($flash['message'])) {
    $success = $flash['message'];
}
?>
<?php include __DIR__ . '/../src/views/public/header.php'; ?>

<div class="container my-5" style="max-width: 760px;">
    <div class="card border-0 shadow-sm p-4 p-md-5">
        <h2 class="fw-bold mb-1">Contact & Inquiries</h2>
        <p class="text-muted mb-4">Have questions regarding a tour package, customized itineraries, or group bookings? Send us a message!</p>

        <?php if ($linked_package): ?>
            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Inquiring about:</strong> <?= e($linked_package['name']) ?> (<?= e($linked_package['destination']) ?>)
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= e($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
            <?php if ($linked_package): ?>
                <input type="hidden" name="package_id" value="<?= $linked_package['id'] ?>">
            <?php endif; ?>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= e($prefill_name) ?>" placeholder="e.g. John Doe" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= e($prefill_email) ?>" placeholder="e.g. john@example.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Phone Number <span class="text-muted">(Optional)</span></label>
                    <input type="tel" name="phone" class="form-control" value="<?= e($prefill_phone) ?>" placeholder="e.g. +1 (555) 000-0000">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control" value="<?= $linked_package ? 'Inquiry: ' . e($linked_package['name']) : '' ?>" placeholder="e.g. Booking inquiry / Group discount" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Message <span class="text-danger">*</span></label>
                    <textarea name="message" class="form-control" rows="5" placeholder="Tell us about your travel plans, dates, number of guests, or specific questions..." required></textarea>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">Send Message <i class="bi bi-send ms-1"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../src/views/public/footer.php'; ?>
