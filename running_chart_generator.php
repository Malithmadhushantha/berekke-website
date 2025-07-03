<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'running_chart_generator.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();

// Get user's vehicles count
$stmt = $pdo->prepare("SELECT COUNT(*) as total_vehicles, 
                              SUM(CASE WHEN is_primary = 1 THEN 1 ELSE 0 END) as primary_vehicles 
                       FROM vehicles WHERE user_id = ? AND status = 'active'");
$stmt->execute([$_SESSION['user_id']]);
$vehicle_stats = $stmt->fetch();

// Get recent trip entries
$stmt = $pdo->prepare("SELECT COUNT(*) as total_trips FROM trip_entries WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$trip_stats = $stmt->fetch();

$page_title = "Vehicle Running Chart System";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="bg-warning text-dark rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-car me-3"></i>
                            Vehicle Running Chart System
                        </h1>
                        <p class="mb-0 opacity-75">
                            Hi <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! 
                            Manage your vehicle logbooks, track fuel consumption, and calculate running charts for company vehicles.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-tachometer-alt fa-4x opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- Main Navigation Buttons -->
            <div class="row g-4 mb-5">
                <!-- Running Chart Button -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">Running Chart</h4>
                            <p class="card-text text-muted mb-4">
                                Calculate fuel consumption, track mileage, and maintain detailed trip records for your primary vehicle.
                            </p>
                            <a href="running_chart.php" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-calculator me-2"></i>Open Running Chart
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Add Vehicle Button -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-success text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-plus-circle fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">Add Vehicle</h4>
                            <p class="card-text text-muted mb-4">
                                Register new vehicles, manage vehicle details, and set up your fleet for tracking and monitoring.
                            </p>
                            <a href="add_vehicle.php" class="btn btn-success btn-lg px-4">
                                <i class="fas fa-car me-2"></i>Manage Vehicles
                            </a>
                        </div>
                    </div>
                </div>

                <!-- My Entries Button -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-info text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-list-alt fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">My Entries</h4>
                            <p class="card-text text-muted mb-4">
                                View and manage all your trip entries, review historical data, and export logbook records.
                            </p>
                            <a href="entry_viwe_page.php" class="btn btn-info btn-lg px-4">
                                <i class="fas fa-history me-2"></i>View Entries
                            </a>
                        </div>
                    </div>
                </div>

                <!-- R/C Calculator Button -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-warning text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-calculator fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">R/C Calculator</h4>
                            <p class="card-text text-muted mb-4">
                                Perform quick calculations for fuel consumption, distance planning, and cost analysis.
                            </p>
                            <a href="rc_calculator.php" class="btn btn-warning btn-lg px-4">
                                <i class="fas fa-calculator me-2"></i>Open Calculator
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Statistics -->
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <h3 class="mb-4">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard Overview
                    </h3>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-primary text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-car fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-primary mb-1"><?php echo $vehicle_stats['total_vehicles']; ?></h4>
                            <p class="text-muted mb-0">Total Vehicles</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-success text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-star fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-success mb-1"><?php echo $vehicle_stats['primary_vehicles']; ?></h4>
                            <p class="text-muted mb-0">Primary Vehicle</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-info text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-route fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-info mb-1"><?php echo $trip_stats['total_trips']; ?></h4>
                            <p class="text-muted mb-0">Total Trips</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-warning text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-calendar-day fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-warning mb-1"><?php echo date('d'); ?></h4>
                            <p class="text-muted mb-0">Today</p>
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
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <i class="fas fa-plus text-primary me-3 fa-2x"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Add New Trip Entry</h6>
                                            <small class="text-muted">Record a new journey in your logbook</small>
                                        </div>
                                        <a href="running_chart.php" class="btn btn-sm btn-outline-primary">
                                            Add Trip
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <i class="fas fa-car text-success me-3 fa-2x"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Register New Vehicle</h6>
                                            <small class="text-muted">Add a new vehicle to your fleet</small>
                                        </div>
                                        <a href="add_vehicle.php" class="btn btn-sm btn-outline-success">
                                            Add Vehicle
                                        </a>
                                    </div>
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
                            How to Use the Vehicle Running Chart System
                        </h6>
                        <hr>
                        <ol class="mb-0">
                            <li><strong>Add Vehicle:</strong> Start by registering your vehicles with all necessary details</li>
                            <li><strong>Set Primary Vehicle:</strong> Designate one vehicle as primary for quick access</li>
                            <li><strong>Running Chart:</strong> Use the running chart to calculate fuel consumption and track trips</li>
                            <li><strong>View Entries:</strong> Review all your recorded trips and generate reports</li>
                            <li><strong>Calculator:</strong> Use the R/C calculator for quick fuel and distance calculations</li>
                        </ol>
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

.card.border-0 {
    transition: all 0.3s ease;
}

.card.border-0:hover {
    box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.1) !important;
}

@media (max-width: 768px) {
    .bg-warning.rounded-4 .col-lg-4 {
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
// Animation on load
document.addEventListener('DOMContentLoaded', function() {
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

    // Animate statistics cards
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

// Quick navigation functions
function goToRunningChart() {
    window.location.href = 'running_chart.php';
}

function goToAddVehicle() {
    window.location.href = 'add_vehicle.php';
}

function goToMyEntries() {
    window.location.href = 'entry_viwe_page.php';
}

function goToCalculator() {
    window.location.href = 'rc_calculator.php';
}

// Check if user has vehicles before going to running chart
function checkVehiclesBeforeChart() {
    const totalVehicles = <?php echo $vehicle_stats['total_vehicles']; ?>;
    if (totalVehicles === 0) {
        alert('Please add at least one vehicle before using the running chart.');
        window.location.href = 'add_vehicle.php';
        return false;
    }
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>