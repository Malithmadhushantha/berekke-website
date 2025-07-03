<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'add_vehicle.php';
    header('Location: login.php');
    exit();
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_vehicle'])) {
        $vehicle_type = cleanInput($_POST['vehicle_type']);
        $model = cleanInput($_POST['model']);
        $registration_number = cleanInput($_POST['registration_number']);
        $date_of_acquisition = cleanInput($_POST['date_of_acquisition']);
        $ownership_note = cleanInput($_POST['ownership_note']);
        $current_odo_reading = floatval($_POST['current_odo_reading']);
        $current_fuel_amount = floatval($_POST['current_fuel_amount']);
        $fuel_efficiency = floatval($_POST['fuel_efficiency']);
        $last_service_date = !empty($_POST['last_service_date']) ? cleanInput($_POST['last_service_date']) : null;
        $odo_at_last_service = !empty($_POST['odo_at_last_service']) ? floatval($_POST['odo_at_last_service']) : null;
        $is_primary = isset($_POST['is_primary']) && $_POST['is_primary'] === 'yes' ? 1 : 0;
        
        // Validation
        if (empty($vehicle_type) || empty($model) || empty($registration_number) || empty($date_of_acquisition)) {
            $error_message = 'Please fill in all required fields.';
        } elseif ($current_odo_reading < 0 || $current_fuel_amount < 0 || $fuel_efficiency <= 0) {
            $error_message = 'Please enter valid positive numbers for ODO reading, fuel amount, and efficiency.';
        } else {
            try {
                // Check if registration number already exists
                $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE registration_number = ?");
                $stmt->execute([$registration_number]);
                if ($stmt->fetch()) {
                    $error_message = 'A vehicle with this registration number already exists.';
                } else {
                    // If this is set as primary, remove primary status from other vehicles
                    if ($is_primary) {
                        $stmt = $pdo->prepare("UPDATE vehicles SET is_primary = 0 WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                    }
                    
                    // Insert new vehicle
                    $stmt = $pdo->prepare("INSERT INTO vehicles (user_id, vehicle_type, model, registration_number, date_of_acquisition, ownership_note, current_odo_reading, current_fuel_amount, fuel_efficiency, last_service_date, odo_at_last_service, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $vehicle_type,
                        $model,
                        $registration_number,
                        $date_of_acquisition,
                        $ownership_note,
                        $current_odo_reading,
                        $current_fuel_amount,
                        $fuel_efficiency,
                        $last_service_date,
                        $odo_at_last_service,
                        $is_primary
                    ]);
                    
                    $success_message = 'Vehicle added successfully!';
                    
                    // Clear form fields
                    $_POST = array();
                }
            } catch (PDOException $e) {
                $error_message = 'Failed to add vehicle. Please try again.';
            }
        }
    } elseif (isset($_POST['update_vehicle'])) {
        // Handle vehicle update
        $vehicle_id = intval($_POST['vehicle_id']);
        $vehicle_type = cleanInput($_POST['vehicle_type']);
        $model = cleanInput($_POST['model']);
        $registration_number = cleanInput($_POST['registration_number']);
        $date_of_acquisition = cleanInput($_POST['date_of_acquisition']);
        $ownership_note = cleanInput($_POST['ownership_note']);
        $current_odo_reading = floatval($_POST['current_odo_reading']);
        $current_fuel_amount = floatval($_POST['current_fuel_amount']);
        $fuel_efficiency = floatval($_POST['fuel_efficiency']);
        $last_service_date = !empty($_POST['last_service_date']) ? cleanInput($_POST['last_service_date']) : null;
        $odo_at_last_service = !empty($_POST['odo_at_last_service']) ? floatval($_POST['odo_at_last_service']) : null;
        $is_primary = isset($_POST['is_primary']) && $_POST['is_primary'] === 'yes' ? 1 : 0;
        
        try {
            // If this is set as primary, remove primary status from other vehicles
            if ($is_primary) {
                $stmt = $pdo->prepare("UPDATE vehicles SET is_primary = 0 WHERE user_id = ? AND id != ?");
                $stmt->execute([$_SESSION['user_id'], $vehicle_id]);
            }
            
            // Update vehicle
            $stmt = $pdo->prepare("UPDATE vehicles SET vehicle_type = ?, model = ?, registration_number = ?, date_of_acquisition = ?, ownership_note = ?, current_odo_reading = ?, current_fuel_amount = ?, fuel_efficiency = ?, last_service_date = ?, odo_at_last_service = ?, is_primary = ? WHERE id = ? AND user_id = ?");
            
            $stmt->execute([
                $vehicle_type,
                $model,
                $registration_number,
                $date_of_acquisition,
                $ownership_note,
                $current_odo_reading,
                $current_fuel_amount,
                $fuel_efficiency,
                $last_service_date,
                $odo_at_last_service,
                $is_primary,
                $vehicle_id,
                $_SESSION['user_id']
            ]);
            
            $success_message = 'Vehicle updated successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to update vehicle. Please try again.';
        }
    } elseif (isset($_POST['delete_vehicle'])) {
        // Handle vehicle deletion
        $vehicle_id = intval($_POST['vehicle_id']);
        
        try {
            $stmt = $pdo->prepare("UPDATE vehicles SET status = 'inactive' WHERE id = ? AND user_id = ?");
            $stmt->execute([$vehicle_id, $_SESSION['user_id']]);
            
            $success_message = 'Vehicle deleted successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to delete vehicle. Please try again.';
        }
    }
}

