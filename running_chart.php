<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'running_chart.php';
    header('Location: login.php');
    exit();
}

// Get primary vehicle
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE user_id = ? AND is_primary = 1 AND status = 'active'");
$stmt->execute([$_SESSION['user_id']]);
$primary_vehicle = $stmt->fetch();

if (!$primary_vehicle) {
    $_SESSION['error_message'] = 'Please set a primary vehicle first before using the running chart.';
    header('Location: add_vehicle.php');
    exit();
}

$error_message = '';
$success_message = '';
$calculation_result = null;

// Handle calculation submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is a duplicate submission
    $is_duplicate = false;
    if (isset($_SESSION['last_post']) && $_SESSION['last_post'] === $_POST) {
        $is_duplicate = true;
    }
    $_SESSION['last_post'] = $_POST;
    
    if (!$is_duplicate) {
        if (isset($_POST['calculate_fuel_burned'])) {
            $calculation_type = cleanInput($_POST['calculation_type']);
            $fuel_refilled = floatval($_POST['fuel_refilled']);
            $input_value = floatval($_POST['input_value']);
            
            $initial_odo = $primary_vehicle['current_odo_reading'];
            $initial_fuel = $primary_vehicle['current_fuel_amount'];
            $efficiency = $primary_vehicle['fuel_efficiency'];
            
            if ($calculation_type === 'distance_driven') {
                $distance_driven = $input_value;
                $final_odo = $initial_odo + $distance_driven;
                $fuel_consumed = $distance_driven / $efficiency;
                $final_fuel = $initial_fuel + $fuel_refilled - $fuel_consumed;
                
                $calculation_result = [
                    'type' => 'distance_driven',
                    'distance_driven' => $distance_driven,
                    'final_odo' => $final_odo,
                    'fuel_consumed' => $fuel_consumed,
                    'final_fuel' => max(0, $final_fuel),
                    'fuel_refilled' => $fuel_refilled
                ];
            } elseif ($calculation_type === 'odo_entered') {
                $final_odo = $input_value;
                if ($final_odo <= $initial_odo) {
                    $error_message = 'Final ODO reading must be greater than current ODO reading.';
                } else {
                    $distance_driven = $final_odo - $initial_odo;
                    $fuel_consumed = $distance_driven / $efficiency;
                    $final_fuel = $initial_fuel + $fuel_refilled - $fuel_consumed;
                    
                    $calculation_result = [
                        'type' => 'odo_entered',
                        'distance_driven' => $distance_driven,
                        'final_odo' => $final_odo,
                        'fuel_consumed' => $fuel_consumed,
                        'final_fuel' => max(0, $final_fuel),
                        'fuel_refilled' => $fuel_refilled
                    ];
                }
            }
        } elseif (isset($_POST['calculate_distance_for_fuel'])) {
            $fuel_to_burn = floatval($_POST['fuel_to_burn']);
            $fuel_in_tank = floatval($_POST['fuel_in_tank']);
            
            $efficiency = $primary_vehicle['fuel_efficiency'];
            $initial_odo = $primary_vehicle['current_odo_reading'];
            
            $distance_to_drive = $fuel_to_burn * $efficiency;
            $final_fuel = $fuel_in_tank - $fuel_to_burn;
            $final_odo = $initial_odo + $distance_to_drive;
            
            $calculation_result = [
                'type' => 'fuel_specified',
                'fuel_to_burn' => $fuel_to_burn,
                'distance_to_drive' => $distance_to_drive,
                'final_fuel' => max(0, $final_fuel),
                'final_odo' => $final_odo
            ];
        } elseif (isset($_POST['calculate_distance_to_save_fuel'])) {
            $fuel_to_save = floatval($_POST['fuel_to_save']);
            
            $initial_fuel = $primary_vehicle['current_fuel_amount'];
            $efficiency = $primary_vehicle['fuel_efficiency'];
            $initial_odo = $primary_vehicle['current_odo_reading'];
            
            if ($fuel_to_save >= $initial_fuel) {
                $error_message = 'Fuel to save cannot be greater than or equal to current fuel amount.';
            } else {
                $fuel_to_burn = $initial_fuel - $fuel_to_save;
                $distance_to_drive = $fuel_to_burn * $efficiency;
                $final_odo = $initial_odo + $distance_to_drive;
                
                $calculation_result = [
                    'type' => 'fuel_to_save',
                    'fuel_to_save' => $fuel_to_save,
                    'fuel_to_burn' => $fuel_to_burn,
                    'distance_to_drive' => $distance_to_drive,
                    'final_fuel' => $fuel_to_save,
                    'final_odo' => $final_odo
                ];
            }
        } elseif (isset($_POST['save_for_next_round'])) {
            // Save trip entry and update vehicle
            $trip_data = json_decode($_POST['trip_data'], true);
            
            try {
                $pdo->beginTransaction();
                
                // Insert trip entry
                $stmt = $pdo->prepare("INSERT INTO trip_entries (user_id, vehicle_id, trip_date, calculation_type, initial_odo_reading, initial_fuel_amount, distance_driven, final_odo_reading, fuel_refilled, fuel_consumed, final_fuel_amount, input_distance, input_fuel_amount, input_fuel_to_save, trip_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $_SESSION['user_id'],
                    $primary_vehicle['id'],
                    date('Y-m-d'),
                    $trip_data['type'],
                    $primary_vehicle['current_odo_reading'],
                    $primary_vehicle['current_fuel_amount'],
                    $trip_data['distance_driven'] ?? null,
                    $trip_data['final_odo'],
                    $trip_data['fuel_refilled'] ?? 0,
                    $trip_data['fuel_consumed'] ?? $trip_data['fuel_to_burn'] ?? 0,
                    $trip_data['final_fuel'],
                    $trip_data['distance_driven'] ?? $trip_data['distance_to_drive'] ?? null,
                    $trip_data['fuel_to_burn'] ?? null,
                    $trip_data['fuel_to_save'] ?? null,
                    cleanInput($_POST['trip_notes'])
                ]);
                
                // Update vehicle's current readings
                $stmt = $pdo->prepare("UPDATE vehicles SET current_odo_reading = ?, current_fuel_amount = ? WHERE id = ?");
                $stmt->execute([
                    $trip_data['final_odo'],
                    $trip_data['final_fuel'],
                    $primary_vehicle['id']
                ]);
                
                $pdo->commit();
                
                $success_message = 'Trip saved successfully! Vehicle readings have been updated.';
                
                // Refresh primary vehicle data
                $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
                $stmt->execute([$primary_vehicle['id']]);
                $primary_vehicle = $stmt->fetch();
                
                $calculation_result = null;
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = 'Failed to save trip. Please try again.';
            }
        }
    } else {
        $error_message = 'Duplicate submission detected. Please perform a new calculation.';
    }
}

