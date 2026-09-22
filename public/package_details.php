<?php
require_once __DIR__ . '/../src/helpers/helpers.php';
initSession();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: index.php");
    exit();
}

$pdo = getDbConnection();

// Fetch package
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$id]);
$package = $stmt->fetch();

if (!$package) {
    header("Location: index.php");
    exit();
}

$review_message = '';
$review_type = 'info';

// Handle direct review submission if logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    requireAuth();
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $review_message = "Invalid request.";
        $review_type = 'danger';
    } else {
        $user_id = getCurrentUserId();
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = sanitizeInput($_POST['comment'] ?? '');

        if ($rating >= 1 && $rating <= 5) {
            $stmt = $pdo->prepare("
                INSERT INTO reviews (user_id, package_id, rating, comment)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)
            ");
            if ($stmt->execute([$user_id, $id, $rating, $comment ?: null])) {
                $review_message = "Your review has been submitted successfully!";
                $review_type = 'success';
            } else {
                $review_message = "Failed to submit review.";
                $review_type = 'danger';
            }
        }
    }
}

// Fetch reviews for this package
$rev_stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.package_id = ? 
    ORDER BY r.created_at DESC
");
$rev_stmt->execute([$id]);
$reviews = $rev_stmt->fetchAll();

// Calculate average rating
$total_reviews = count($reviews);
$avg_rating = $total_reviews > 0 ? array_sum(array_column($reviews, 'rating')) / $total_reviews : 0;

// Check if current user has already reviewed
$user_has_reviewed = false;
$user_review = null;
if (isLoggedIn()) {
    $current_user_id = getCurrentUserId();
    foreach ($reviews as $rev) {
        if ($rev['user_id'] == $current_user_id) {
            $user_has_reviewed = true;
            $user_review = $rev;
            break;
        }
    }
}
?>
<?php include __DIR__ . '/../src/views/public/header.php'; ?>

