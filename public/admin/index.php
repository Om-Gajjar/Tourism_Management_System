<?php
require_once __DIR__ . '/../../src/helpers/helpers.php';
initSession();
requireAdminAuth();

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'updated') $message = "Package updated successfully!";
    if ($_GET['msg'] == 'deleted') $message = "Package deleted successfully!";
    if ($_GET['msg'] == 'added') $message = "Package added successfully!";
}

$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_package'])) {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $message = "Invalid request.";
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $destination = sanitizeInput($_POST['destination'] ?? '');
        $duration = sanitizeInput($_POST['duration'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $max_travelers = (int)($_POST['max_travelers'] ?? 20);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $description = sanitizeInput($_POST['description'] ?? '');
        $image = sanitizeInput($_POST['image'] ?? '');

        if (empty($image)) {
            $image = "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80";
        }

        if (!empty($name) && $price > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO packages (name, destination, duration, price, max_travelers, featured, is_active, description, image) 
                VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?)
            ");
            if ($stmt->execute([$name, $destination, $duration, $price, $max_travelers, $featured, $description, $image])) {
                redirectWithMessage('index.php', 'Package added successfully!', 'success');
            } else {
                $message = "Failed to add package.";
            }
        } else {
            $message = "Package name and a valid positive price are required.";
        }
    }
}

// Stats metrics
$total_packages = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'confirmed' OR payment_status = 'paid'")->fetchColumn() ?: 0;
$unread_enquiries = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'unread'")->fetchColumn();

