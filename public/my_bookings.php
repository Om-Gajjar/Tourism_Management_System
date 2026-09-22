<?php
require_once __DIR__ . '/../src/helpers/helpers.php';
initSession();
requireAuth();

$user_id = getCurrentUserId();
$message = '';
$message_type = 'info';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $message = "Invalid request.";
        $message_type = 'error';
    } else {
        $pkg_id = (int)($_POST['package_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = sanitizeInput($_POST['comment'] ?? '');

        if ($pkg_id && $rating >= 1 && $rating <= 5) {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                INSERT INTO reviews (user_id, package_id, rating, comment)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)
            ");
            if ($stmt->execute([$user_id, $pkg_id, $rating, $comment ?: null])) {
                $message = "Thank you! Your review has been saved.";
                $message_type = 'success';
            } else {
                $message = "Could not save review.";
                $message_type = 'error';
            }
        }
    }
}

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $message = "Invalid request.";
        $message_type = 'error';
    } else {
        $cancel_id = (int)$_POST['cancel_booking_id'];
        $pdo = getDbConnection();
        $update_stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'cancelled', 
                payment_status = IF(payment_status = 'paid', 'refunded', payment_status)
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ");
        $update_stmt->execute([$cancel_id, $user_id]);
        if ($update_stmt->rowCount() > 0) {
            $message = "Booking #$cancel_id cancelled successfully.";
            $message_type = 'success';
        } else {
            $message = "Could not cancel booking or it is already processed.";
            $message_type = 'error';
        }
    }
}

// Fetch all bookings for this user with reviews info
$pdo = getDbConnection();
$stmt = $pdo->prepare("
    SELECT 
        b.id, b.travel_date, b.number_of_travelers, b.total_price, b.booking_date, b.status, b.payment_status, b.special_requests,
        p.id AS package_id, p.name AS package_name, p.destination, p.duration, p.price, p.image,
        r.rating AS user_rating, r.comment AS user_comment
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    LEFT JOIN reviews r ON (r.package_id = p.id AND r.user_id = b.user_id)
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->execute([$user_id]);
$my_bookings = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../src/views/public/header.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">My Bookings</h2>
            <p class="text-muted small mb-0">Track and manage all your reserved vacation itineraries</p>
        </div>
        <a href="index.php" class="btn btn-outline-primary btn-sm rounded-pill">+ Book Another Trip</a>
    </div>

    <?php displayFlashMessage(); ?>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type === 'success' ? 'success' : ($message_type === 'error' ? 'danger' : 'info') ?> alert-dismissible fade show" role="alert">
            <?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($my_bookings)): ?>
        <div class="card border-0 shadow-sm p-5 text-center">
            <h4 class="text-muted mb-2">No bookings yet!</h4>
            <p class="text-muted">You haven't reserved any vacation packages yet. Explore our destinations today!</p>
            <div class="mt-3">
                <a href="index.php" class="btn btn-primary rounded-pill px-4">Browse Packages</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking ID</th>
                            <th>Package & Destination</th>
                            <th>Travel Date</th>
                            <th>Guests</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_bookings as $b): ?>
                            <tr>
                                <td class="fw-bold text-primary">#<?= e($b['id']) ?></td>
                                <td>
                                    <strong><a href="package_details.php?id=<?= $b['package_id'] ?>" class="text-decoration-none text-dark"><?= e($b['package_name']) ?></a></strong><br>
                                    <small class="text-muted"><i class="bi bi-geo-alt-fill text-primary me-1"></i><?= e($b['destination'] ?? 'Global') ?> • <?= e($b['duration']) ?></small>
                                    <?php if (!empty($b['special_requests'])): ?>
                                        <div class="small text-muted fst-italic mt-1" title="<?= e($b['special_requests']) ?>">
                                            <i class="bi bi-chat-left-text me-1 text-info"></i>Note: <?= e(strlen($b['special_requests']) > 35 ? substr($b['special_requests'], 0, 35) . '...' : $b['special_requests']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-medium"><i class="bi bi-calendar-event me-1 text-primary"></i><?= formatDate($b['travel_date'], 'M d, Y') ?></span>
                                    <div class="text-muted small">Booked: <?= formatDate($b['booking_date'], 'M d, Y') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($b['number_of_travelers']) ?> <?= $b['number_of_travelers'] > 1 ? 'Guests' : 'Guest' ?></span>
                                </td>
                                <td class="text-success fw-bold"><?= formatPrice($b['total_price'] ?: $b['price']) ?></td>
                                <td>
                                    <span class="badge <?= getStatusBadgeClass($b['status']) ?>"><?= getStatusLabel($b['status']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= getStatusBadgeClass($b['payment_status']) ?>"><?= getStatusLabel($b['payment_status']) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <?php if ($b['status'] === 'pending'): ?>
                                            <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');" style="display:inline;">
                                                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                                                <input type="hidden" name="cancel_booking_id" value="<?= $b['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Cancel</button>
                                            </form>
                                        <?php elseif ($b['status'] === 'confirmed'): ?>
                                            <button type="button" class="btn btn-outline-warning btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#reviewModal<?= $b['id'] ?>">
                                                <i class="bi bi-star-fill me-1"></i><?= $b['user_rating'] ? 'Edit Review' : 'Rate Tour' ?>
                                            </button>

                                            <!-- Review Modal -->
                                            <div class="modal fade" id="reviewModal<?= $b['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content text-start">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fw-bold">Review <?= e($b['package_name']) ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                                                                <input type="hidden" name="submit_review" value="1">
                                                                <input type="hidden" name="package_id" value="<?= $b['package_id'] ?>">

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-medium">Rating (1 to 5 Stars)</label>
                                                                    <select name="rating" class="form-select" required>
                                                                        <option value="5" <?= ($b['user_rating'] ?? 5) == 5 ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5/5) Exceptional</option>
                                                                        <option value="4" <?= ($b['user_rating'] ?? 5) == 4 ? 'selected' : '' ?>>⭐⭐⭐⭐ (4/5) Very Good</option>
                                                                        <option value="3" <?= ($b['user_rating'] ?? 5) == 3 ? 'selected' : '' ?>>⭐⭐⭐ (3/5) Average</option>
                                                                        <option value="2" <?= ($b['user_rating'] ?? 5) == 2 ? 'selected' : '' ?>>⭐⭐ (2/5) Below Expectations</option>
                                                                        <option value="1" <?= ($b['user_rating'] ?? 5) == 1 ? 'selected' : '' ?>>⭐ (1/5) Poor</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-medium">Your Feedback</label>
                                                                    <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience on this tour..."><?= e($b['user_comment'] ?? '') ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary rounded-pill fw-bold">Submit Review</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">None</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../src/views/public/footer.php'; ?>
