<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'Create_report.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();

// Get user statistics for dashboard
$stmt = $pdo->prepare("SELECT COUNT(*) as total_stations FROM police_stations WHERE created_by = ?");
$stmt->execute([$_SESSION['user_id']]);
$station_stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as total_officers FROM officers WHERE created_by = ?");
$stmt->execute([$_SESSION['user_id']]);
$officer_stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as total_reports FROM salary_increment_reports WHERE created_by = ?");
$stmt->execute([$_SESSION['user_id']]);
$report_stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as total_calls FROM telephone_register WHERE created_by = ?");
$stmt->execute([$_SESSION['user_id']]);
$call_stats = $stmt->fetch();

$page_title = "Report Create Tool";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="bg-info text-white rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-file-alt me-3"></i>
                            Report Create Tool
                        </h1>
                        <p class="mb-0 opacity-75">
                            Hi <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! 
                            Manage office administration tasks including station management, officer records, salary reports, and communication logs.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <a href="index.php" class="btn btn-outline-light">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>

            <!-- Administrative Tools Dashboard -->
            <div class="row g-4 mb-5">
                <!-- Create Station -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">Create Station</h4>
                            <p class="card-text text-muted mb-4">
                                Manage police station records, create new station profiles, and maintain station administrative details.
                            </p>
                            <div class="mb-3">
                                <span class="badge bg-primary fs-6"><?php echo $station_stats['total_stations'] ?? 0; ?> Stations</span>
                            </div>
                            <a href="create_station.php" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-plus-circle me-2"></i>Manage Stations
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Add Officers -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-success text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user-plus fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">Add Officers</h4>
                            <p class="card-text text-muted mb-4">
                                Register new officers, manage officer profiles, maintain personnel records, and track assignments.
                            </p>
                            <div class="mb-3">
                                <span class="badge bg-success fs-6"><?php echo $officer_stats['total_officers'] ?? 0; ?> Officers</span>
                            </div>
                            <a href="add_officers.php" class="btn btn-success btn-lg px-4">
                                <i class="fas fa-users me-2"></i>Manage Officers
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Salary Increment Report -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-warning text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">Salary Increment Report</h4>
                            <p class="card-text text-muted mb-4">
                                Generate salary increment reports, track promotions, manage pay scales, and process salary adjustments.
                            </p>
                            <div class="mb-3">
                                <span class="badge bg-warning fs-6"><?php echo $report_stats['total_reports'] ?? 0; ?> Reports</span>
                            </div>
                            <a href="salary_increments_report.php" class="btn btn-warning btn-lg px-4">
                                <i class="fas fa-money-bill-wave me-2"></i>Create Reports
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Telephone Register -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-danger text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-phone fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">Telephone Register</h4>
                            <p class="card-text text-muted mb-4">
                                Maintain telephone call logs, track important communications, and manage contact directories.
                            </p>
                            <div class="mb-3">
                                <span class="badge bg-danger fs-6"><?php echo $call_stats['total_calls'] ?? 0; ?> Call Logs</span>
                            </div>
                            <a href="telephone_register.php" class="btn btn-danger btn-lg px-4">
                                <i class="fas fa-address-book me-2"></i>Manage Calls
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Overview -->
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <h3 class="mb-4">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Administrative Overview
                    </h3>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-primary text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-building fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-primary mb-1"><?php echo $station_stats['total_stations'] ?? 0; ?></h4>
                            <p class="text-muted mb-0">Police Stations</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-success text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-success mb-1"><?php echo $officer_stats['total_officers'] ?? 0; ?></h4>
                            <p class="text-muted mb-0">Registered Officers</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-warning text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-invoice-dollar fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-warning mb-1"><?php echo $report_stats['total_reports'] ?? 0; ?></h4>
                            <p class="text-muted mb-0">Salary Reports</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-danger text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-phone-volume fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-danger mb-1"><?php echo $call_stats['total_calls'] ?? 0; ?></h4>
                            <p class="text-muted mb-0">Call Records</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 border rounded hover-item">
                                        <i class="fas fa-plus text-primary me-3 fa-2x"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Add New Officer</h6>
                                            <small class="text-muted">Register a new police officer</small>
                                        </div>
                                        <a href="add_officers.php" class="btn btn-sm btn-outline-primary">
                                            Add Officer
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 border rounded hover-item">
                                        <i class="fas fa-building text-success me-3 fa-2x"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Register New Station</h6>
                                            <small class="text-muted">Add a new police station</small>
                                        </div>
                                        <a href="create_station.php" class="btn btn-sm btn-outline-success">
                                            Add Station
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 border rounded hover-item">
                                        <i class="fas fa-chart-line text-warning me-3 fa-2x"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Generate Salary Report</h6>
                                            <small class="text-muted">Create new salary increment report</small>
                                        </div>
                                        <a href="salary_increments_report.php" class="btn btn-sm btn-outline-warning">
                                            Create Report
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 border rounded hover-item">
                                        <i class="fas fa-phone text-danger me-3 fa-2x"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Log Phone Call</h6>
                                            <small class="text-muted">Record important communication</small>
                                        </div>
                                        <a href="telephone_register.php" class="btn btn-sm btn-outline-danger">
                                            Log Call
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>
                                Recent Administrative Activity
                            </h5>
                            <div>
                                <button class="btn btn-outline-primary btn-sm" onclick="refreshActivity()">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="recentActivity">
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-clock fa-2x mb-3"></i>
                                    <p>No recent activity. Start by using the administrative tools above!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Administrative Tools Guide
                        </h6>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li><strong>Create Station:</strong> Manage police station records and administrative details</li>
                                    <li><strong>Add Officers:</strong> Register new officers and maintain personnel records</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li><strong>Salary Reports:</strong> Generate increment reports and process salary adjustments</li>
                                    <li><strong>Telephone Register:</strong> Maintain call logs and communication records</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15) !important;
}

