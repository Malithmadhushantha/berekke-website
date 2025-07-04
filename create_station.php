<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'create_station.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();
$error_message = '';
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_station'])) {
        // Create new station
        $station_name = cleanInput($_POST['station_name']);
        $station_telephone = cleanInput($_POST['station_telephone']);
        $station_address = cleanInput($_POST['station_address']);
        $station_code = cleanInput($_POST['station_code']);
        
        if (empty($station_name)) {
            $error_message = 'Station name is required.';
        } else {
            try {
                // Check if station name already exists for this user
                $stmt = $pdo->prepare("SELECT station_id FROM police_stations WHERE station_name = ? AND user_id = ? AND status = 'active'");
                $stmt->execute([$station_name, $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    $error_message = 'A station with this name already exists.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO police_stations (user_id, station_name, station_telephone, station_address, station_code, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $station_name, $station_telephone, $station_address, $station_code, $_SESSION['user_id']]);
                    $success_message = 'Police station created successfully!';
                }
            } catch (PDOException $e) {
                $error_message = 'Failed to create station. Please try again.';
            }
        }
    } elseif (isset($_POST['create_branch'])) {
        // Create new branch
        $station_id = intval($_POST['station_id']);
        $branch_name = cleanInput($_POST['branch_name']);
        $branch_telephone = cleanInput($_POST['branch_telephone']);
        $branch_share = cleanInput($_POST['branch_share']);
        $branch_address = cleanInput($_POST['branch_address']);
        $branch_code = cleanInput($_POST['branch_code']);
        
        if (empty($branch_name) || empty($station_id)) {
            $error_message = 'Branch name and station selection are required.';
        } else {
            try {
                // Verify station belongs to user
                $stmt = $pdo->prepare("SELECT station_id FROM police_stations WHERE station_id = ? AND user_id = ?");
                $stmt->execute([$station_id, $_SESSION['user_id']]);
                if (!$stmt->fetch()) {
                    $error_message = 'Invalid station selected.';
                } else {
                    // Check if branch name already exists for this station
                    $stmt = $pdo->prepare("SELECT branch_id FROM branch_table WHERE branch_name = ? AND station_id = ? AND status = 'active'");
                    $stmt->execute([$branch_name, $station_id]);
                    if ($stmt->fetch()) {
                        $error_message = 'A branch with this name already exists in this station.';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO branch_table (user_id, station_id, branch_name, branch_telephone, branch_share, branch_address, branch_code, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$_SESSION['user_id'], $station_id, $branch_name, $branch_telephone, $branch_share, $branch_address, $branch_code, $_SESSION['user_id']]);
                        $success_message = 'Branch created successfully!';
                    }
                }
            } catch (PDOException $e) {
                $error_message = 'Failed to create branch. Please try again.';
            }
        }
    } elseif (isset($_POST['update_station'])) {
        // Update station
        $station_id = intval($_POST['station_id']);
        $station_name = cleanInput($_POST['station_name']);
        $station_telephone = cleanInput($_POST['station_telephone']);
        $station_address = cleanInput($_POST['station_address']);
        $station_code = cleanInput($_POST['station_code']);
        
        try {
            $stmt = $pdo->prepare("UPDATE police_stations SET station_name = ?, station_telephone = ?, station_address = ?, station_code = ? WHERE station_id = ? AND user_id = ?");
            $stmt->execute([$station_name, $station_telephone, $station_address, $station_code, $station_id, $_SESSION['user_id']]);
            $success_message = 'Station updated successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to update station. Please try again.';
        }
    } elseif (isset($_POST['update_branch'])) {
        // Update branch
        $branch_id = intval($_POST['branch_id']);
        $branch_name = cleanInput($_POST['branch_name']);
        $branch_telephone = cleanInput($_POST['branch_telephone']);
        $branch_share = cleanInput($_POST['branch_share']);
        $branch_address = cleanInput($_POST['branch_address']);
        $branch_code = cleanInput($_POST['branch_code']);
        
        try {
            $stmt = $pdo->prepare("UPDATE branch_table SET branch_name = ?, branch_telephone = ?, branch_share = ?, branch_address = ?, branch_code = ? WHERE branch_id = ? AND user_id = ?");
            $stmt->execute([$branch_name, $branch_telephone, $branch_share, $branch_address, $branch_code, $branch_id, $_SESSION['user_id']]);
            $success_message = 'Branch updated successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to update branch. Please try again.';
        }
    } elseif (isset($_POST['delete_station'])) {
        // Delete station (soft delete)
        $station_id = intval($_POST['station_id']);
        
        try {
            $stmt = $pdo->prepare("UPDATE police_stations SET status = 'inactive' WHERE station_id = ? AND user_id = ?");
            $stmt->execute([$station_id, $_SESSION['user_id']]);
            
            // Also mark branches as inactive
            $stmt = $pdo->prepare("UPDATE branch_table SET status = 'inactive' WHERE station_id = ? AND user_id = ?");
            $stmt->execute([$station_id, $_SESSION['user_id']]);
            
            $success_message = 'Station and its branches deleted successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to delete station. Please try again.';
        }
    } elseif (isset($_POST['delete_branch'])) {
        // Delete branch (soft delete)
        $branch_id = intval($_POST['branch_id']);
        
        try {
            $stmt = $pdo->prepare("UPDATE branch_table SET status = 'inactive' WHERE branch_id = ? AND user_id = ?");
            $stmt->execute([$branch_id, $_SESSION['user_id']]);
            $success_message = 'Branch deleted successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to delete branch. Please try again.';
        }
    }
}