// Get user's vehicles
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE user_id = ? AND status = 'active' ORDER BY is_primary DESC, created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$vehicles = $stmt->fetchAll();

// Get vehicle for editing if ID is provided
$edit_vehicle = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ? AND user_id = ?");
    $stmt->execute([$edit_id, $_SESSION['user_id']]);
    $edit_vehicle = $stmt->fetch();
}

$page_title = "Vehicle Management";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="bg-success text-white rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-car me-3"></i>
                            Vehicle Management
                        </h1>
                        <p class="mb-0 opacity-75">
                            Add, edit, and manage your fleet of vehicles for running chart calculations and trip tracking.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <a href="running_chart_generator.php" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Add/Edit Vehicle Form -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>
                                <?php echo $edit_vehicle ? 'Edit Vehicle' : 'Add New Vehicle'; ?>
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="">
                                <?php if ($edit_vehicle): ?>
                                <input type="hidden" name="vehicle_id" value="<?php echo $edit_vehicle['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <!-- Vehicle Type -->
                                    <div class="col-md-6 mb-3">
                                        <label for="vehicle_type" class="form-label fw-semibold">
                                            Vehicle Type <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                                            <option value="">Select Vehicle Type</option>
                                            <option value="bike" <?php echo ($edit_vehicle && $edit_vehicle['vehicle_type'] === 'bike') ? 'selected' : ''; ?>>Bike</option>
                                            <option value="three-wheeler" <?php echo ($edit_vehicle && $edit_vehicle['vehicle_type'] === 'three-wheeler') ? 'selected' : ''; ?>>Three-Wheeler</option>
                                            <option value="car" <?php echo ($edit_vehicle && $edit_vehicle['vehicle_type'] === 'car') ? 'selected' : ''; ?>>Car</option>
                                            <option value="cab" <?php echo ($edit_vehicle && $edit_vehicle['vehicle_type'] === 'cab') ? 'selected' : ''; ?>>Cab</option>
                                            <option value="jeep" <?php echo ($edit_vehicle && $edit_vehicle['vehicle_type'] === 'jeep') ? 'selected' : ''; ?>>Jeep</option>
                                            <option value="van" <?php echo ($edit_vehicle && $edit_vehicle['vehicle_type'] === 'van') ? 'selected' : ''; ?>>Van</option>
                                            <option value="lorry" <?php echo ($edit_vehicle && $edit_vehicle['vehicle_type'] === 'lorry') ? 'selected' : ''; ?>>Lorry</option>
                                            <option value="bus" <?php echo ($edit_vehicle && $edit_vehicle['vehicle_type'] === 'bus') ? 'selected' : ''; ?>>Bus</option>
                                        </select>
                                    </div>

                                    <!-- Model -->
                                    <div class="col-md-6 mb-3">
                                        <label for="model" class="form-label fw-semibold">
                                            Model <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="model" name="model" 
                                               value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['model']) : ''; ?>"
                                               placeholder="e.g., Toyota Corolla" required>
                                    </div>

                                    <!-- Registration Number -->
                                    <div class="col-md-6 mb-3">
                                        <label for="registration_number" class="form-label fw-semibold">
                                            Registration Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="registration_number" name="registration_number" 
                                               value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['registration_number']) : ''; ?>"
                                               placeholder="e.g., ABC-1234" required>
                                    </div>

                                    <!-- Date of Acquisition -->
                                    <div class="col-md-6 mb-3">
                                        <label for="date_of_acquisition" class="form-label fw-semibold">
                                            Date of Acquisition <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="date_of_acquisition" name="date_of_acquisition" 
                                               value="<?php echo $edit_vehicle ? $edit_vehicle['date_of_acquisition'] : ''; ?>" required>
                                    </div>

                                    <!-- Current ODO Reading -->
                                    <div class="col-md-6 mb-3">
                                        <label for="current_odo_reading" class="form-label fw-semibold">
                                            Current ODO Reading (Km) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.01" class="form-control" id="current_odo_reading" name="current_odo_reading" 
                                               value="<?php echo $edit_vehicle ? $edit_vehicle['current_odo_reading'] : ''; ?>"
                                               placeholder="0.00" min="0" required>
                                    </div>

                                    <!-- Current Fuel Amount -->
                                    <div class="col-md-6 mb-3">
                                        <label for="current_fuel_amount" class="form-label fw-semibold">
                                            Current Fuel Amount (Liters) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.001" class="form-control" id="current_fuel_amount" name="current_fuel_amount" 
                                               value="<?php echo $edit_vehicle ? $edit_vehicle['current_fuel_amount'] : ''; ?>"
                                               placeholder="0.000" min="0" required>
                                    </div>

                                    <!-- Fuel Efficiency -->
                                    <div class="col-md-6 mb-3">
                                        <label for="fuel_efficiency" class="form-label fw-semibold">
                                            Distance per Liter (Km/L) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.01" class="form-control" id="fuel_efficiency" name="fuel_efficiency" 
                                               value="<?php echo $edit_vehicle ? $edit_vehicle['fuel_efficiency'] : ''; ?>"
                                               placeholder="12.5" min="0.01" required>
                                    </div>

                                    <!-- Last Service Date -->
                                    <div class="col-md-6 mb-3">
                                        <label for="last_service_date" class="form-label fw-semibold">
                                            Last Service Date
                                        </label>
                                        <input type="date" class="form-control" id="last_service_date" name="last_service_date" 
                                               value="<?php echo $edit_vehicle ? $edit_vehicle['last_service_date'] : ''; ?>">
                                    </div>

                                    <!-- ODO at Last Service -->
                                    <div class="col-md-6 mb-3">
                                        <label for="odo_at_last_service" class="form-label fw-semibold">
                                            ODO Reading at Last Service (Km)
                                        </label>
                                        <input type="number" step="0.01" class="form-control" id="odo_at_last_service" name="odo_at_last_service" 
                                               value="<?php echo $edit_vehicle ? $edit_vehicle['odo_at_last_service'] : ''; ?>"
                                               placeholder="0.00" min="0">
                                    </div>

                                    <!-- Make Primary Vehicle -->
                                    <div class="col-md-6 mb-3">
                                        <label for="is_primary" class="form-label fw-semibold">
                                            Make Primary Vehicle
                                        </label>
                                        <select class="form-select" id="is_primary" name="is_primary">
                                            <option value="no" <?php echo (!$edit_vehicle || $edit_vehicle['is_primary'] == 0) ? 'selected' : ''; ?>>No</option>
                                            <option value="yes" <?php echo ($edit_vehicle && $edit_vehicle['is_primary'] == 1) ? 'selected' : ''; ?>>Yes</option>
                                        </select>
                                        <div class="form-text">Primary vehicle will be used for running chart calculations by default.</div>
                                    </div>

                                    <!-- Ownership Note -->
                                    <div class="col-12 mb-3">
                                        <label for="ownership_note" class="form-label fw-semibold">
                                            Ownership Note
                                        </label>
                                        <textarea class="form-control" id="ownership_note" name="ownership_note" rows="3" 
                                                  placeholder="Additional notes about vehicle ownership, condition, etc."><?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['ownership_note']) : ''; ?></textarea>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <?php if ($edit_vehicle): ?>
                                    <a href="add_vehicle.php" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" name="update_vehicle" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Update Vehicle
                                    </button>
                                    <?php else: ?>
                                    <button type="reset" class="btn btn-secondary me-2">Reset Form</button>
                                    <button type="submit" name="add_vehicle" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>Add Vehicle
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicle List -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Your Vehicles (<?php echo count($vehicles); ?>)
                            </h5>
                            <div>
                                <button class="btn btn-outline-primary btn-sm" onclick="exportVehicles()">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($vehicles)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Model</th>
                                            <th>Registration</th>
                                            <th>Current ODO</th>
                                            <th>Fuel Level</th>
                                            <th>Efficiency</th>
                                            <th>Primary</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vehicles as $vehicle): ?>
                                        <tr <?php echo $vehicle['is_primary'] ? 'class="table-warning"' : ''; ?>>
                                            <td>
                                                <i class="fas fa-car text-primary me-2"></i>
                                                <?php echo ucfirst(str_replace('-', ' ', $vehicle['vehicle_type'])); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($vehicle['registration_number']); ?></strong>
                                                <?php if ($vehicle['is_primary']): ?>
                                                <span class="badge bg-warning text-dark ms-1">Primary</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo number_format($vehicle['current_odo_reading'], 2); ?> km</td>
                                            <td><?php echo number_format($vehicle['current_fuel_amount'], 3); ?> L</td>
                                            <td><?php echo number_format($vehicle['fuel_efficiency'], 2); ?> km/L</td>
                                            <td>
                                                <?php if ($vehicle['is_primary']): ?>
                                                <i class="fas fa-star text-warning"></i>
                                                <?php else: ?>
                                                <i class="fas fa-star text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="add_vehicle.php?edit=<?php echo $vehicle['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-info" 
                                                            onclick="viewVehicle(<?php echo $vehicle['id']; ?>)" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteVehicle(<?php echo $vehicle['id']; ?>)" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-car fa-4x text-muted mb-3"></i>
                                <h5>No vehicles added yet</h5>
                                <p class="text-muted">Add your first vehicle to start using the running chart system.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Details Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vehicle Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="vehicleModalBody">
                <!-- Vehicle details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this vehicle? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="vehicle_id" id="deleteVehicleId">
                    <button type="submit" name="delete_vehicle" class="btn btn-danger">Delete Vehicle</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.table-warning {
    --bs-table-bg: rgba(255, 193, 7, 0.1) !important;
}

.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}

