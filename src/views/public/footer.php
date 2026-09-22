<footer class="bg-white border-top py-5 mt-auto">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bi bi-globe2 me-1"></i><?= APP_NAME ?>
                </h5>
                <p class="text-muted small">
                    Your trusted partner for effortless travel planning. Explore destinations, book packages, and manage your trips all in one place.
                </p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="index.php" class="text-decoration-none text-muted"><i class="bi bi-chevron-right me-1"></i>Home</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-decoration-none text-muted"><i class="bi bi-chevron-right me-1"></i>Contact Us</a></li>
                    <li class="mb-2"><a href="admin/login.php" class="text-decoration-none text-muted"><i class="bi bi-chevron-right me-1"></i>Admin Area</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold mb-3">Account</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="login.php" class="text-decoration-none text-muted"><i class="bi bi-chevron-right me-1"></i>Login</a></li>
                    <li class="mb-2"><a href="register.php" class="text-decoration-none text-muted"><i class="bi bi-chevron-right me-1"></i>Register</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li class="mb-2"><a href="my_bookings.php" class="text-decoration-none text-muted"><i class="bi bi-chevron-right me-1"></i>My Bookings</a></li>
                        <li class="mb-2"><a href="profile.php" class="text-decoration-none text-muted"><i class="bi bi-chevron-right me-1"></i>Profile</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold mb-3">Connect</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted"><i class="bi bi-facebook me-1"></i>Facebook</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted"><i class="bi bi-twitter me-1"></i>Twitter</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted"><i class="bi bi-instagram me-1"></i>Instagram</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4 opacity-25">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted">
            <p class="mb-2 mb-md-0">&copy; <?= date('Y') ?> <?= APP_NAME ?>. Built with <i class="bi bi-heart-fill text-danger"></i> for beginners.</p>
            <div class="d-flex gap-3">
                <a href="#" class="text-decoration-none text-muted">Privacy Policy</a>
                <a href="#" class="text-decoration-none text-muted">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>