.feature-icon {
    transition: all 0.3s ease;
}

.hover-card:hover .feature-icon {
    transform: scale(1.1);
}

.hover-item {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-item:hover {
    border-color: var(--bs-primary) !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.card.border-0 {
    transition: all 0.3s ease;
}

.card.border-0:hover {
    box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.1) !important;
}

.badge.fs-6 {
    font-size: 1rem !important;
    padding: 0.5rem 1rem;
}

@media (max-width: 768px) {
    .bg-info.rounded-4 .col-lg-4 {
        text-align: center !important;
        margin-top: 1rem;
    }
    
    .feature-icon {
        width: 60px !important;
        height: 60px !important;
    }
    
    .feature-icon i {
        font-size: 1.5rem !important;
    }
}

.alert-info {
    border-left: 4px solid #17a2b8;
}
</style>

<script>
// Initialize recent activity display
function displayRecentActivity() {
    // This would normally fetch from database
    const activities = JSON.parse(localStorage.getItem('adminActivities') || '[]');
    const container = document.getElementById('recentActivity');
    
    if (activities.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-clock fa-2x mb-3"></i>
                <p>No recent activity. Start by using the administrative tools above!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = activities.slice(0, 5).map(activity => `
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
            <div class="d-flex align-items-center">
                <i class="${activity.icon} text-${activity.color} me-3 fa-lg"></i>
                <div>
                    <h6 class="mb-1">${activity.title}</h6>
                    <small class="text-muted">${activity.description}</small>
                </div>
            </div>
            <small class="text-muted">${activity.time}</small>
        </div>
    `).join('');
}

function refreshActivity() {
    // Simulate refresh
    const refreshBtn = document.querySelector('[onclick="refreshActivity()"]');
    const icon = refreshBtn.querySelector('i');
    
    icon.style.animation = 'spin 1s linear';
    
    setTimeout(() => {
        icon.style.animation = '';
        displayRecentActivity();
    }, 1000);
}

// Add activity to local storage (for demo purposes)
function addActivity(title, description, icon, color) {
    const activities = JSON.parse(localStorage.getItem('adminActivities') || '[]');
    
    activities.unshift({
        title,
        description,
        icon,
        color,
        time: new Date().toLocaleString()
    });
    
    // Keep only last 10 activities
    activities.splice(10);
    
    localStorage.setItem('adminActivities', JSON.stringify(activities));
    displayRecentActivity();
}

// Animation on page load
document.addEventListener('DOMContentLoaded', function() {
    displayRecentActivity();
    
    // Animate cards
    const cards = document.querySelectorAll('.hover-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });

    // Animate stats cards
    const statCards = document.querySelectorAll('.card.border-0.shadow-sm');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            card.style.transition = 'all 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'scale(1)';
            }, 50);
        }, (index + 4) * 100);
    });
});

// Add CSS animation for refresh button
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
</script>

<?php include 'includes/footer.php'; ?>