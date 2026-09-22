<?php
require_once __DIR__ . '/../src/helpers/helpers.php';
initSession();
requireAuth();

$user_id = getCurrentUserId();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $new_password = $_POST['new_password'] ?? '';

        if (empty($name)) {
            $error = "Name cannot be empty.";
        } else {
            $pdo = getDbConnection();
            if (!empty($new_password)) {
                if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
                    $error = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters.";
                } else {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
                    $stmt->execute([$name, $phone ?: null, $hashed, $user_id]);
                }
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
                $stmt->execute([$name, $phone ?: null, $user_id]);
            }
            
            if (empty($error)) {
                $_SESSION['user_name'] = $name;
                $success = "Profile updated successfully!";
            }
        }
    }
}

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT name, email, phone, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<?php include __DIR__ . '/../src/views/public/header.php'; ?>

<div class="container my-5" style="max-width: 550px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-person-fill text-primary" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h1 class="fw-bold mb-0">My Profile</h1>
                    <p class="text-muted small mb-0">Manage your account settings</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= e($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= e($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-medium">Email Address</label>
                    <input type="email" class="form-control bg-light" value="<?= e($user['email']) ?>" disabled>
                    <div class="form-text">Email cannot be changed for security reasons.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Change Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep existing password">
                    <div class="form-text">Minimum <?= PASSWORD_MIN_LENGTH ?> characters</div>
                </div>
                <div class="mb-3 text-muted small">
                    <i class="bi bi-calendar-check me-1"></i> Member since: <?= formatDate($user['created_at'], 'M d, Y') ?>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/views/public/footer.php'; ?>