$page_title = "Running Chart";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="bg-primary text-white rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-calculator me-3"></i>
                            Running Chart Calculator
                        </h1>
                        <p class="mb-0 opacity-75">
                            Calculate fuel consumption, track mileage, and maintain trip records for your primary vehicle.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <a href="running_chart_generator.php" class="btn btn-outline-light me-2">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                        <a href="add_vehicle.php" class="btn btn-outline-light">
                            <i class="fas fa-cog me-1"></i>Manage Vehicles
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

            <!-- Primary Vehicle Info -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-star me-2"></i>
                                Primary Vehicle: <?php echo htmlspecialchars($primary_vehicle['registration_number']); ?>
                                (<?php echo htmlspecialchars($primary_vehicle['model']); ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-lg-4 col-md-4 mb-3">
                                    <div class="p-3">
                                        <h3 class="text-primary mb-1"><?php echo number_format($primary_vehicle['current_odo_reading'], 2); ?></h3>
                                        <small class="text-muted">A. ODO Reading (Km)</small>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 mb-3">
                                    <div class="p-3">
                                        <h3 class="text-success mb-1"><?php echo number_format($primary_vehicle['fuel_efficiency'], 2); ?></h3>
                                        <small class="text-muted">B. Distance per Liter (Km/L)</small>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 mb-3">
                                    <div class="p-3">
                                        <h3 class="text-info mb-1"><?php echo number_format($primary_vehicle['current_fuel_amount'], 3); ?></h3>
                                        <small class="text-muted">C. Fuel in Tank (Liters)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calculation Sections -->
            <div class="row g-4">
                <!-- Section 1: Calculate Fuel Burned -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-gas-pump me-2"></i>
                                1. Calculate Amount of Fuel Burned
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="fuelBurnedForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">D. Fuel Refilled During Trip (Liters)</label>
                                        <input type="number" step="0.001" class="form-control" name="fuel_refilled" 
                                               placeholder="0.000" min="0" value="0">
                                        <div class="form-text">Enter amount if you refueled during the trip</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">E. Calculation Method</label>
                                        <select class="form-select" name="calculation_type" id="calculation_type" required>
                                            <option value="">Select calculation method</option>
                                            <option value="distance_driven">Distance Driven (Km)</option>
                                            <option value="odo_entered">ODO Reading After Trip</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold" id="input_label">Input Value</label>
                                        <input type="number" step="0.01" class="form-control" name="input_value" 
                                               id="input_value" placeholder="Enter value" min="0" required>
                                        <div class="form-text" id="input_help">Select calculation method first</div>
                                    </div>
                                </div>
                                <button type="submit" name="calculate_fuel_burned" class="btn btn-primary">
                                    <i class="fas fa-calculator me-2"></i>Calculate
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Calculate Distance for Fuel Amount -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-route me-2"></i>
                                2. Calculate Distance for Fuel Amount
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="distanceForFuelForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">F. Amount of Fuel to Burn (Liters)</label>
                                        <input type="number" step="0.001" class="form-control" name="fuel_to_burn" 
                                               placeholder="0.000" min="0.001" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">I. Amount of Fuel in Tank (Liters)</label>
                                        <input type="number" step="0.001" class="form-control" name="fuel_in_tank" 
                                               value="<?php echo $primary_vehicle['current_fuel_amount']; ?>" required>
                                        <div class="form-text">Current tank level pre-filled</div>
                                    </div>
                                </div>
                                <button type="submit" name="calculate_distance_for_fuel" class="btn btn-success">
                                    <i class="fas fa-calculator me-2"></i>Calculate Distance
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Calculate Distance to Save Fuel -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-save me-2"></i>
                                3. Calculate Distance to Save Fuel
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="distanceToSaveForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">F. Amount of Fuel to Save in Tank (Liters)</label>
                                        <input type="number" step="0.001" class="form-control" name="fuel_to_save" 
                                               placeholder="0.000" min="0.001" 
                                               max="<?php echo $primary_vehicle['current_fuel_amount'] - 0.001; ?>" required>
                                        <div class="form-text">Maximum: <?php echo number_format($primary_vehicle['current_fuel_amount'], 3); ?> L</div>
                                    </div>
                                </div>
                                <button type="submit" name="calculate_distance_to_save_fuel" class="btn btn-info">
                                    <i class="fas fa-calculator me-2"></i>Calculate Distance to Save Fuel
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calculation Result Modal -->
<div class="modal fade" id="calculationResultModal" tabindex="-1" aria-labelledby="calculationResultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="calculationResultModalLabel">
                    <i class="fas fa-chart-line me-2"></i>
                    Calculation Results
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="resultContent">
                    <!-- Result content will be inserted here by JavaScript -->
                </div>
                
                <!-- Save for Next Round -->
                <div class="mt-4 p-3 border rounded">
                    <h6 class="mb-3">Save Trip Entry</h6>
                    <form method="POST" action="" id="saveTripForm">
                        <input type="hidden" name="trip_data" id="tripDataInput">
                        <div class="mb-3">
                            <label class="form-label">Trip Notes (Optional)</label>
                            <textarea class="form-control" name="trip_notes" rows="2" 
                                      placeholder="Add any notes about this trip..."></textarea>
                        </div>
                        <button type="submit" name="save_for_next_round" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>Save for Next Round
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg ms-2" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Close
                        </button>
                        <small class="text-muted ms-3 d-block mt-2">This will update your vehicle's ODO and fuel readings</small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.result-item {
    transition: all 0.3s ease;
    border-left: 4px solid #198754;
    background-color: rgba(25, 135, 84, 0.05);
}