// Fetch lists
$packages = $pdo->query("SELECT * FROM packages ORDER BY id DESC")->fetchAll();
$bookings = $pdo->query("
    SELECT 
        b.*, 
        u.name AS user_name, 
        u.email AS user_email, 
        u.phone AS user_phone,
        p.name AS package_name, 
        p.destination AS package_destination,
        p.price AS package_unit_price
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN packages p ON b.package_id = p.id 
    ORDER BY b.id DESC
")->fetchAll();
?>
<?php include __DIR__ . '/../../src/views/admin/header.php'; ?>

<div class="container my-4">
    <?php if($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php displayFlashMessage(); ?>

    <!-- Dashboard Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <small class="text-uppercase fw-bold text-muted">Total Packages</small>
                    <h2 class="fw-bold mb-0 text-primary"><?= $total_packages ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <small class="text-uppercase fw-bold text-muted">Confirmed Revenue</small>
                    <h2 class="fw-bold mb-0 text-success">$<?= number_format((float)$total_revenue, 2) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <small class="text-uppercase fw-bold text-muted">Total Bookings</small>
                    <h2 class="fw-bold mb-0 text-info"><?= $total_bookings ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <small class="text-uppercase fw-bold text-muted">Unread Enquiries</small>
                    <h2 class="fw-bold mb-0 text-warning"><?= $unread_enquiries ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Add Package Form -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4">
                <h4 class="fw-bold mb-3">Add New Package</h4>
                <form method="POST">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="add_package" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Package Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Swiss Alps Adventure" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Destination</label>
                        <input type="text" name="destination" class="form-control" placeholder="e.g. Switzerland" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Duration</label>
                        <input type="text" name="duration" class="form-control" placeholder="e.g. 5 Days / 4 Nights" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium">Price ($)</label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="1299.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Max Guests</label>
                            <input type="number" name="max_travelers" class="form-control" value="20" min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Image URL</label>
                        <input type="url" name="image" class="form-control" placeholder="https://images.unsplash.com/...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Overview of the tour experience..." required></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="addFeatured" name="featured" value="1">
                        <label class="form-check-label" for="addFeatured">Highlight as Featured Tour</label>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-pill">Save Package</button>
                </form>
            </div>
        </div>

        <!-- Right Side: Packages & Bookings Lists -->
        <div class="col-lg-8">
            <!-- Manage Packages Table -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Tour Packages Management</h5>
                    <span class="badge bg-light text-dark border"><?= count($packages) ?> Packages</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Package</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($packages as $p): ?>
                            <tr>
                                <td class="text-primary fw-bold">#<?= $p['id'] ?></td>
                                <td>
                                    <strong><?= e($p['name']) ?></strong>
                                    <?php if ($p['featured']): ?>
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem;">Featured</span>
                                    <?php endif; ?>
                                    <br>
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= e($p['destination']) ?></small>
                                </td>
                                <td>
                                    <small><?= e($p['duration']) ?></small><br>
                                    <small class="text-muted">Max: <?= e((string)($p['max_travelers'] ?? 20)) ?></small>
                                </td>
                                <td class="fw-bold text-success"><?= formatPrice($p['price']) ?></td>
                                <td>
                                    <?php if ($p['is_active']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_package.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary me-1 rounded-pill">Edit</a>
                                    <a href="delete_package.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Are you sure you want to delete this package?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bookings Management Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Customer Bookings</h5>
                    <span class="badge bg-light text-dark border"><?= count($bookings) ?> Bookings</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Tour & Date</th>
                                <th>Guests & Price</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $b): ?>
                            <tr>
                                <td class="text-primary fw-bold">#<?= $b['id'] ?></td>
                                <td>
                                    <strong><?= e($b['user_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($b['user_email']) ?></small>
                                    <?php if (!empty($b['user_phone'])): ?>
                                        <br><small class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($b['user_phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= e($b['package_name']) ?></strong><br>
                                    <small class="text-primary"><i class="bi bi-calendar-event me-1"></i><?= formatDate($b['travel_date'], 'M d, Y') ?></small>
                                    <?php if (!empty($b['special_requests'])): ?>
                                        <div class="small text-muted fst-italic" title="<?= e($b['special_requests']) ?>">
                                            <i class="bi bi-chat-quote me-1 text-info"></i>Req: <?= e(strlen($b['special_requests']) > 25 ? substr($b['special_requests'], 0, 25) . '...' : $b['special_requests']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border mb-1"><?= e($b['number_of_travelers']) ?> <?= $b['number_of_travelers'] > 1 ? 'Guests' : 'Guest' ?></span><br>
                                    <span class="fw-bold text-success"><?= formatPrice($b['total_price'] ?: $b['package_unit_price']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= getStatusBadgeClass($b['status']) ?>"><?= getStatusLabel($b['status']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= getStatusBadgeClass($b['payment_status']) ?>"><?= getStatusLabel($b['payment_status']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <!-- Booking Status dropdown -->
                                        <div class="dropdown">
                                            <button class="btn btn-xs btn-sm btn-outline-dark dropdown-toggle rounded-pill w-100" type="button" data-bs-toggle="dropdown" style="font-size: 0.75rem;">
                                                Status
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item text-success" href="update_booking.php?id=<?= $b['id'] ?>&status=confirmed"><i class="bi bi-check-circle me-2"></i>Confirm</a></li>
                                                <li><a class="dropdown-item text-warning" href="update_booking.php?id=<?= $b['id'] ?>&status=pending"><i class="bi bi-clock me-2"></i>Pending</a></li>
                                                <li><a class="dropdown-item text-danger" href="update_booking.php?id=<?= $b['id'] ?>&status=cancelled"><i class="bi bi-x-circle me-2"></i>Cancel</a></li>
                                            </ul>
                                        </div>
                                        <!-- Payment Status dropdown -->
                                        <div class="dropdown">
                                            <button class="btn btn-xs btn-sm btn-outline-secondary dropdown-toggle rounded-pill w-100" type="button" data-bs-toggle="dropdown" style="font-size: 0.75rem;">
                                                Payment
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item text-success" href="update_booking.php?id=<?= $b['id'] ?>&payment_status=paid"><i class="bi bi-credit-card-2-front me-2"></i>Paid</a></li>
                                                <li><a class="dropdown-item text-warning" href="update_booking.php?id=<?= $b['id'] ?>&payment_status=pending"><i class="bi bi-clock me-2"></i>Pending</a></li>
                                                <li><a class="dropdown-item text-danger" href="update_booking.php?id=<?= $b['id'] ?>&payment_status=refunded"><i class="bi bi-arrow-counterclockwise me-2"></i>Refunded</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../src/views/admin/footer.php'; ?>
