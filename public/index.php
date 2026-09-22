<?php
require_once __DIR__ . '/../src/helpers/helpers.php';
initSession();

$pdo = getDbConnection();
$packages = $pdo->query("
    SELECT 
        p.*, 
        AVG(r.rating) AS avg_rating, 
        COUNT(r.id) AS review_count
    FROM packages p
    LEFT JOIN reviews r ON p.id = r.package_id
    WHERE p.is_active = 1
    GROUP BY p.id
    ORDER BY p.featured DESC, p.id DESC
")->fetchAll();
?>
<?php include __DIR__ . '/../src/views/public/header.php'; ?>

<!-- Hero Section -->
<div class="hero-section text-center">
    <div class="container py-5 position-relative">
        <h1 class="display-3 fw-bold mb-3" style="letter-spacing: -0.02em;">Discover Your Next Adventure</h1>
        <p class="lead mb-4" style="max-width: 700px; margin: 0 auto;">Explore curated tour packages with simple booking and instant reservations.</p>
        <a href="#packages" class="btn btn-light btn-lg px-5 fw-bold rounded-pill shadow-sm">Browse Packages</a>
    </div>
</div>

<!-- Package Grid -->
<div class="container mb-5" id="packages">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Featured Packages</h2>
            <p class="text-muted small mb-0">Hand-picked destinations for unforgettable experiences</p>
        </div>
        <span class="badge bg-light text-muted border px-3 py-2 rounded-pill"><?= count($packages) ?> packages available</span>
    </div>
    <div class="row g-4">
        <?php foreach($packages as $pkg): ?>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm overflow-hidden position-relative">
                <?php if ($pkg['featured']): ?>
                    <span class="position-absolute top-0 end-0 badge bg-warning text-dark m-3 shadow-sm">
                        <i class="bi bi-star-fill me-1"></i>Featured
                    </span>
                <?php endif; ?>
                <img src="<?= e($pkg['image']) ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= e($pkg['name']) ?>">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary"><?= e($pkg['destination']) ?></span>
                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= e($pkg['duration']) ?></small>
                    </div>
                    <h5 class="card-title fw-bold mb-1"><?= e($pkg['name']) ?></h5>
                    
                    <!-- Rating summary -->
                    <div class="mb-2 small">
                        <?php if ($pkg['review_count'] > 0): ?>
                            <?= renderStars($pkg['avg_rating']) ?>
                            <span class="text-muted ms-1">(<?= (int)$pkg['review_count'] ?>)</span>
                        <?php else: ?>
                            <span class="text-muted"><i class="bi bi-star me-1"></i>New Tour</span>
                        <?php endif; ?>
                    </div>

                    <p class="card-text text-muted small flex-grow-1"><?= e(substr($pkg['description'], 0, 110)) ?>...</p>
                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Starting from</small>
                            <span class="fw-bold text-primary fs-5"><?= formatPrice($pkg['price']) ?></span>
                        </div>
                        <div>
                            <a href="package_details.php?id=<?= $pkg['id'] ?>" class="btn btn-outline-secondary btn-sm me-1 rounded-pill px-3">Details</a>
                            <a href="booking.php?id=<?= $pkg['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../src/views/public/footer.php'; ?>