<div class="container my-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0 mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($package['name']) ?></li>
        </ol>
    </nav>

    <?php if ($review_message): ?>
        <div class="alert alert-<?= $review_type ?> alert-dismissible fade show" role="alert">
            <?= e($review_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <img src="<?= e($package['image']) ?>" class="img-fluid" style="height: 400px; width: 100%; object-fit: cover;" alt="<?= e($package['name']) ?>">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary"><?= e($package['destination']) ?></span>
                        <?php if ($package['featured']): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Featured Tour</span>
                        <?php endif; ?>
                    </div>
                    
                    <h2 class="card-title fw-bold mb-3"><?= e($package['name']) ?></h2>
                    
                    <div class="d-flex flex-wrap gap-3 mb-4 text-muted small">
                        <span><i class="bi bi-clock me-1 text-primary"></i> Duration: <strong><?= e($package['duration']) ?></strong></span>
                        <span><i class="bi bi-geo-alt me-1 text-primary"></i> Destination: <strong><?= e($package['destination']) ?></strong></span>
                        <span><i class="bi bi-people me-1 text-primary"></i> Max Group: <strong><?= e((string)($package['max_travelers'] ?? 20)) ?> Guests</strong></span>
                        <?php if ($total_reviews > 0): ?>
                            <span>
                                <?= renderStars($avg_rating) ?>
                                <strong><?= number_format($avg_rating, 1) ?></strong> (<?= $total_reviews ?> <?= $total_reviews === 1 ? 'review' : 'reviews' ?>)
                            </span>
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold mb-3">Overview</h5>
                    <p class="text-muted" style="line-height: 1.8;"><?= nl2br(e($package['description'])) ?></p>

                    <h5 class="fw-bold mb-3 mt-4">What's Included</h5>
                    <ul class="list-unstyled text-muted mb-0">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Verified premium accommodation & guided tours</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> 24/7 Concierge & emergency support</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Instant reservation confirmation with price lock</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Free itinerary customization upon request</li>
                    </ul>
                </div>
            </div>

            <!-- Customer Reviews Section -->
            <div class="card border-0 shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-0">Customer Reviews</h4>
                        <div class="text-muted small">
                            <?php if ($total_reviews > 0): ?>
                                <?= renderStars($avg_rating) ?>
                                <span class="fw-bold ms-1"><?= number_format($avg_rating, 1) ?> out of 5</span> (<?= $total_reviews ?> <?= $total_reviews === 1 ? 'verified review' : 'verified reviews' ?>)
                            <?php else: ?>
                                No reviews yet. Be the first to share your experience!
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Review List -->
                <?php if (!empty($reviews)): ?>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach ($reviews as $r): ?>
                            <div class="p-3 bg-light rounded-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.85rem; font-weight: bold;">
                                            <?= strtoupper(substr($r['user_name'], 0, 1)) ?>
                                        </div>
                                        <strong class="text-dark"><?= e($r['user_name']) ?></strong>
                                    </div>
                                    <span class="text-muted small"><?= formatDate($r['created_at'], 'M d, Y') ?></span>
                                </div>
                                <div class="mb-2">
                                    <?= renderStars($r['rating']) ?>
                                </div>
                                <?php if (!empty($r['comment'])): ?>
                                    <p class="text-muted mb-0 small"><?= nl2br(e($r['comment'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Leave / Edit Review Form -->
                <div class="border-top pt-4">
                    <h5 class="fw-bold mb-3"><?= $user_has_reviewed ? 'Update Your Review' : 'Leave a Review' ?></h5>
                    <?php if (isLoggedIn()): ?>
                        <form method="POST">
                            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="submit_review" value="1">
                            
                            <div class="mb-3">
                                <label class="form-label fw-medium">Your Rating</label>
                                <select name="rating" class="form-select" required>
                                    <option value="5" <?= ($user_review['rating'] ?? 5) == 5 ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5/5) Exceptional</option>
                                    <option value="4" <?= ($user_review['rating'] ?? 5) == 4 ? 'selected' : '' ?>>⭐⭐⭐⭐ (4/5) Very Good</option>
                                    <option value="3" <?= ($user_review['rating'] ?? 5) == 3 ? 'selected' : '' ?>>⭐⭐⭐ (3/5) Average</option>
                                    <option value="2" <?= ($user_review['rating'] ?? 5) == 2 ? 'selected' : '' ?>>⭐⭐ (2/5) Below Expectations</option>
                                    <option value="1" <?= ($user_review['rating'] ?? 5) == 1 ? 'selected' : '' ?>>⭐ (1/5) Poor</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium">Your Feedback</label>
                                <textarea name="comment" class="form-control" rows="3" placeholder="What did you enjoy most about this tour?"><?= e($user_review['comment'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                                <?= $user_has_reviewed ? 'Update Review' : 'Submit Review' ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="p-3 bg-light rounded text-center">
                            <p class="text-muted small mb-2">Have you experienced this tour? Sign in to share your review.</p>
                            <a href="login.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Sign In to Review</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 sticky-top" style="top: 20px;">
                <h4 class="fw-bold mb-3">Booking Summary</h4>
                <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-3">
                    <div>
                        <span class="text-muted small d-block">Starting from</span>
                        <span class="fs-2 fw-bold text-primary"><?= formatPrice($package['price']) ?></span>
                        <span class="text-muted small">/ person</span>
                    </div>
                    <div class="text-end text-muted small">
                        <div><i class="bi bi-shield-check text-success me-1"></i>Best Price Guarantee</div>
                        <div><i class="bi bi-clock-history text-primary me-1"></i>Free Cancellation</div>
                    </div>
                </div>
                
                <a href="booking.php?id=<?= $package['id'] ?>" class="btn btn-primary btn-lg w-100 mb-2 fw-bold py-3 rounded-pill shadow-sm">
                    Book This Tour <i class="bi bi-arrow-right ms-1"></i>
                </a>
                <a href="contact.php?package_id=<?= $package['id'] ?>" class="btn btn-outline-secondary w-100 fw-bold py-3 rounded-pill">
                    Ask a Question
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/views/public/footer.php'; ?>