@media (max-width: 768px) {
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
}
</style>

<script>
function viewVehicle(vehicleId) {
    // Fetch vehicle details and display in modal
    const vehicles = <?php echo json_encode($vehicles); ?>;
    const vehicle = vehicles.find(v => v.id == vehicleId);
    
    if (vehicle) {
        const modalBody = document.getElementById('vehicleModalBody');
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">Basic Information</h6>
                    <table class="table table-sm">
                        <tr><th>Type:</th><td>${vehicle.vehicle_type.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}</td></tr>
                        <tr><th>Model:</th><td>${vehicle.model}</td></tr>
                        <tr><th>Registration:</th><td><strong>${vehicle.registration_number}</strong></td></tr>
                        <tr><th>Acquired:</th><td>${vehicle.date_of_acquisition}</td></tr>
                        <tr><th>Primary:</th><td>${vehicle.is_primary ? 'Yes' : 'No'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Current Status</h6>
                    <table class="table table-sm">
                        <tr><th>ODO Reading:</th><td>${parseFloat(vehicle.current_odo_reading).toFixed(2)} km</td></tr>
                        <tr><th>Fuel Level:</th><td>${parseFloat(vehicle.current_fuel_amount).toFixed(3)} L</td></tr>
                        <tr><th>Efficiency:</th><td>${parseFloat(vehicle.fuel_efficiency).toFixed(2)} km/L</td></tr>
                        <tr><th>Last Service:</th><td>${vehicle.last_service_date || 'Not recorded'}</td></tr>
                        <tr><th>ODO @ Service:</th><td>${vehicle.odo_at_last_service ? parseFloat(vehicle.odo_at_last_service).toFixed(2) + ' km' : 'Not recorded'}</td></tr>
                    </table>
                </div>
                ${vehicle.ownership_note ? `<div class="col-12 mt-3"><h6>Notes:</h6><p class="bg-light p-3 rounded">${vehicle.ownership_note}</p></div>` : ''}
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('vehicleModal'));
        modal.show();
    }
}

function deleteVehicle(vehicleId) {
    document.getElementById('deleteVehicleId').value = vehicleId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function exportVehicles() {
    // Simple CSV export functionality
    const vehicles = <?php echo json_encode($vehicles); ?>;
    let csv = 'Type,Model,Registration,ODO Reading,Fuel Level,Efficiency,Primary\n';
    
    vehicles.forEach(vehicle => {
        csv += `"${vehicle.vehicle_type}","${vehicle.model}","${vehicle.registration_number}",${vehicle.current_odo_reading},${vehicle.current_fuel_amount},${vehicle.fuel_efficiency},"${vehicle.is_primary ? 'Yes' : 'No'}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'vehicles_export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const odoReading = parseFloat(document.getElementById('current_odo_reading').value);
            const fuelAmount = parseFloat(document.getElementById('current_fuel_amount').value);
            const efficiency = parseFloat(document.getElementById('fuel_efficiency').value);
            
            if (odoReading < 0 || fuelAmount < 0 || efficiency <= 0) {
                e.preventDefault();
                alert('Please enter valid positive numbers for ODO reading, fuel amount, and efficiency.');
                return false;
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>