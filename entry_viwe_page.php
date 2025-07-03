<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'entry_viwe_page.php';
    header('Location: login.php');
    exit();
}

$error_message = '';
$success_message = '';

// Get filter parameters
$selected_vehicle = isset($_GET['vehicle']) ? intval($_GET['vehicle']) : '';
$date_from = isset($_GET['date_from']) ? cleanInput($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? cleanInput($_GET['date_to']) : '';
$calculation_type = isset($_GET['calculation_type']) ? cleanInput($_GET['calculation_type']) : '';

// Get user's vehicles for filter dropdown
$stmt = $pdo->prepare("SELECT id, registration_number, model, vehicle_type FROM vehicles WHERE user_id = ? AND status = 'active' ORDER BY is_primary DESC, registration_number");
$stmt->execute([$_SESSION['user_id']]);
$vehicles = $stmt->fetchAll();

// Build query for trip entries
$where_conditions = ["te.user_id = :user_id"];
$params = [':user_id' => $_SESSION['user_id']];

if ($selected_vehicle) {
    $where_conditions[] = "te.vehicle_id = :vehicle_id";
    $params[':vehicle_id'] = $selected_vehicle;
}

if ($date_from) {
    $where_conditions[] = "te.trip_date >= :date_from";
    $params[':date_from'] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "te.trip_date <= :date_to";
    $params[':date_to'] = $date_to;
}

if ($calculation_type) {
    $where_conditions[] = "te.calculation_type = :calculation_type";
    $params[':calculation_type'] = $calculation_type;
}

$where_clause = implode(' AND ', $where_conditions);

// Get trip entries with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$entries_per_page = 15;
$offset = ($page - 1) * $entries_per_page;

$sql = "SELECT te.*, v.registration_number, v.model, v.vehicle_type 
        FROM trip_entries te 
        JOIN vehicles v ON te.vehicle_id = v.id 
        WHERE $where_clause 
        ORDER BY te.trip_date DESC, te.created_at DESC 
        LIMIT :offset, :limit";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
$stmt->execute();
$entries = $stmt->fetchAll();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) FROM trip_entries te WHERE $where_clause";
$stmt = $pdo->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_entries = $stmt->fetchColumn();
$total_pages = ceil($total_entries / $entries_per_page);

// Get summary statistics
$stats_sql = "SELECT 
    COUNT(*) as total_trips,
    SUM(te.distance_driven) as total_distance,
    SUM(te.fuel_consumed) as total_fuel_consumed,
    AVG(te.fuel_consumed / NULLIF(te.distance_driven, 0)) as avg_consumption
    FROM trip_entries te WHERE $where_clause";