.result-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.calculation-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.1);
}

.calculation-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.calculation-card .card-header {
    border-bottom: none;
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .result-item h4 {
        font-size: 1.5rem;
    }
}

/* Animation for modal */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-content {
    animation: fadeIn 0.3s ease-out;
}

/* Pulse animation for calculate buttons */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn-primary, .btn-success, .btn-info {
    animation: pulse 2s infinite;
}

.btn-primary:hover, .btn-success:hover, .btn-info:hover {
    animation: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculation type selector
    const calculationType = document.getElementById('calculation_type');
    if (calculationType) {
        calculationType.addEventListener('change', function() {
            const calculationTypeValue = this.value;
            const inputLabel = document.getElementById('input_label');
            const inputHelp = document.getElementById('input_help');
            const inputValue = document.getElementById('input_value');
            
            if (calculationTypeValue === 'distance_driven') {
                inputLabel.textContent = 'Distance Driven (Km)';
                inputHelp.textContent = 'Enter the distance you drove in kilometers';
                inputValue.placeholder = 'Enter distance in km';
                inputValue.step = '0.01';
            } else if (calculationTypeValue === 'odo_entered') {
                inputLabel.textContent = 'ODO Reading After Trip (Km)';
                inputHelp.textContent = 'Enter the ODO reading shown after your trip';
                inputValue.placeholder = 'Enter ODO reading';
                inputValue.step = '0.01';
            } else {
                inputLabel.textContent = 'Input Value';
                inputHelp.textContent = 'Select calculation method first';
                inputValue.placeholder = 'Enter value';
            }
        });
    }

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const numberInputs = form.querySelectorAll('input[type="number"]');
            let isValid = true;
            
            numberInputs.forEach(input => {
                if (input.required && (input.value === '' || parseFloat(input.value) < 0)) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields with valid positive numbers.');
            }
        });
    });

    // Check if we should show the modal
    <?php if ($calculation_result): ?>
        showResultModal(<?php echo json_encode($calculation_result); ?>);
    <?php endif; ?>
});

