<?php
require_once __DIR__ . '/../src/helpers/helpers.php';
initSession();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            $error = "Please fill in all required fields.";
        } elseif (!validateEmail($email)) {
            $error = "Please provide a valid email address.";
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $error = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters.";
        } else {
            try {
                $pdo = getDbConnection();
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone ?: null, $hashedPassword]);
                $success = "Registration successful! You can now login.";
            } catch (\PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $error = "Email already exists.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>
<?php include __DIR__ . '/../src/views/public/header.php'; ?>

<div class="container my-5" style="max-width: 450px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h1 class="fw-bold text-primary mb-1">Create Account</h1>
                <p class="text-muted">Join Tourism MS and start exploring</p>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= e($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= e($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-medium">Full Name</label>
                    <input type="text" name="name" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Email address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Phone Number <span class="text-muted small fw-normal">(optional)</span></label>
                    <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="<?= PASSWORD_MIN_LENGTH ?>">
                    <div class="form-text">At least <?= PASSWORD_MIN_LENGTH ?> characters</div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Create Account</button>
            </form>

            <p class="mt-4 text-center text-muted small">
                Already have an account? <a href="login.php" class="fw-medium">Sign in</a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/views/public/footer.php'; ?>