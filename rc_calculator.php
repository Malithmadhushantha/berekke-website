<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'rc_calculator.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();

// Get user's vehicles for dropdown
$stmt = $pdo->prepare("SELECT id, registration_number, model, fuel_efficiency FROM vehicles WHERE user_id = ? AND status = 'active' ORDER BY is_primary DESC, registration_number ASC");
$stmt->execute([$_SESSION['user_id']]);
$user_vehicles = $stmt->fetchAll();

$page_title = "R/C Calculator";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="bg-warning text-dark rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-calculator me-3"></i>
                            R/C Calculator
                        </h1>
                        <p class="mb-0 opacity-75">
                            Hi <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! 
                            Perform quick calculations for fuel consumption, distance planning, cost analysis, and trip optimization.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <a href="running_chart_generator.php" class="btn btn-outline-dark">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calculator Tabs -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <ul class="nav nav-tabs card-header-tabs" id="calculatorTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="fuel-calc-tab" data-bs-toggle="tab" 
                                            data-bs-target="#fuel-calc" type="button" role="tab">
                                        <i class="fas fa-gas-pump me-2"></i>Fuel Calculator
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="distance-calc-tab" data-bs-toggle="tab" 
                                            data-bs-target="#distance-calc" type="button" role="tab">
                                        <i class="fas fa-route me-2"></i>Distance Calculator
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="cost-calc-tab" data-bs-toggle="tab" 
                                            data-bs-target="#cost-calc" type="button" role="tab">
                                        <i class="fas fa-dollar-sign me-2"></i>Cost Calculator
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="efficiency-calc-tab" data-bs-toggle="tab" 
                                            data-bs-target="#efficiency-calc" type="button" role="tab">
                                        <i class="fas fa-chart-line me-2"></i>Efficiency Calculator
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="trip-calc-tab" data-bs-toggle="tab" 
                                            data-bs-target="#trip-calc" type="button" role="tab">
                                        <i class="fas fa-map-marked-alt me-2"></i>Trip Planner
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="calculatorTabContent">
                                
                                <!-- Fuel Calculator Tab -->
                                <div class="tab-pane fade show active" id="fuel-calc" role="tabpanel">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <h5 class="mb-4">
                                                <i class="fas fa-gas-pump text-primary me-2"></i>
                                                Fuel Consumption Calculator
                                            </h5>
                                            
                                            <!-- Quick Vehicle Select -->
                                            <?php if (!empty($user_vehicles)): ?>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Quick Select Vehicle</label>
                                                <select class="form-select" id="quickVehicleSelect" onchange="loadVehicleData(this.value, 'fuel')">
                                                    <option value="">Manual Entry</option>
                                                    <?php foreach ($user_vehicles as $vehicle): ?>
                                                    <option value="<?php echo $vehicle['id']; ?>" 
                                                            data-efficiency="<?php echo $vehicle['fuel_efficiency']; ?>">
                                                        <?php echo htmlspecialchars($vehicle['registration_number'] . ' - ' . $vehicle['model']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Distance Driven (km)</label>
                                                <input type="number" step="0.01" class="form-control" id="fuelDistance" 
                                                       placeholder="Enter distance" min="0">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fuel Efficiency (km/L)</label>
                                                <input type="number" step="0.01" class="form-control" id="fuelEfficiency" 
                                                       placeholder="Enter efficiency" min="0.1">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fuel Price per Liter (Rs.)</label>
                                                <input type="number" step="0.01" class="form-control" id="fuelPrice" 
                                                       placeholder="Enter fuel price" min="0" value="350.00">
                                            </div>
                                            
                                            <button class="btn btn-primary w-100" onclick="calculateFuel()">
                                                <i class="fas fa-calculator me-2"></i>Calculate Fuel Consumption
                                            </button>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                            <div class="result-panel bg-light rounded p-4">
                                                <h6 class="mb-3">Calculation Results</h6>
                                                <div id="fuelResults">
                                                    <div class="text-center text-muted">
                                                        <i class="fas fa-calculator fa-3x mb-3"></i>
                                                        <p>Enter values and click calculate to see results</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Distance Calculator Tab -->
                                <div class="tab-pane fade" id="distance-calc" role="tabpanel">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <h5 class="mb-4">
                                                <i class="fas fa-route text-success me-2"></i>
                                                Distance Calculator
                                            </h5>
                                            
                                            <!-- Quick Vehicle Select -->
                                            <?php if (!empty($user_vehicles)): ?>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Quick Select Vehicle</label>
                                                <select class="form-select" id="quickVehicleSelectDistance" onchange="loadVehicleData(this.value, 'distance')">
                                                    <option value="">Manual Entry</option>
                                                    <?php foreach ($user_vehicles as $vehicle): ?>
                                                    <option value="<?php echo $vehicle['id']; ?>" 
                                                            data-efficiency="<?php echo $vehicle['fuel_efficiency']; ?>">
                                                        <?php echo htmlspecialchars($vehicle['registration_number'] . ' - ' . $vehicle['model']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Available Fuel (Liters)</label>
                                                <input type="number" step="0.001" class="form-control" id="availableFuel" 
                                                       placeholder="Enter fuel amount" min="0">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fuel Efficiency (km/L)</label>
                                                <input type="number" step="0.01" class="form-control" id="distanceEfficiency" 
                                                       placeholder="Enter efficiency" min="0.1">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Reserve Fuel (Liters)</label>
                                                <input type="number" step="0.001" class="form-control" id="reserveFuel" 
                                                       placeholder="Fuel to keep in reserve" min="0" value="5.000">
                                            </div>
                                            
                                            <button class="btn btn-success w-100" onclick="calculateDistance()">
                                                <i class="fas fa-calculator me-2"></i>Calculate Maximum Distance
                                            </button>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                            <div class="result-panel bg-light rounded p-4">
                                                <h6 class="mb-3">Calculation Results</h6>
                                                <div id="distanceResults">
                                                    <div class="text-center text-muted">
                                                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                                                        <p>Enter values and click calculate to see results</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cost Calculator Tab -->
                                <div class="tab-pane fade" id="cost-calc" role="tabpanel">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <h5 class="mb-4">
                                                <i class="fas fa-dollar-sign text-warning me-2"></i>
                                                Trip Cost Calculator
                                            </h5>
                                            
                                            <!-- Quick Vehicle Select -->
                                            <?php if (!empty($user_vehicles)): ?>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Quick Select Vehicle</label>
                                                <select class="form-select" id="quickVehicleSelectCost" onchange="loadVehicleData(this.value, 'cost')">
                                                    <option value="">Manual Entry</option>
                                                    <?php foreach ($user_vehicles as $vehicle): ?>
                                                    <option value="<?php echo $vehicle['id']; ?>" 
                                                            data-efficiency="<?php echo $vehicle['fuel_efficiency']; ?>">
                                                        <?php echo htmlspecialchars($vehicle['registration_number'] . ' - ' . $vehicle['model']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Trip Distance (km)</label>
                                                <input type="number" step="0.01" class="form-control" id="tripDistance" 
                                                       placeholder="Enter total distance" min="0">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fuel Efficiency (km/L)</label>
                                                <input type="number" step="0.01" class="form-control" id="costEfficiency" 
                                                       placeholder="Enter efficiency" min="0.1">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fuel Price per Liter (Rs.)</label>
                                                <input type="number" step="0.01" class="form-control" id="costFuelPrice" 
                                                       placeholder="Enter fuel price" min="0" value="350.00">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Additional Costs (Rs.)</label>
                                                <input type="number" step="0.01" class="form-control" id="additionalCosts" 
                                                       placeholder="Tolls, parking, etc." min="0" value="0">
                                            </div>
                                            
                                            <button class="btn btn-warning w-100" onclick="calculateCost()">
                                                <i class="fas fa-calculator me-2"></i>Calculate Trip Cost
                                            </button>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                            <div class="result-panel bg-light rounded p-4">
                                                <h6 class="mb-3">Cost Breakdown</h6>
                                                <div id="costResults">
                                                    <div class="text-center text-muted">
                                                        <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                                                        <p>Enter values and click calculate to see cost breakdown</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Efficiency Calculator Tab -->
                                <div class="tab-pane fade" id="efficiency-calc" role="tabpanel">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <h5 class="mb-4">
                                                <i class="fas fa-chart-line text-info me-2"></i>
                                                Fuel Efficiency Calculator
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Distance Traveled (km)</label>
                                                <input type="number" step="0.01" class="form-control" id="efficiencyDistance" 
                                                       placeholder="Enter distance traveled" min="0">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fuel Used (Liters)</label>
                                                <input type="number" step="0.001" class="form-control" id="fuelUsed" 
                                                       placeholder="Enter fuel consumed" min="0">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Calculation Type</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="efficiencyType" 
                                                           id="kmPerLiter" value="kmpl" checked>
                                                    <label class="form-check-label" for="kmPerLiter">
                                                        Kilometers per Liter (km/L)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="efficiencyType" 
                                                           id="litersPerKm" value="lpkm">
                                                    <label class="form-check-label" for="litersPerKm">
                                                        Liters per 100 Kilometers (L/100km)
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <button class="btn btn-info w-100" onclick="calculateEfficiency()">
                                                <i class="fas fa-calculator me-2"></i>Calculate Efficiency
                                            </button>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                            <div class="result-panel bg-light rounded p-4">
                                                <h6 class="mb-3">Efficiency Results</h6>
                                                <div id="efficiencyResults">
                                                    <div class="text-center text-muted">
                                                        <i class="fas fa-tachometer-alt fa-3x mb-3"></i>
                                                        <p>Enter values and click calculate to see efficiency</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Trip Planner Tab -->
                                <div class="tab-pane fade" id="trip-calc" role="tabpanel">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <h5 class="mb-4">
                                                <i class="fas fa-map-marked-alt text-danger me-2"></i>
                                                Trip Planner
                                            </h5>
                                            
                                            <!-- Quick Vehicle Select -->
                                            <?php if (!empty($user_vehicles)): ?>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Quick Select Vehicle</label>
                                                <select class="form-select" id="quickVehicleSelectTrip" onchange="loadVehicleData(this.value, 'trip')">
                                                    <option value="">Manual Entry</option>
                                                    <?php foreach ($user_vehicles as $vehicle): ?>
                                                    <option value="<?php echo $vehicle['id']; ?>" 
                                                            data-efficiency="<?php echo $vehicle['fuel_efficiency']; ?>">
                                                        <?php echo htmlspecialchars($vehicle['registration_number'] . ' - ' . $vehicle['model']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Total Trip Distance (km)</label>
                                                <input type="number" step="0.01" class="form-control" id="totalTripDistance" 
                                                       placeholder="Round trip distance" min="0">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Current Fuel in Tank (Liters)</label>
                                                <input type="number" step="0.001" class="form-control" id="currentFuelAmount" 
                                                       placeholder="Fuel currently available" min="0">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fuel Efficiency (km/L)</label>
                                                <input type="number" step="0.01" class="form-control" id="tripEfficiency" 
                                                       placeholder="Vehicle efficiency" min="0.1">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Safety Buffer (%)</label>
                                                <input type="number" step="1" class="form-control" id="safetyBuffer" 
                                                       placeholder="Extra fuel percentage" min="0" max="50" value="20">
                                            </div>
                                            
                                            <button class="btn btn-danger w-100" onclick="calculateTrip()">
                                                <i class="fas fa-calculator me-2"></i>Plan Trip
                                            </button>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                            <div class="result-panel bg-light rounded p-4">
                                                <h6 class="mb-3">Trip Planning Results</h6>
                                                <div id="tripResults">
                                                    <div class="text-center text-muted">
                                                        <i class="fas fa-route fa-3x mb-3"></i>
                                                        <p>Enter trip details and click plan to see recommendations</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Reference Cards -->
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-lightbulb fa-2x text-warning mb-3"></i>
                            <h6>Quick Tip</h6>
                            <p class="text-muted small mb-0">
                                For accurate calculations, use actual fuel efficiency from your vehicle's recent trips.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-leaf fa-2x text-success mb-3"></i>
                            <h6>Eco Driving</h6>
                            <p class="text-muted small mb-0">
                                Maintain steady speeds and avoid rapid acceleration to improve fuel efficiency.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-tools fa-2x text-primary mb-3"></i>
                            <h6>Maintenance</h6>
                            <p class="text-muted small mb-0">
                                Regular vehicle maintenance can improve fuel efficiency by up to 10%.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.result-panel {
    min-height: 400px;
    border: 2px dashed #dee2e6;
}

.result-item {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.result-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #0d6efd;
}

.result-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 3px solid #0d6efd;
    background: none;
}

.alert-tip {
    background: linear-gradient(45deg, #fff3cd, #ffeaa7);
    border: none;
    border-left: 4px solid #ffc107;
}

@media (max-width: 768px) {
    .nav-tabs {
        flex-direction: column;
    }
    
    .nav-tabs .nav-link {
        text-align: center;
        border-bottom: 1px solid #dee2e6;
    }
    
    .result-panel {
        min-height: 300px;
        margin-top: 2rem;
    }
}
</style>

<script>
// Load vehicle data into form fields
function loadVehicleData(vehicleId, calculatorType) {
    if (!vehicleId) return;
    
    const select = document.querySelector(`#quickVehicleSelect${calculatorType === 'fuel' ? '' : calculatorType.charAt(0).toUpperCase() + calculatorType.slice(1)}`);
    const option = select.querySelector(`option[value="${vehicleId}"]`);
    
    if (option) {
        const efficiency = option.dataset.efficiency;
        
        // Set efficiency in the appropriate field
        const efficiencyFields = {
            'fuel': 'fuelEfficiency',
            'distance': 'distanceEfficiency', 
            'cost': 'costEfficiency',
            'trip': 'tripEfficiency'
        };
        
        const fieldId = efficiencyFields[calculatorType];
        if (fieldId) {
            document.getElementById(fieldId).value = efficiency;
        }
    }
}

// Fuel Calculator
function calculateFuel() {
    const distance = parseFloat(document.getElementById('fuelDistance').value);
    const efficiency = parseFloat(document.getElementById('fuelEfficiency').value);
    const price = parseFloat(document.getElementById('fuelPrice').value);
    
    if (!distance || !efficiency || !price) {
        alert('Please fill in all required fields.');
        return;
    }
    
    const fuelNeeded = distance / efficiency;
    const cost = fuelNeeded * price;
    const costPerKm = cost / distance;
    
    const results = `
        <div class="result-item">
            <div class="result-label">Fuel Required</div>
            <div class="result-value">${fuelNeeded.toFixed(3)} L</div>
        </div>
        <div class="result-item">
            <div class="result-label">Total Cost</div>
            <div class="result-value">Rs. ${cost.toFixed(2)}</div>
        </div>
        <div class="result-item">
            <div class="result-label">Cost per Kilometer</div>
            <div class="result-value">Rs. ${costPerKm.toFixed(2)}</div>
        </div>
        <div class="alert alert-tip mt-3">
            <small><i class="fas fa-info-circle me-1"></i>
            Based on ${efficiency} km/L efficiency and Rs. ${price}/L fuel price</small>
        </div>
    `;
    
    document.getElementById('fuelResults').innerHTML = results;
}

// Distance Calculator
function calculateDistance() {
    const fuel = parseFloat(document.getElementById('availableFuel').value);
    const efficiency = parseFloat(document.getElementById('distanceEfficiency').value);
    const reserve = parseFloat(document.getElementById('reserveFuel').value);
    
    if (!fuel || !efficiency) {
        alert('Please fill in required fields.');
        return;
    }
    
    const usableFuel = Math.max(0, fuel - (reserve || 0));
    const maxDistance = usableFuel * efficiency;
    const safeDistance = maxDistance * 0.9; // 90% for safety
    
    const results = `
        <div class="result-item">
            <div class="result-label">Maximum Distance</div>
            <div class="result-value">${maxDistance.toFixed(2)} km</div>
        </div>
        <div class="result-item">
            <div class="result-label">Safe Distance (90%)</div>
            <div class="result-value">${safeDistance.toFixed(2)} km</div>
        </div>
        <div class="result-item">
            <div class="result-label">Usable Fuel</div>
            <div class="result-value">${usableFuel.toFixed(3)} L</div>
        </div>
        <div class="alert alert-tip mt-3">
            <small><i class="fas fa-shield-alt me-1"></i>
            Safe distance calculation keeps ${(reserve || 0).toFixed(1)}L in reserve</small>
        </div>
    `;
    
    document.getElementById('distanceResults').innerHTML = results;
}

// Cost Calculator
function calculateCost() {
    const distance = parseFloat(document.getElementById('tripDistance').value);
    const efficiency = parseFloat(document.getElementById('costEfficiency').value);
    const price = parseFloat(document.getElementById('costFuelPrice').value);
    const additional = parseFloat(document.getElementById('additionalCosts').value) || 0;
    
    if (!distance || !efficiency || !price) {
        alert('Please fill in all required fields.');
        return;
    }
    
    const fuelNeeded = distance / efficiency;
    const fuelCost = fuelNeeded * price;
    const totalCost = fuelCost + additional;
    
    const results = `
        <div class="result-item">
            <div class="result-label">Fuel Cost</div>
            <div class="result-value">Rs. ${fuelCost.toFixed(2)}</div>
        </div>
        <div class="result-item">
            <div class="result-label">Additional Costs</div>
            <div class="result-value">Rs. ${additional.toFixed(2)}</div>
        </div>
        <div class="result-item">
            <div class="result-label">Total Trip Cost</div>
            <div class="result-value text-danger">Rs. ${totalCost.toFixed(2)}</div>
        </div>
        <div class="result-item">
            <div class="result-label">Fuel Required</div>
            <div class="result-value">${fuelNeeded.toFixed(3)} L</div>
        </div>
        <div class="alert alert-tip mt-3">
            <small><i class="fas fa-calculator me-1"></i>
            Cost breakdown for ${distance}km trip at Rs. ${price}/L</small>
        </div>
    `;
    
    document.getElementById('costResults').innerHTML = results;
}

// Efficiency Calculator
function calculateEfficiency() {
    const distance = parseFloat(document.getElementById('efficiencyDistance').value);
    const fuel = parseFloat(document.getElementById('fuelUsed').value);
    const type = document.querySelector('input[name="efficiencyType"]:checked').value;
    
    if (!distance || !fuel) {
        alert('Please fill in all required fields.');
        return;
    }
    
    let efficiency, unit, rating;
    
    if (type === 'kmpl') {
        efficiency = distance / fuel;
        unit = 'km/L';
        
        if (efficiency > 15) rating = 'Excellent';
        else if (efficiency > 12) rating = 'Good';
        else if (efficiency > 8) rating = 'Average';
        else rating = 'Poor';
    } else {
        efficiency = (fuel / distance) * 100;
        unit = 'L/100km';
        
        if (efficiency < 6) rating = 'Excellent';
        else if (efficiency < 8) rating = 'Good';
        else if (efficiency < 12) rating = 'Average';
        else rating = 'Poor';
    }
    
    const results = `
        <div class="result-item">
            <div class="result-label">Fuel Efficiency</div>
            <div class="result-value">${efficiency.toFixed(2)} ${unit}</div>
        </div>
        <div class="result-item">
            <div class="result-label">Efficiency Rating</div>
            <div class="result-value text-${rating === 'Excellent' ? 'success' : rating === 'Good' ? 'primary' : rating === 'Average' ? 'warning' : 'danger'}">
                ${rating}
            </div>
        </div>
        <div class="alert alert-tip mt-3">
            <small><i class="fas fa-chart-line me-1"></i>
            Based on ${distance}km distance and ${fuel}L fuel consumption</small>
        </div>
    `;
    
    document.getElementById('efficiencyResults').innerHTML = results;
}

// Trip Planner
function calculateTrip() {
    const distance = parseFloat(document.getElementById('totalTripDistance').value);
    const currentFuel = parseFloat(document.getElementById('currentFuelAmount').value);
    const efficiency = parseFloat(document.getElementById('tripEfficiency').value);
    const buffer = parseFloat(document.getElementById('safetyBuffer').value) || 20;
    
    if (!distance || !currentFuel || !efficiency) {
        alert('Please fill in all required fields.');
        return;
    }
    
    const fuelNeeded = distance / efficiency;
    const fuelWithBuffer = fuelNeeded * (1 + buffer / 100);
    const fuelToAdd = Math.max(0, fuelWithBuffer - currentFuel);
    const canMakeTrip = currentFuel >= fuelNeeded;
    
    let status, statusClass;
    if (canMakeTrip) {
        if (currentFuel >= fuelWithBuffer) {
            status = 'Ready to Go';
            statusClass = 'success';
        } else {
            status = 'Caution - Low Buffer';
            statusClass = 'warning';
        }
    } else {
        status = 'Need More Fuel';
        statusClass = 'danger';
    }
    
    const results = `
        <div class="result-item">
            <div class="result-label">Trip Status</div>
            <div class="result-value text-${statusClass}">${status}</div>
        </div>
        <div class="result-item">
            <div class="result-label">Fuel Required</div>
            <div class="result-value">${fuelNeeded.toFixed(3)} L</div>
        </div>
        <div class="result-item">
            <div class="result-label">With ${buffer}% Buffer</div>
            <div class="result-value">${fuelWithBuffer.toFixed(3)} L</div>
        </div>
        ${fuelToAdd > 0 ? `
        <div class="result-item">
            <div class="result-label">Fuel to Add</div>
            <div class="result-value text-warning">${fuelToAdd.toFixed(3)} L</div>
        </div>` : ''}
        <div class="alert alert-tip mt-3">
            <small><i class="fas fa-route me-1"></i>
            Trip planning for ${distance}km with ${buffer}% safety buffer</small>
        </div>
    `;
    
    document.getElementById('tripResults').innerHTML = results;
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Add animation to cards
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
});
</script>

<?php include 'includes/footer.php'; ?>