function showResultModal(result) {
    const modal = new bootstrap.Modal(document.getElementById('calculationResultModal'));
    const resultContent = document.getElementById('resultContent');
    const tripDataInput = document.getElementById('tripDataInput');
    
    // Set the trip data for the save form
    tripDataInput.value = JSON.stringify(result);
    
    // Clear previous content
    resultContent.innerHTML = '';
    
    // Create content based on result type
    if (result.type === 'distance_driven' || result.type === 'odo_entered') {
        // Create HTML for distance_driven or odo_entered results
        let html = `
            <div class="col-md-6 mb-3">
                <div class="result-item p-3 rounded">
                    <h6 class="text-primary">Final ODO Reading</h6>
                    <h4>${result.final_odo.toFixed(2)} km</h4>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="result-item p-3 rounded">
                    <h6 class="text-danger">Fuel Consumed</h6>
                    <h4>${result.fuel_consumed.toFixed(3)} L</h4>
                </div>
            </div>`;
            
        if (result.type === 'odo_entered') {
            html += `
            <div class="col-md-6 mb-3">
                <div class="result-item p-3 rounded">
                    <h6 class="text-info">Distance Driven</h6>
                    <h4>${result.distance_driven.toFixed(2)} km</h4>
                </div>
            </div>`;
        }
        
        html += `
            <div class="col-md-6 mb-3">
                <div class="result-item p-3 rounded">
                    <h6 class="text-success">Remaining Fuel</h6>
                    <h4>${result.final_fuel.toFixed(3)} L</h4>`;
                    
        if (result.fuel_refilled > 0) {
            html += `<small class="text-muted">(includes ${result.fuel_refilled.toFixed(3)}L refill)</small>`;
        }
        
        html += `</div></div>`;
        
        resultContent.innerHTML = html;
        
    } else if (result.type === 'fuel_specified') {
        resultContent.innerHTML = `
            <div class="col-md-4 mb-3">
                <div class="result-item p-3 rounded">
                    <h6 class="text-primary">Distance to Drive</h6>
                    <h4>${result.distance_to_drive.toFixed(2)} km</h4>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="result-item p-3 rounded">
                    <h6 class="text-success">Remaining Fuel</h6>
                    <h4>${result.final_fuel.toFixed(3)} L</h4>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="result-item p-3 rounded">
                    <h6 class="text-info">Final ODO Reading</h6>
                    <h4>${result.final_odo.toFixed(2)} km</h4>
                </div>
            </div>`;
            
    } else if (result.type === 'fuel_to_save') {
        resultContent.innerHTML = `
            <div class="col-md-4 mb-3">
                <div class="result-item p-3 rounded">
                    <h6 class="text-primary">Distance to Drive</h6>
                    <h4>${result.distance_to_drive.toFixed(2)} km</h4>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="result-item p-3 rounded">
                    <h6 class="text-warning">Fuel to Burn</h6>
                    <h4>${result.fuel_to_burn.toFixed(3)} L</h4>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="result-item p-3 rounded">
                    <h6 class="text-info">Final ODO Reading</h6>
                    <h4>${result.final_odo.toFixed(2)} km</h4>
                </div>
            </div>`;
    }
    
    // Show the modal
    modal.show();
    
    // Focus on the notes field when modal is shown
    document.getElementById('calculationResultModal').addEventListener('shown.bs.modal', function() {
        document.querySelector('#saveTripForm textarea').focus();
    });
}
</script>

<?php include 'includes/footer.php'; ?>