// Get user's stations with branches
$stmt = $pdo->prepare("
    SELECT 
        ps.station_id, ps.station_name, ps.station_telephone, ps.station_address, ps.station_code, ps.created_at,
        bt.branch_id, bt.branch_name, bt.branch_telephone, bt.branch_share, bt.branch_address, bt.branch_code
    FROM police_stations ps
    LEFT JOIN branch_table bt ON ps.station_id = bt.station_id AND bt.status = 'active'
    WHERE ps.user_id = ? AND ps.status = 'active'
    ORDER BY ps.station_name ASC, bt.branch_name ASC
");
$stmt->execute([$_SESSION['user_id']]);
$stations_data = $stmt->fetchAll();

// Organize data by stations
$stations = [];
foreach ($stations_data as $row) {
    $station_id = $row['station_id'];
    if (!isset($stations[$station_id])) {
        $stations[$station_id] = [
            'station_id' => $row['station_id'],
            'station_name' => $row['station_name'],
            'station_telephone' => $row['station_telephone'],
            'station_address' => $row['station_address'],
            'station_code' => $row['station_code'],
            'created_at' => $row['created_at'],
            'branches' => []
        ];
    }
    
    if ($row['branch_id']) {
        $stations[$station_id]['branches'][] = [
            'branch_id' => $row['branch_id'],
            'branch_name' => $row['branch_name'],
            'branch_telephone' => $row['branch_telephone'],
            'branch_share' => $row['branch_share'],
            'branch_address' => $row['branch_address'],
            'branch_code' => $row['branch_code']
        ];
    }
}

// Get stations for branch creation dropdown
$stmt = $pdo->prepare("SELECT station_id, station_name FROM police_stations WHERE user_id = ? AND status = 'active' ORDER BY station_name ASC");
$stmt->execute([$_SESSION['user_id']]);
$available_stations = $stmt->fetchAll();

$page_title = "Station Management";
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
                            <i class="fas fa-building me-3"></i>
                            Station Management
                        </h1>
                        <p class="mb-0 opacity-75">
                            Create and manage police stations and their branches. Organize your administrative structure efficiently.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <a href="Create_report.php" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Reports
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

            <!-- Creation Forms -->
            <div class="row g-4 mb-5">
                <!-- Create Station Form -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>
                                Create New Station
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="station_name" class="form-label fw-semibold">
                                        Station Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="station_name" name="station_name" 
                                           placeholder="e.g., Colombo Central Police Station" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="station_code" class="form-label fw-semibold">Station Code</label>
                                    <input type="text" class="form-control" id="station_code" name="station_code" 
                                           placeholder="e.g., CCPS" maxlength="10">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="station_telephone" class="form-label fw-semibold">Station Telephone</label>
                                    <input type="text" class="form-control" id="station_telephone" name="station_telephone" 
                                           placeholder="e.g., 011-2433333">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="station_address" class="form-label fw-semibold">Station Address</label>
                                    <textarea class="form-control" id="station_address" name="station_address" rows="3" 
                                              placeholder="Complete station address"></textarea>
                                </div>
                                
                                <button type="submit" name="create_station" class="btn btn-primary w-100">
                                    <i class="fas fa-building me-2"></i>Create Station
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Create Branch Form -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-sitemap me-2"></i>
                                Create New Branch
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($available_stations)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-building fa-3x mb-3"></i>
                                <p>Please create a station first before adding branches.</p>
                            </div>
                            <?php else: ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="station_id" class="form-label fw-semibold">
                                        Select Station <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="station_id" name="station_id" required>
                                        <option value="">Choose a station...</option>
                                        <?php foreach ($available_stations as $station): ?>
                                        <option value="<?php echo $station['station_id']; ?>">
                                            <?php echo htmlspecialchars($station['station_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="branch_name" class="form-label fw-semibold">
                                        Branch Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="branch_name" name="branch_name" 
                                           placeholder="e.g., Traffic Division" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="branch_code" class="form-label fw-semibold">Branch Code</label>
                                    <input type="text" class="form-control" id="branch_code" name="branch_code" 
                                           placeholder="e.g., TD01" maxlength="10">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="branch_telephone" class="form-label fw-semibold">Branch Telephone</label>
                                    <input type="text" class="form-control" id="branch_telephone" name="branch_telephone" 
                                           placeholder="e.g., 011-2433334">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="branch_share" class="form-label fw-semibold">Branch Share</label>
                                    <select class="form-select" id="branch_share" name="branch_share">
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="branch_address" class="form-label fw-semibold">Branch Address</label>
                                    <textarea class="form-control" id="branch_address" name="branch_address" rows="2" 
                                              placeholder="Branch address (if different from station)"></textarea>
                                </div>
                                
                                <button type="submit" name="create_branch" class="btn btn-success w-100">
                                    <i class="fas fa-sitemap me-2"></i>Create Branch
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stations and Branches List -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Your Stations & Branches (<?php echo count($stations); ?> stations)
                            </h5>
                            <div>
                                <button class="btn btn-outline-primary btn-sm" onclick="exportStations()">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($stations)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-building fa-4x text-muted mb-3"></i>
                                <h5>No stations created yet</h5>
                                <p class="text-muted">Create your first police station to get started.</p>
                            </div>
                            <?php else: ?>
                            <div class="accordion" id="stationsAccordion">
                                <?php foreach ($stations as $station): ?>
                                <div class="accordion-item mb-3 border rounded">
                                    <h2 class="accordion-header" id="heading<?php echo $station['station_id']; ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?php echo $station['station_id']; ?>">
                                            <div class="d-flex align-items-center w-100">
                                                <i class="fas fa-building text-primary me-3"></i>
                                                <div class="flex-grow-1">
                                                    <strong><?php echo htmlspecialchars($station['station_name']); ?></strong>
                                                    <?php if ($station['station_code']): ?>
                                                    <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($station['station_code']); ?></span>
                                                    <?php endif; ?>
                                                    <div class="small text-muted">
                                                        <?php echo count($station['branches']); ?> branches
                                                        <?php if ($station['station_telephone']): ?>
                                                        | Tel: <?php echo htmlspecialchars($station['station_telephone']); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $station['station_id']; ?>" class="accordion-collapse collapse" 
                                         data-bs-parent="#stationsAccordion">
                                        <div class="accordion-body">
                                            <!-- Station Details -->
                                            <div class="row mb-3">
                                                <div class="col-md-8">
                                                    <h6 class="text-primary">Station Information</h6>
                                                    <?php if ($station['station_address']): ?>
                                                    <p class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($station['station_address']); ?></p>
                                                    <?php endif; ?>
                                                    <p class="mb-1"><strong>Created:</strong> <?php echo date('F j, Y', strtotime($station['created_at'])); ?></p>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button class="btn btn-outline-primary" onclick="editStation(<?php echo $station['station_id']; ?>)">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="deleteStation(<?php echo $station['station_id']; ?>)">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Branches -->
                                            <hr>
                                            <h6 class="text-success">
                                                <i class="fas fa-sitemap me-2"></i>
                                                Branches (<?php echo count($station['branches']); ?>)
                                            </h6>
                                            
                                            <?php if (empty($station['branches'])): ?>
                                            <div class="text-center py-3 text-muted">
                                                <i class="fas fa-sitemap fa-2x mb-2"></i>
                                                <p class="mb-0">No branches created for this station yet.</p>
                                            </div>
                                            <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Branch Name</th>
                                                            <th>Code</th>
                                                            <th>Telephone</th>
                                                            <th>Share</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($station['branches'] as $branch): ?>
                                                        <tr>
                                                            <td>
                                                                <i class="fas fa-sitemap text-success me-2"></i>
                                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($branch['branch_code']): ?>
                                                                <span class="badge bg-success"><?php echo htmlspecialchars($branch['branch_code']); ?></span>
                                                                <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($branch['branch_telephone']) ?: '-'; ?></td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $branch['branch_share'] === 'yes' ? 'success' : 'secondary'; ?>">
                                                                    <?php echo ucfirst($branch['branch_share']); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <button class="btn btn-outline-primary btn-sm" onclick="editBranch(<?php echo $branch['branch_id']; ?>)">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteBranch(<?php echo $branch['branch_id']; ?>)">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Station Modal -->
<div class="modal fade" id="editStationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Station</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="station_id" id="editStationId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Station Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="station_name" id="editStationName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Station Code</label>
                        <input type="text" class="form-control" name="station_code" id="editStationCode" maxlength="10">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Station Telephone</label>
                        <input type="text" class="form-control" name="station_telephone" id="editStationTelephone">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Station Address</label>
                        <textarea class="form-control" name="station_address" id="editStationAddress" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_station" class="btn btn-primary">Update Station</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Branch Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="branch_id" id="editBranchId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="branch_name" id="editBranchName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Code</label>
                        <input type="text" class="form-control" name="branch_code" id="editBranchCode" maxlength="10">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Telephone</label>
                        <input type="text" class="form-control" name="branch_telephone" id="editBranchTelephone">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Share</label>
                        <select class="form-select" name="branch_share" id="editBranchShare">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Address</label>
                        <textarea class="form-control" name="branch_address" id="editBranchAddress" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_branch" class="btn btn-success">Update Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modals -->
<div class="modal fade" id="deleteStationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Station</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This will delete the station and all its branches. This action cannot be undone.
                </div>
                <p>Are you sure you want to delete this station?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="station_id" id="deleteStationId">
                    <button type="submit" name="delete_station" class="btn btn-danger">Delete Station</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this branch?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="branch_id" id="deleteBranchId">
                    <button type="submit" name="delete_branch" class="btn btn-danger">Delete Branch</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.accordion-button {
    box-shadow: none;
}

.accordion-button:not(.collapsed) {
    color: var(--bs-primary);
    background-color: rgba(13, 110, 253, 0.1);
}

.accordion-button:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
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
    
    .accordion-button {
        padding: 0.75rem;
    }
}
</style>

<script>
// Store station and branch data for editing
const stationsData = <?php echo json_encode($stations); ?>;
const branchesData = {};

// Flatten branches data for easy access
Object.values(stationsData).forEach(station => {
    station.branches.forEach(branch => {
        branchesData[branch.branch_id] = {
            ...branch,
            station_name: station.station_name
        };
    });
});

function editStation(stationId) {
    const station = stationsData[stationId];
    if (station) {
        document.getElementById('editStationId').value = station.station_id;
        document.getElementById('editStationName').value = station.station_name;
        document.getElementById('editStationCode').value = station.station_code || '';
        document.getElementById('editStationTelephone').value = station.station_telephone || '';
        document.getElementById('editStationAddress').value = station.station_address || '';
        
        const modal = new bootstrap.Modal(document.getElementById('editStationModal'));
        modal.show();
    }
}

function editBranch(branchId) {
    const branch = branchesData[branchId];
    if (branch) {
        document.getElementById('editBranchId').value = branch.branch_id;
        document.getElementById('editBranchName').value = branch.branch_name;
        document.getElementById('editBranchCode').value = branch.branch_code || '';
        document.getElementById('editBranchTelephone').value = branch.branch_telephone || '';
        document.getElementById('editBranchShare').value = branch.branch_share;
        document.getElementById('editBranchAddress').value = branch.branch_address || '';
        
        const modal = new bootstrap.Modal(document.getElementById('editBranchModal'));
        modal.show();
    }
}

function deleteStation(stationId) {
    document.getElementById('deleteStationId').value = stationId;
    const modal = new bootstrap.Modal(document.getElementById('deleteStationModal'));
    modal.show();
}

function deleteBranch(branchId) {
    document.getElementById('deleteBranchId').value = branchId;
    const modal = new bootstrap.Modal(document.getElementById('deleteBranchModal'));
    modal.show();
}

function exportStations() {
    // Simple CSV export
    let csv = 'Station Name,Station Code,Telephone,Address,Branch Name,Branch Code,Branch Telephone,Branch Share\n';
    
    Object.values(stationsData).forEach(station => {
        if (station.branches.length === 0) {
            csv += `"${station.station_name}","${station.station_code || ''}","${station.station_telephone || ''}","${station.station_address || ''}","","","",""\n`;
        } else {
            station.branches.forEach(branch => {
                csv += `"${station.station_name}","${station.station_code || ''}","${station.station_telephone || ''}","${station.station_address || ''}","${branch.branch_name}","${branch.branch_code || ''}","${branch.branch_telephone || ''}","${branch.branch_share}"\n`;
            });
        }
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'stations_and_branches.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
});
</script>

<?php include 'includes/footer.php'; ?>