$stmt = $pdo->prepare($stats_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$stats = $stmt->fetch();

// Handle entry deletion
if (isset($_POST['delete_entry'])) {
    $entry_id = intval($_POST['entry_id']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM trip_entries WHERE id = ? AND user_id = ?");
        $stmt->execute([$entry_id, $_SESSION['user_id']]);
        
        $success_message = 'Trip entry deleted successfully!';
    } catch (PDOException $e) {
        $error_message = 'Failed to delete entry. Please try again.';
    }
}

$page_title = "My Trip Entries";
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
                            <i class="fas fa-list-alt me-3"></i>
                            My Trip Entries
                        </h1>
                        <p class="mb-0 opacity-75">
                            View and manage all your vehicle trip entries, analyze fuel consumption patterns, and export logbook data.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <a href="running_chart_generator.php" class="btn btn-outline-light me-2">
                            <i class="fas fa-arrow-left me-1"></i>Dashboard
                        </a>
                        <a href="running_chart.php" class="btn btn-outline-light">
                            <i class="fas fa-plus me-1"></i>Add Entry
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

            <!-- Statistics Cards -->
            <div class="row g-4 mb-5">
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-primary text-white rounded-circle mx-auto mb-3" 
                                 style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-route fa-2x"></i>
                            </div>
                            <h4 class="text-primary mb-1"><?php echo number_format($stats['total_trips']); ?></h4>
                            <p class="text-muted mb-0">Total Trips</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-success text-white rounded-circle mx-auto mb-3" 
                                 style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-road fa-2x"></i>
                            </div>
                            <h4 class="text-success mb-1"><?php echo number_format($stats['total_distance'], 2); ?></h4>
                            <p class="text-muted mb-0">Total Distance (Km)</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-warning text-white rounded-circle mx-auto mb-3" 
                                 style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-gas-pump fa-2x"></i>
                            </div>
                            <h4 class="text-warning mb-1"><?php echo number_format($stats['total_fuel_consumed'], 3); ?></h4>
                            <p class="text-muted mb-0">Total Fuel (Liters)</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-info text-white rounded-circle mx-auto mb-3" 
                                 style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-tachometer-alt fa-2x"></i>
                            </div>
                            <h4 class="text-info mb-1"><?php echo number_format($stats['avg_consumption'], 2); ?></h4>
                            <p class="text-muted mb-0">Avg Efficiency (L/Km)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-filter me-2"></i>
                                Filter Entries
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Vehicle</label>
                                        <select class="form-select" name="vehicle">
                                            <option value="">All Vehicles</option>
                                            <?php foreach ($vehicles as $vehicle): ?>
                                            <option value="<?php echo $vehicle['id']; ?>" 
                                                    <?php echo ($selected_vehicle == $vehicle['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($vehicle['registration_number'] . ' - ' . $vehicle['model']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Date From</label>
                                        <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Date To</label>
                                        <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Calculation Type</label>
                                        <select class="form-select" name="calculation_type">
                                            <option value="">All Types</option>
                                            <option value="distance_driven" <?php echo ($calculation_type === 'distance_driven') ? 'selected' : ''; ?>>Distance Driven</option>
                                            <option value="odo_entered" <?php echo ($calculation_type === 'odo_entered') ? 'selected' : ''; ?>>ODO Entered</option>
                                            <option value="fuel_specified" <?php echo ($calculation_type === 'fuel_specified') ? 'selected' : ''; ?>>Fuel Specified</option>
                                            <option value="fuel_to_save" <?php echo ($calculation_type === 'fuel_to_save') ? 'selected' : ''; ?>>Fuel to Save</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3 d-flex align-items-end">
                                        <div class="w-100">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-search me-1"></i>Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <a href="entry_viwe_page.php" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times me-1"></i>Clear Filters
                                        </a>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportEntries()">
                                            <i class="fas fa-download me-1"></i>Export CSV
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="printReport()">
                                            <i class="fas fa-print me-1"></i>Print Report
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trip Entries Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Trip Entries 
                                <?php if ($total_entries > 0): ?>
                                <span class="badge bg-primary"><?php echo number_format($total_entries); ?></span>
                                <?php endif; ?>
                            </h5>
                            <div>
                                Showing <?php echo min($offset + 1, $total_entries); ?> - 
                                <?php echo min($offset + $entries_per_page, $total_entries); ?> of 
                                <?php echo number_format($total_entries); ?> entries
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($entries)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Vehicle</th>
                                            <th>Type</th>
                                            <th>Initial ODO</th>
                                            <th>Final ODO</th>
                                            <th>Distance</th>
                                            <th>Initial Fuel</th>
                                            <th>Fuel Consumed</th>
                                            <th>Final Fuel</th>
                                            <th>Notes</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($entries as $entry): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted"><?php echo date('M j, Y', strtotime($entry['trip_date'])); ?></small>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($entry['registration_number']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($entry['model']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php 
                                                    $type_labels = [
                                                        'distance_driven' => 'Distance',
                                                        'odo_entered' => 'ODO',
                                                        'fuel_specified' => 'Fuel Spec',
                                                        'fuel_to_save' => 'Fuel Save'
                                                    ];
                                                    echo $type_labels[$entry['calculation_type']] ?? $entry['calculation_type'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($entry['initial_odo_reading'], 2); ?> km</td>
                                            <td><?php echo number_format($entry['final_odo_reading'], 2); ?> km</td>
                                            <td>
                                                <strong class="text-primary">
                                                    <?php echo number_format($entry['distance_driven'] ?? ($entry['final_odo_reading'] - $entry['initial_odo_reading']), 2); ?> km
                                                </strong>
                                            </td>
                                            <td><?php echo number_format($entry['initial_fuel_amount'], 3); ?> L</td>
                                            <td>
                                                <strong class="text-danger">
                                                    <?php echo number_format($entry['fuel_consumed'], 3); ?> L
                                                </strong>
                                            </td>
                                            <td><?php echo number_format($entry['final_fuel_amount'], 3); ?> L</td>
                                            <td>
                                                <?php if (!empty($entry['trip_notes'])): ?>
                                                <button class="btn btn-outline-info btn-sm" onclick="showNotes('<?php echo htmlspecialchars($entry['trip_notes']); ?>')">
                                                    <i class="fas fa-sticky-note"></i>
                                                </button>
                                                <?php else: ?>
                                                <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" onclick="viewEntryDetails(<?php echo $entry['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="deleteEntry(<?php echo $entry['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Trip entries pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $start_page + 4);
                                        for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                            <?php endif; ?>

                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-list-alt fa-4x text-muted mb-3"></i>
                                <h5>No trip entries found</h5>
                                <p class="text-muted">
                                    <?php if ($selected_vehicle || $date_from || $date_to || $calculation_type): ?>
                                    No entries match your current filters. Try adjusting your search criteria.
                                    <?php else: ?>
                                    Start recording your trips using the running chart calculator.
                                    <?php endif; ?>
                                </p>
                                <a href="running_chart.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add First Trip Entry
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Entry Details Modal -->
<div class="modal fade" id="entryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Trip Entry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="entryModalBody">
                <!-- Entry details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Trip Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notesModalBody">
                <!-- Notes will be displayed here -->
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
                <p>Are you sure you want to delete this trip entry? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="entry_id" id="deleteEntryId">
                    <button type="submit" name="delete_entry" class="btn btn-danger">Delete Entry</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    font-size: 0.875rem;
    font-weight: 600;
    white-space: nowrap;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.75rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
    }
}
</style>

<script>
function viewEntryDetails(entryId) {
    const entries = <?php echo json_encode($entries); ?>;
    const entry = entries.find(e => e.id == entryId);
    
    if (entry) {
        const modalBody = document.getElementById('entryModalBody');
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">Trip Information</h6>
                    <table class="table table-sm">
                        <tr><th>Date:</th><td>${entry.trip_date}</td></tr>
                        <tr><th>Vehicle:</th><td>${entry.registration_number} - ${entry.model}</td></tr>
                        <tr><th>Calculation Type:</th><td>${entry.calculation_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</td></tr>
                    </table>
                    
                    <h6 class="mb-3 mt-4">Readings</h6>
                    <table class="table table-sm">
                        <tr><th>Initial ODO:</th><td>${parseFloat(entry.initial_odo_reading).toFixed(2)} km</td></tr>
                        <tr><th>Final ODO:</th><td>${parseFloat(entry.final_odo_reading).toFixed(2)} km</td></tr>
                        <tr><th>Distance Driven:</th><td><strong class="text-primary">${parseFloat(entry.distance_driven || (entry.final_odo_reading - entry.initial_odo_reading)).toFixed(2)} km</strong></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Fuel Information</h6>
                    <table class="table table-sm">
                        <tr><th>Initial Fuel:</th><td>${parseFloat(entry.initial_fuel_amount).toFixed(3)} L</td></tr>
                        <tr><th>Fuel Refilled:</th><td>${parseFloat(entry.fuel_refilled || 0).toFixed(3)} L</td></tr>
                        <tr><th>Fuel Consumed:</th><td><strong class="text-danger">${parseFloat(entry.fuel_consumed).toFixed(3)} L</strong></td></tr>
                        <tr><th>Final Fuel:</th><td>${parseFloat(entry.final_fuel_amount).toFixed(3)} L</td></tr>
                    </table>
                    
                    <h6 class="mb-3 mt-4">Efficiency</h6>
                    <table class="table table-sm">
                        <tr><th>Fuel per Km:</th><td>${(parseFloat(entry.fuel_consumed) / parseFloat(entry.distance_driven || (entry.final_odo_reading - entry.initial_odo_reading))).toFixed(3)} L/km</td></tr>
                        <tr><th>Km per Liter:</th><td>${(parseFloat(entry.distance_driven || (entry.final_odo_reading - entry.initial_odo_reading)) / parseFloat(entry.fuel_consumed)).toFixed(2)} km/L</td></tr>
                    </table>
                </div>
                ${entry.trip_notes ? `<div class="col-12 mt-3"><h6>Notes:</h6><p class="bg-light p-3 rounded">${entry.trip_notes}</p></div>` : ''}
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('entryModal'));
        modal.show();
    }
}

function showNotes(notes) {
    document.getElementById('notesModalBody').innerHTML = `<p>${notes}</p>`;
    const modal = new bootstrap.Modal(document.getElementById('notesModal'));
    modal.show();
}

function deleteEntry(entryId) {
    document.getElementById('deleteEntryId').value = entryId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function exportEntries() {
    const entries = <?php echo json_encode($entries); ?>;
    let csv = 'Date,Vehicle,Registration,Type,Initial ODO,Final ODO,Distance,Initial Fuel,Fuel Consumed,Final Fuel,Notes\n';
    
    entries.forEach(entry => {
        const distance = entry.distance_driven || (entry.final_odo_reading - entry.initial_odo_reading);
        csv += `"${entry.trip_date}","${entry.model}","${entry.registration_number}","${entry.calculation_type}",${entry.initial_odo_reading},${entry.final_odo_reading},${distance},${entry.initial_fuel_amount},${entry.fuel_consumed},${entry.final_fuel_amount},"${entry.trip_notes || ''}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'trip_entries_export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

function printReport() {
    window.print();
}

// Auto-submit form when date fields change
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Optional: auto-submit on date change
            // this.form.submit();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>