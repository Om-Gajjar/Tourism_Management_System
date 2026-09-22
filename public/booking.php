<?php
require_once __DIR__ . '/../src/helpers/helpers.php';
initSession();
requireAuth();

$package_id = (int)($_GET['id'] ?? 0);
if (!$package_id) {
    header("Location: index.php");
    exit();
}

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch();

if (!$package) {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';
$travel_date = '';
$travelers = 1;
$special_requests = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $travel_date = sanitizeInput($_POST['travel_date'] ?? '');
        $travelers = (int)($_POST['number_of_travelers'] ?? 1);
        $special_requests = sanitizeInput($_POST['special_requests'] ?? '');

        $max_travelers = (int)($package['max_travelers'] ?? 20);
        if ($max_travelers <= 0) $max_travelers = 20;

        // Validation
        if (empty($travel_date)) {
            $error = "Please select a travel departure date.";
        } elseif (strtotime($travel_date) < strtotime(date('Y-m-d'))) {
            $error = "Travel date cannot be in the past.";
        } elseif ($travelers < 1 || $travelers > $max_travelers) {
            $error = "Number of travelers must be between 1 and $max_travelers.";
        } else {
            $user_id = getCurrentUserId();
            $total_price = (float)$package['price'] * $travelers;
            
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, package_id, travel_date, number_of_travelers, total_price, status, payment_status, special_requests)
                VALUES (?, ?, ?, ?, ?, 'pending', 'pending', ?)
            ");
            
            if ($stmt->execute([$user_id, $package_id, $travel_date, $travelers, $total_price, $special_requests ?: null])) {
                $booking_id = $pdo->lastInsertId();
                redirectWithMessage('my_bookings.php', "Booking #$booking_id placed successfully! Our team will confirm your reservation shortly.", 'success');
            } else {
                $error = "Failed to process booking. Please try again.";
            }
        }
    }
}

$min_date = date('Y-m-d', strtotime('+1 day'));
$max_date = date('Y-m-d', strtotime('+1 year'));
?>
<?php include __DIR__ . '/../src/views/public/header.php'; ?>

<div class="container my-5" style="max-width: 650px;">
    <div class="card border-0 shadow-sm p-4">
        <h2 class="fw-bold mb-3">Book Tour Package</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= e($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="mb-4 p-3 bg-light rounded-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h4 class="text-primary fw-bold mb-1"><?= e($package['name']) ?></h4>
                    <span class="badge bg-secondary me-2"><i class="bi bi-geo-alt me-1"></i><?= e($package['destination']) ?></span>
                    <span class="badge bg-light text-dark border"><i class="bi bi-clock me-1"></i><?= e($package['duration']) ?></span>
                </div>
                <div class="text-end">
                    <span class="text-muted small d-block">Price / person</span>
                    <span class="fs-4 text-success fw-bold"><?= formatPrice($package['price']) ?></span>
                </div>
            </div>
            <p class="text-muted small mb-0"><?= e($package['description']) ?></p>
        </div>

        <form method="POST" id="bookingForm">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Departure Date <span class="text-danger">*</span></label>
                    <input type="date" name="travel_date" class="form-control" min="<?= $min_date ?>" max="<?= $max_date ?>" value="<?= e($travel_date ?: $min_date) ?>" required>
                    <div class="form-text">Choose your departure date</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Number of Travelers <span class="text-danger">*</span></label>
                    <input type="number" id="travelersInput" name="number_of_travelers" class="form-control" min="1" max="<?= e($package['max_travelers'] ?? 20) ?>" value="<?= e((string)$travelers) ?>" required>
                    <div class="form-text">Max <?= e($package['max_travelers'] ?? 20) ?> guests</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Special Requests <span class="text-muted small fw-normal">(optional)</span></label>
                <textarea name="special_requests" class="form-control" rows="3" placeholder="Dietary requirements, room preferences, airport pickup details..."><?= e($special_requests) ?></textarea>
            </div>

            <!-- Price Breakdown Summary -->
            <div class="card border bg-light mb-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Rate per person</span>
                        <span><?= formatPrice($package['price']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Travelers</span>
                        <span id="travelersCount">1</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total Amount Due</span>
                        <span class="fs-4 fw-bold text-success" id="totalPriceDisplay"><?= formatPrice($package['price']) ?></span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill">Confirm & Reserve Booking</button>
        </form>

        <div class="mt-3 text-center">
            <a href="package_details.php?id=<?= $package['id'] ?>" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Package Details</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const basePrice = <?= (float)$package['price'] ?>;
    const input = document.getElementById('travelersInput');
    const countDisplay = document.getElementById('travelersCount');
    const totalDisplay = document.getElementById('totalPriceDisplay');

    function updateCalculations() {
        const count = Math.max(1, parseInt(input.value) || 1);
        countDisplay.textContent = count;
        const total = basePrice * count;
        totalDisplay.textContent = '$' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    input.addEventListener('input', updateCalculations);
    input.addEventListener('change', updateCalculations);
    updateCalculations();
});
</script>

<?php include __DIR__ . '/../src/views/public/footer.php'; ?>
