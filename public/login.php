<?php
require_once __DIR__ . '/../src/helpers/helpers.php';
initSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Please fill in all fields.";
        } else {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                // Regenerate session ID for security
                session_regenerate_id(true);
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid email or password.";
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
                <h1 class="fw-bold text-primary mb-1">Welcome Back</h1>
                <p class="text-muted">Sign in to your Tourism MS account</p>
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
                    <label class="form-label fw-medium">Email address</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Sign In</button>
            </form>

            <p class="mt-4 text-center text-muted small">
                Don't have an account? <a href="register.php" class="fw-medium">Create one</a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/views/public/footer.php'; ?>