<?php
require_once __DIR__ . '/../../src/helpers/helpers.php';
initSession();
requireAdminAuth();

// Change enquiry status
if (isset($_GET['set_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $new_status = sanitizeInput($_GET['set_status']);
    $allowed = ['unread', 'read', 'replied'];
    
    if (!verifyCsrfToken($_GET[CSRF_TOKEN_NAME] ?? '') || !in_array($new_status, $allowed)) {
        redirectWithMessage('enquiries.php', 'Invalid request.', 'error');
    }
    
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("UPDATE enquiries SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    redirectWithMessage('enquiries.php', 'Enquiry marked as ' . ucfirst($new_status) . '!', 'success');
}

// Backward compatible toggle
if (isset($_GET['toggle_read'])) {
    $id = (int)$_GET['toggle_read'];
    if (!verifyCsrfToken($_GET[CSRF_TOKEN_NAME] ?? '')) {
        redirectWithMessage('enquiries.php', 'Invalid request.', 'error');
    }
    
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("UPDATE enquiries SET status = IF(status='read', 'unread', 'read') WHERE id = ?");
    $stmt->execute([$id]);
    redirectWithMessage('enquiries.php', 'Enquiry status updated!', 'success');
}

$pdo = getDbConnection();
$enquiries = $pdo->query("
    SELECT 
        e.*, 
        p.name AS package_name, 
        p.destination AS package_destination,
        u.name AS user_account_name
    FROM enquiries e 
    LEFT JOIN packages p ON e.package_id = p.id 
    LEFT JOIN users u ON e.user_id = u.id 
    ORDER BY e.id DESC
")->fetchAll();
?>
<?php include __DIR__ . '/../../src/views/admin/header.php'; ?>

<div class="container my-4">
    <?php displayFlashMessage(); ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Customer Enquiries</h2>
            <p class="text-muted small mb-0">Review questions, corporate inquiries, and reply to customers</p>
        </div>
        <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill"><?= count($enquiries) ?> Total Inquiries</span>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Sender</th>
                        <th>Tour Package</th>
                        <th>Subject & Preview</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($enquiries)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No enquiries received yet.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($enquiries as $eq): ?>
                        <tr class="enquiry-row <?= $eq['status'] == 'unread' ? 'fw-bold table-light' : '' ?>" data-enquiry-id="<?= $eq['id'] ?>" onclick="openEnquiryDrawer(<?= $eq['id'] ?>)" style="cursor: pointer;">
                            <td class="text-primary fw-bold">#<?= $eq['id'] ?></td>
                            <td>
                                <strong><?= e($eq['name']) ?></strong>
                                <?php if ($eq['user_account_name']): ?>
                                    <span class="badge bg-info text-dark" style="font-size: 0.65rem;">Member</span>
                                <?php endif; ?>
                                <br>
                                <a href="mailto:<?= e($eq['email']) ?>" class="text-decoration-none text-muted small" onclick="event.stopPropagation()"><?= e($eq['email']) ?></a>
                                <?php if (!empty($eq['phone'])): ?>
                                    <br><small class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($eq['phone']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($eq['package_name']): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                        <i class="bi bi-geo-alt me-1"></i><?= e($eq['package_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">General Inquiry</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= e($eq['subject']) ?></div>
                                <small class="text-muted fw-normal"><?= e(substr($eq['message'], 0, 50)) ?><?= strlen($eq['message']) > 50 ? '...' : '' ?></small>
                            </td>
                            <td class="text-muted small"><?= formatDate($eq['created_at'], 'M d, Y') ?></td>
                            <td>
                                <span class="badge <?= getStatusBadgeClass($eq['status']) ?>"><?= getStatusLabel($eq['status']) ?></span>
                            </td>
                            <td class="text-center" onclick="event.stopPropagation()">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                        Manage
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="enquiries.php?id=<?= $eq['id'] ?>&set_status=read&<?= CSRF_TOKEN_NAME ?>=<?= generateCsrfToken() ?>"><i class="bi bi-envelope-open me-2 text-secondary"></i>Mark as Read</a></li>
                                        <li><a class="dropdown-item" href="enquiries.php?id=<?= $eq['id'] ?>&set_status=unread&<?= CSRF_TOKEN_NAME ?>=<?= generateCsrfToken() ?>"><i class="bi bi-envelope me-2 text-warning"></i>Mark as Unread</a></li>
                                        <li><a class="dropdown-item" href="enquiries.php?id=<?= $eq['id'] ?>&set_status=replied&<?= CSRF_TOKEN_NAME ?>=<?= generateCsrfToken() ?>"><i class="bi bi-reply-fill me-2 text-success"></i>Mark as Replied</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-primary" href="mailto:<?= e($eq['email']) ?>?subject=Re: <?= urlencode($eq['subject']) ?>"><i class="bi bi-send me-2"></i>Send Email Reply</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Enquiry Detail Drawer -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="enquiryDrawer" aria-labelledby="enquiryDrawerLabel" style="width: 520px;">
    <div class="offcanvas-header bg-light border-bottom">
        <h5 class="offcanvas-title fw-bold" id="enquiryDrawerLabel">
            <i class="bi bi-envelope-open me-2 text-primary"></i>Enquiry Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4" id="enquiryDrawerBody">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading inquiry details...</p>
        </div>
    </div>
</div>
<input type="hidden" id="csrfToken" value="<?= generateCsrfToken() ?>">

<?php include __DIR__ . '/../../src/views/admin/footer.php'; ?>

<script>
function openEnquiryDrawer(id) {
    const drawerElement = document.getElementById('enquiryDrawer');
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(drawerElement);
    const body = document.getElementById('enquiryDrawerBody');
    
    // Show loading state
    body.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading inquiry details...</p>
        </div>
    `;
    
    drawer.show();
    
    // Fetch enquiry details
    fetch('enquiry_detail.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            body.innerHTML = html;
        })
        .catch(error => {
            body.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Failed to load enquiry details.
                </div>
            `;
        });
}

function updateEnquiryStatus(id, newStatus) {
    const csrfToken = document.getElementById('csrfToken').value;
    
    fetch('enquiries.php?id=' + id + '&set_status=' + newStatus + '&<?= CSRF_TOKEN_NAME ?>=' + csrfToken, {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(() => {
        // Refresh drawer content
        openEnquiryDrawer(id);
        // Refresh background table status badge
        const row = document.querySelector(`.enquiry-row[data-enquiry-id="${id}"]`);
        if (row) {
            const badge = row.querySelector('.badge');
            if (badge) {
                if (newStatus === 'read') {
                    badge.className = 'badge bg-secondary';
                    badge.textContent = 'Read';
                    row.classList.remove('fw-bold', 'table-light');
                } else if (newStatus === 'unread') {
                    badge.className = 'badge bg-warning text-dark';
                    badge.textContent = 'Unread';
                    row.classList.add('fw-bold', 'table-light');
                } else if (newStatus === 'replied') {
                    badge.className = 'badge bg-confirmed';
                    badge.textContent = 'Replied';
                    row.classList.remove('fw-bold', 'table-light');
                }
            }
        }
    })
    .catch(error => console.error('Error updating status:', error));
}
</script>
