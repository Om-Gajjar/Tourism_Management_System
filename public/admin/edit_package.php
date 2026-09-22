<?php
require_once __DIR__ . '/../../src/helpers/helpers.php';
initSession();
requireAdminAuth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: index.php");
    exit();
}

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$id]);
$package = $stmt->fetch();

if (!$package) {
    header("Location: index.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $name = sanitizeInput($_POST['name']);
        $destination = sanitizeInput($_POST['destination']);
        $duration = sanitizeInput($_POST['duration']);
        $price = (float)$_POST['price'];
        $max_travelers = (int)($_POST['max_travelers'] ?? 20);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $description = sanitizeInput($_POST['description']);
        $image = sanitizeInput($_POST['image']);

        if (empty($name) || $price <= 0) {
            $error = "Package Name and Price (must be greater than 0) are required.";
        } else {
            $update_stmt = $pdo->prepare("
                UPDATE packages 
                SET name = ?, destination = ?, duration = ?, price = ?, max_travelers = ?, featured = ?, is_active = ?, description = ?, image = ? 
                WHERE id = ?
            ");
            if ($update_stmt->execute([$name, $destination, $duration, $price, $max_travelers, $featured, $is_active, $description, $image, $id])) {
                redirectWithMessage('index.php', 'Package updated successfully!', 'success');
            } else {
                $error = "Failed to update package.";
            }
        }
    }
}
?>
<?php include __DIR__ . '/../../src/views/admin/header.php'; ?>

<div class="container my-5" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Edit Package #<?= $package['id'] ?></h2>
            <p class="text-muted small mb-0">Update package pricing, destination, and configuration</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
    </div>

    <div class="card border-0 shadow-sm p-4">
        <?php if ($message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= e($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= e($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php displayFlashMessage(); ?>

        <form method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
            
            <div class="mb-3">
                <label class="form-label fw-medium">Package Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= e($package['name']) ?>" required>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Destination <span class="text-danger">*</span></label>
                    <input type="text" name="destination" class="form-control" value="<?= e($package['destination'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Duration <span class="text-danger">*</span></label>
                    <input type="text" name="duration" class="form-control" value="<?= e($package['duration'] ?? '') ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Price ($) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= e($package['price']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Max Travelers per Group</label>
                    <input type="number" name="max_travelers" class="form-control" value="<?= e((string)($package['max_travelers'] ?? 20)) ?>" min="1">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Image URL</label>
                <input type="url" name="image" class="form-control" value="<?= e($package['image'] ?? '') ?>" placeholder="https://images.unsplash.com/...">
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Description</label>
                <textarea name="description" class="form-control" rows="4"><?= e($package['description']) ?></textarea>
            </div>

            <div class="row g-3 mb-4 p-3 bg-light rounded-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" <?= $package['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-medium" for="isActive">Active (Visible to Customers)</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="isFeatured" name="featured" value="1" <?= $package['featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-medium" for="isFeatured">Featured on Homepage</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../src/views/admin/footer.php'; ?>
