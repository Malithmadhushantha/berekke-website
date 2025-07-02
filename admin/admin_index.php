<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

// Get dashboard statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'active_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE email_verified = 1")->fetchColumn(),
    'total_blogs' => $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn(),
    'published_blogs' => $pdo->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn(),
    'total_downloads' => $pdo->query("SELECT COUNT(*) FROM downloads")->fetchColumn(),
    'total_categories' => $pdo->query("SELECT COUNT(*) FROM download_categories")->fetchColumn(),
    'penal_code_sections' => $pdo->query("SELECT COUNT(*) FROM penal_code")->fetchColumn(),
    'criminal_procedure_sections' => $pdo->query("SELECT COUNT(*) FROM criminal_procedure_code")->fetchColumn(),
    'evidence_ordinance_sections' => $pdo->query("SELECT COUNT(*) FROM evidence_ordinance")->fetchColumn(),
    'total_bookmarks' => $pdo->query("SELECT COUNT(*) FROM user_bookmarks")->fetchColumn()
];

// Get recent activities
$recent_users = $pdo->query("SELECT first_name, last_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_blogs = $pdo->query("SELECT b.title, u.first_name, u.last_name, b.created_at FROM blogs b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll();
$recent_downloads = $pdo->query("SELECT title, file_size, created_at FROM downloads ORDER BY created_at DESC LIMIT 5")->fetchAll();

$page_title = "Admin Dashboard";
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>
                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                        Admin Dashboard
                    </h2>
                    <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
                </div>
                <div>
                    <a href="../index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-1"></i>Back to Website
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-3 p-3">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1"><?php echo number_format($stats['total_users']); ?></h3>
                            <p class="text-muted mb-0">Total Users</p>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>
                                <?php echo number_format($stats['active_users']); ?> verified
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success text-white rounded-3 p-3">
                                <i class="fas fa-blog fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1"><?php echo number_format($stats['total_blogs']); ?></h3>
                            <p class="text-muted mb-0">Blog Posts</p>
                            <small class="text-success">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo number_format($stats['published_blogs']); ?> published
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning text-white rounded-3 p-3">
                                <i class="fas fa-download fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1"><?php echo number_format($stats['total_downloads']); ?></h3>
                            <p class="text-muted mb-0">Downloads</p>
                            <small class="text-info">
                                <i class="fas fa-folder me-1"></i>
                                <?php echo number_format($stats['total_categories']); ?> categories
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info text-white rounded-3 p-3">
                                <i class="fas fa-gavel fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1"><?php echo number_format($stats['penal_code_sections'] + $stats['criminal_procedure_sections'] + $stats['evidence_ordinance_sections']); ?></h3>
                            <p class="text-muted mb-0">Legal Sections</p>
                            <small class="text-warning">
                                <i class="fas fa-bookmark me-1"></i>
                                <?php echo number_format($stats['total_bookmarks']); ?> bookmarked
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legal Database Statistics -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-balance-scale me-2"></i>
                        Legal Database Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-lg-4 col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <h4 class="text-primary mb-2"><?php echo number_format($stats['penal_code_sections']); ?></h4>
                                <p class="mb-1">Penal Code Sections</p>
                                <a href="manage_penal_code.php" class="btn btn-sm btn-outline-primary">Manage</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <h4 class="text-success mb-2"><?php echo number_format($stats['criminal_procedure_sections']); ?></h4>
                                <p class="mb-1">Criminal Procedure Sections</p>
                                <a href="manage_criminal_procedure.php" class="btn btn-sm btn-outline-success">Manage</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <h4 class="text-warning mb-2"><?php echo number_format($stats['evidence_ordinance_sections']); ?></h4>
                                <p class="mb-1">Evidence Ordinance Sections</p>
                                <a href="manage_evidence_ordinance.php" class="btn btn-sm btn-outline-warning">Manage</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="upload_download.php" class="btn btn-outline-primary w-100 p-3">
                                <i class="fas fa-upload fa-2x mb-2 d-block"></i>
                                Upload File
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="manage_users.php" class="btn btn-outline-success w-100 p-3">
                                <i class="fas fa-users-cog fa-2x mb-2 d-block"></i>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="manage_blogs.php" class="btn btn-outline-warning w-100 p-3">
                                <i class="fas fa-blog fa-2x mb-2 d-block"></i>
                                Manage Blogs
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="manage_downloads.php" class="btn btn-outline-info w-100 p-3">
                                <i class="fas fa-download fa-2x mb-2 d-block"></i>
                                Manage Downloads
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Recent Users
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_users)): ?>
                        <?php foreach ($recent_users as $user): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                <br>
                                <small class="text-muted"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No recent users</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-blog me-2"></i>
                        Recent Blog Posts
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_blogs)): ?>
                        <?php foreach ($recent_blogs as $blog): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-pen"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars(substr($blog['title'], 0, 30)) . (strlen($blog['title']) > 30 ? '...' : ''); ?></h6>
                                <small class="text-muted">By <?php echo htmlspecialchars($blog['first_name'] . ' ' . $blog['last_name']); ?></small>
                                <br>
                                <small class="text-muted"><?php echo date('M j, Y', strtotime($blog['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No recent blogs</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-download me-2"></i>
                        Recent Downloads
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_downloads)): ?>
                        <?php foreach ($recent_downloads as $download): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-file"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars(substr($download['title'], 0, 25)) . (strlen($download['title']) > 25 ? '...' : ''); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($download['file_size']); ?></small>
                                <br>
                                <small class="text-muted"><?php echo date('M j, Y', strtotime($download['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No recent downloads</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
}

@media (max-width: 768px) {
    .container-fluid {
        padding-left: 15px;
        padding-right: 15px;
    }
}
</style>

<script>
// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Animate statistics cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Add click tracking for quick actions
    const quickActions = document.querySelectorAll('.card-body .btn');
    quickActions.forEach(btn => {
        btn.addEventListener('click', function() {
            console.log('Quick action clicked:', this.textContent.trim());
        });
    });
});

// Auto-refresh statistics every 5 minutes
setInterval(() => {
    // You can implement AJAX refresh here
    console.log('Auto-refreshing statistics...');
}, 300000);

// Chart.js integration (you can add charts later)
function initializeCharts() {
    // Placeholder for future chart implementations
    console.log('Charts would be initialized here');
}

// Print dashboard function
function printDashboard() {
    window.print();
}

// Export dashboard data
function exportDashboard() {
    const data = {
        timestamp: new Date().toISOString(),
        statistics: <?php echo json_encode($stats); ?>,
        generated_by: '<?php echo addslashes($_SESSION['user_name']); ?>'
    };
    
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(data, null, 2));
    const downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("download", "dashboard_export_" + new Date().toISOString().split('T')[0] + ".json");
    document.body.appendChild(downloadAnchorNode);
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
}
</script>

<?php include '../includes/footer.php'; ?>