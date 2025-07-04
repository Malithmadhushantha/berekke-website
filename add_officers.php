<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'add_officers.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();
$error_message = '';
$success_message = '';

// Handle AJAX requests for branches
if (isset($_GET['action']) && $_GET['action'] === 'get_branches' && isset($_GET['station_id'])) {
    header('Content-Type: application/json');
    $station_id = intval($_GET['station_id']);
    
    $stmt = $pdo->prepare("SELECT branch_id, branch_name FROM branch_table WHERE station_id = ? AND user_id = ? AND status = 'active' ORDER BY branch_name ASC");
    $stmt->execute([$station_id, $_SESSION['user_id']]);
    $branches = $stmt->fetchAll();
    
    echo json_encode($branches);
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_officer'])) {
        // Add new officer
        $rank = cleanInput($_POST['rank']);
        $regimental_number = cleanInput($_POST['regimental_number']);
        $first_name = cleanInput($_POST['first_name']);
        $last_name = cleanInput($_POST['last_name']);
        $station_id = intval($_POST['station_id']);
        $branch_id = !empty($_POST['branch_id']) ? intval($_POST['branch_id']) : null;
        $birthday = !empty($_POST['birthday']) ? cleanInput($_POST['birthday']) : null;
        $date_of_joining = !empty($_POST['date_of_joining_the_service']) ? cleanInput($_POST['date_of_joining_the_service']) : null;
        $date_of_current_position = !empty($_POST['date_of_assuming_current_position']) ? cleanInput($_POST['date_of_assuming_current_position']) : null;
        $retirement_date = !empty($_POST['retirement_date']) ? cleanInput($_POST['retirement_date']) : null;
        $additional_notes = cleanInput($_POST['additional_notes']);
        
        // Validation
        if (empty($rank) || empty($regimental_number) || empty($first_name) || empty($last_name) || empty($station_id)) {
            $error_message = 'Please fill in all required fields.';
        } else {
            try {
                // Check if regimental number already exists
                $stmt = $pdo->prepare("SELECT officer_id FROM officers WHERE regimental_number = ? AND officer_status = 'active'");
                $stmt->execute([$regimental_number]);
                if ($stmt->fetch()) {
                    $error_message = 'An officer with this regimental number already exists.';
                } else {
                    // Verify station belongs to user
                    $stmt = $pdo->prepare("SELECT station_id FROM police_stations WHERE station_id = ? AND user_id = ?");
                    $stmt->execute([$station_id, $_SESSION['user_id']]);
                    if (!$stmt->fetch()) {
                        $error_message = 'Invalid station selected.';
                    } else {
                        // Handle profile picture upload
                        $profile_picture = 'default_officer.jpg';
                        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                            $upload_dir = PROFILE_PICS_PATH;
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            
                            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                            
                            if (in_array(strtolower($file_extension), $allowed_extensions)) {
                                $profile_picture = 'officer_' . time() . '.' . $file_extension;
                                $upload_path = $upload_dir . $profile_picture;
                                
                                if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                                    $profile_picture = 'default_officer.jpg';
                                }
                            }
                        }
                        
                        $stmt = $pdo->prepare("INSERT INTO officers (rank, regimental_number, first_name, last_name, station_id, branch_id, birthday, date_of_joining_the_service, date_of_assuming_current_position, retirement_date, profile_picture, additional_notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$rank, $regimental_number, $first_name, $last_name, $station_id, $branch_id, $birthday, $date_of_joining, $date_of_current_position, $retirement_date, $profile_picture, $additional_notes, $_SESSION['user_id']]);
                        
                        $success_message = 'Officer added successfully!';
                    }
                }
            } catch (PDOException $e) {
                $error_message = 'Failed to add officer. Please try again.';
            }
        }
    } elseif (isset($_POST['update_officer'])) {
        // Update officer
        $officer_id = intval($_POST['officer_id']);
        $rank = cleanInput($_POST['rank']);
        $regimental_number = cleanInput($_POST['regimental_number']);
        $first_name = cleanInput($_POST['first_name']);
        $last_name = cleanInput($_POST['last_name']);
        $station_id = intval($_POST['station_id']);
        $branch_id = !empty($_POST['branch_id']) ? intval($_POST['branch_id']) : null;
        $birthday = !empty($_POST['birthday']) ? cleanInput($_POST['birthday']) : null;
        $date_of_joining = !empty($_POST['date_of_joining_the_service']) ? cleanInput($_POST['date_of_joining_the_service']) : null;
        $date_of_current_position = !empty($_POST['date_of_assuming_current_position']) ? cleanInput($_POST['date_of_assuming_current_position']) : null;
        $retirement_date = !empty($_POST['retirement_date']) ? cleanInput($_POST['retirement_date']) : null;
        $officer_status = cleanInput($_POST['officer_status']);
        $additional_notes = cleanInput($_POST['additional_notes']);
        
        try {
            $stmt = $pdo->prepare("UPDATE officers SET rank = ?, regimental_number = ?, first_name = ?, last_name = ?, station_id = ?, branch_id = ?, birthday = ?, date_of_joining_the_service = ?, date_of_assuming_current_position = ?, retirement_date = ?, officer_status = ?, additional_notes = ? WHERE officer_id = ? AND created_by = ?");
            $stmt->execute([$rank, $regimental_number, $first_name, $last_name, $station_id, $branch_id, $birthday, $date_of_joining, $date_of_current_position, $retirement_date, $officer_status, $additional_notes, $officer_id, $_SESSION['user_id']]);
            
            $success_message = 'Officer updated successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to update officer. Please try again.';
        }
    } elseif (isset($_POST['delete_officer'])) {
        // Delete officer (soft delete)
        $officer_id = intval($_POST['officer_id']);
        
        try {
            $stmt = $pdo->prepare("UPDATE officers SET officer_status = 'resigned' WHERE officer_id = ? AND created_by = ?");
            $stmt->execute([$officer_id, $_SESSION['user_id']]);
            $success_message = 'Officer deleted successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to delete officer. Please try again.';
        }
    }
}

// Get user's stations for dropdown
$stmt = $pdo->prepare("SELECT station_id, station_name, station_code FROM police_stations WHERE user_id = ? AND status = 'active' ORDER BY station_name ASC");
$stmt->execute([$_SESSION['user_id']]);
$available_stations = $stmt->fetchAll();

// Get officer ranks
$stmt = $pdo->query("SELECT rank_name FROM officer_ranks ORDER BY rank_level ASC");
$ranks = $stmt->fetchAll();

// Get officers with station and branch information
$stmt = $pdo->prepare("
    SELECT 
        o.officer_id, o.rank, o.regimental_number, o.first_name, o.last_name,
        o.station_id, o.branch_id, o.birthday, o.date_of_joining_the_service,
        o.date_of_assuming_current_position, o.retirement_date, o.officer_status,
        o.profile_picture, o.additional_notes, o.created_at,
        ps.station_name, ps.station_code,
        bt.branch_name
    FROM officers o
    JOIN police_stations ps ON o.station_id = ps.station_id
    LEFT JOIN branch_table bt ON o.branch_id = bt.branch_id
    WHERE ps.user_id = ? AND o.officer_status = 'active'
    ORDER BY ps.station_name ASC, bt.branch_name ASC, o.rank ASC, o.last_name ASC
");
$stmt->execute([$_SESSION['user_id']]);
$officers_data = $stmt->fetchAll();

// Organize officers by station and branch
$officers_by_location = [];
foreach ($officers_data as $officer) {
    $station_key = $officer['station_id'];
    $branch_key = $officer['branch_id'] ?? 'no_branch';
    
    if (!isset($officers_by_location[$station_key])) {
        $officers_by_location[$station_key] = [
            'station_name' => $officer['station_name'],
            'station_code' => $officer['station_code'],
            'branches' => []
        ];
    }
    
    if (!isset($officers_by_location[$station_key]['branches'][$branch_key])) {
        $officers_by_location[$station_key]['branches'][$branch_key] = [
            'branch_name' => $officer['branch_name'] ?? 'No Branch Assigned',
            'officers' => []
        ];
    }
    
    $officers_by_location[$station_key]['branches'][$branch_key]['officers'][] = $officer;
}

$page_title = "Officer Management";
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
                            <i class="fas fa-user-plus me-3"></i>
                            Officer Management
                        </h1>
                        <p class="mb-0 opacity-75">
                            Register new officers, manage personnel records, and maintain comprehensive officer database.
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

            <!-- Add Officer Form -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user-plus me-2"></i>
                                Add New Officer
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($available_stations)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-building fa-3x mb-3"></i>
                                <p>Please create a police station first before adding officers.</p>
                                <a href="create_station.php" class="btn btn-primary">Create Station</a>
                            </div>
                            <?php else: ?>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="row">
                                    <!-- Personal Information -->
                                    <div class="col-12 mb-4">
                                        <h6 class="text-success border-bottom pb-2">Personal Information</h6>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="rank" class="form-label fw-semibold">
                                            Rank <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="rank" name="rank" required>
                                            <option value="">Select Rank</option>
                                            <?php foreach ($ranks as $rank): ?>
                                            <option value="<?php echo htmlspecialchars($rank['rank_name']); ?>">
                                                <?php echo htmlspecialchars($rank['rank_name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="regimental_number" class="form-label fw-semibold">
                                            Regimental Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="regimental_number" name="regimental_number" 
                                               placeholder="e.g., IP001, SI001, PC001" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label fw-semibold">
                                            First Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               placeholder="Enter first name" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label fw-semibold">
                                            Last Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               placeholder="Enter last name" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="birthday" class="form-label fw-semibold">Birthday</label>
                                        <input type="date" class="form-control" id="birthday" name="birthday">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="profile_picture" class="form-label fw-semibold">Profile Picture</label>
                                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" 
                                               accept="image/*">
                                        <div class="form-text">Accepted formats: JPG, PNG, GIF (Max 5MB)</div>
                                    </div>

                                    <!-- Assignment Information -->
                                    <div class="col-12 mb-4 mt-4">
                                        <h6 class="text-success border-bottom pb-2">Assignment Information</h6>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="station_id" class="form-label fw-semibold">
                                            Assigned Station <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="station_id" name="station_id" onchange="loadBranches(this.value)" required>
                                            <option value="">Select Station</option>
                                            <?php foreach ($available_stations as $station): ?>
                                            <option value="<?php echo $station['station_id']; ?>">
                                                <?php echo htmlspecialchars($station['station_name']); ?>
                                                <?php if ($station['station_code']): ?>
                                                (<?php echo htmlspecialchars($station['station_code']); ?>)
                                                <?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="branch_id" class="form-label fw-semibold">Assigned Branch</label>
                                        <select class="form-select" id="branch_id" name="branch_id">
                                            <option value="">Select station first</option>
                                        </select>
                                        <div class="form-text">Optional - Select a station first to see available branches</div>
                                    </div>

                                    <!-- Service Dates -->
                                    <div class="col-12 mb-4 mt-4">
                                        <h6 class="text-success border-bottom pb-2">Service Information</h6>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="date_of_joining_the_service" class="form-label fw-semibold">Date of Joining Service</label>
                                        <input type="date" class="form-control" id="date_of_joining_the_service" name="date_of_joining_the_service">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="date_of_assuming_current_position" class="form-label fw-semibold">Date of Current Position</label>
                                        <input type="date" class="form-control" id="date_of_assuming_current_position" name="date_of_assuming_current_position">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="retirement_date" class="form-label fw-semibold">Expected Retirement Date</label>
                                        <input type="date" class="form-control" id="retirement_date" name="retirement_date">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <!-- Placeholder for future use -->
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label for="additional_notes" class="form-label fw-semibold">Additional Notes</label>
                                        <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3" 
                                                  placeholder="Any additional information about the officer"></textarea>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <button type="reset" class="btn btn-secondary me-2">Reset Form</button>
                                    <button type="submit" name="add_officer" class="btn btn-success">
                                        <i class="fas fa-user-plus me-2"></i>Add Officer
                                    </button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Officers List -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                Registered Officers (<?php echo count($officers_data); ?> officers)
                            </h5>
                            <div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="toggleView('card')">
                                        <i class="fas fa-th-large"></i> Cards
                                    </button>
                                    <button class="btn btn-outline-primary active" onclick="toggleView('table')">
                                        <i class="fas fa-table"></i> Table
                                    </button>
                                </div>
                                <button class="btn btn-outline-success btn-sm ms-2" onclick="exportOfficers()">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($officers_data)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                <h5>No officers registered yet</h5>
                                <p class="text-muted">Add your first officer to get started.</p>
                            </div>
                            <?php else: ?>
                            
                            <!-- Table View -->
                            <div id="tableView">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Photo</th>
                                                <th>Rank</th>
                                                <th>Reg. Number</th>
                                                <th>Name</th>
                                                <th>Station</th>
                                                <th>Branch</th>
                                                <th>Service Years</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($officers_data as $officer): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo PROFILE_PICS_PATH . $officer['profile_picture']; ?>" 
                                                         alt="Profile" class="rounded-circle" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($officer['rank']); ?></span>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($officer['regimental_number']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($officer['station_name']); ?>
                                                    <?php if ($officer['station_code']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($officer['station_code']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($officer['branch_name'] ?? 'Not Assigned'); ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if ($officer['date_of_joining_the_service']) {
                                                        $years = date('Y') - date('Y', strtotime($officer['date_of_joining_the_service']));
                                                        echo $years . ' years';
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button class="btn btn-outline-primary" onclick="viewOfficer(<?php echo $officer['officer_id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-success" onclick="editOfficer(<?php echo $officer['officer_id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="deleteOfficer(<?php echo $officer['officer_id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Card View -->
                            <div id="cardView" style="display: none;">
                                <?php foreach ($officers_by_location as $station_id => $station_data): ?>
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="fas fa-building me-2"></i>
                                        <?php echo htmlspecialchars($station_data['station_name']); ?>
                                        <?php if ($station_data['station_code']): ?>
                                        <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($station_data['station_code']); ?></span>
                                        <?php endif; ?>
                                    </h6>
                                    
                                    <?php foreach ($station_data['branches'] as $branch_data): ?>
                                    <div class="mb-3">
                                        <h6 class="text-success ms-3">
                                            <i class="fas fa-sitemap me-2"></i>
                                            <?php echo htmlspecialchars($branch_data['branch_name']); ?>
                                        </h6>
                                        
                                        <div class="row">
                                            <?php foreach ($branch_data['officers'] as $officer): ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100 border-0 shadow-sm officer-card">
                                                    <div class="card-body text-center">
                                                        <img src="<?php echo PROFILE_PICS_PATH . $officer['profile_picture']; ?>" 
                                                             alt="Profile" class="rounded-circle mb-3" 
                                                             style="width: 80px; height: 80px; object-fit: cover;">
                                                        
                                                        <h6 class="card-title">
                                                            <?php echo htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']); ?>
                                                        </h6>
                                                        
                                                        <div class="mb-2">
                                                            <span class="badge bg-primary"><?php echo htmlspecialchars($officer['rank']); ?></span>
                                                        </div>
                                                        
                                                        <p class="card-text">
                                                            <small class="text-muted">
                                                                <strong>Reg:</strong> <?php echo htmlspecialchars($officer['regimental_number']); ?><br>
                                                                <?php if ($officer['date_of_joining_the_service']): ?>
                                                                <strong>Service:</strong> 
                                                                <?php 
                                                                $years = date('Y') - date('Y', strtotime($officer['date_of_joining_the_service']));
                                                                echo $years . ' years';
                                                                ?>
                                                                <?php endif; ?>
                                                            </small>
                                                        </p>
                                                        
                                                        <div class="btn-group btn-group-sm w-100" role="group">
                                                            <button class="btn btn-outline-primary" onclick="viewOfficer(<?php echo $officer['officer_id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-outline-success" onclick="editOfficer(<?php echo $officer['officer_id']; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-outline-danger" onclick="deleteOfficer(<?php echo $officer['officer_id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
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

<!-- View Officer Modal -->
<div class="modal fade" id="viewOfficerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Officer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="officerDetailsBody">
                <!-- Officer details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Officer Modal -->
<div class="modal fade" id="editOfficerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Officer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="officer_id" id="editOfficerId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Rank <span class="text-danger">*</span></label>
                            <select class="form-select" name="rank" id="editRank" required>
                                <?php foreach ($ranks as $rank): ?>
                                <option value="<?php echo htmlspecialchars($rank['rank_name']); ?>">
                                    <?php echo htmlspecialchars($rank['rank_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Regimental Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="regimental_number" id="editRegNumber" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" id="editFirstName" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" id="editLastName" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Station <span class="text-danger">*</span></label>
                            <select class="form-select" name="station_id" id="editStationId" onchange="loadBranchesForEdit(this.value)" required>
                                <?php foreach ($available_stations as $station): ?>
                                <option value="<?php echo $station['station_id']; ?>">
                                    <?php echo htmlspecialchars($station['station_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Branch</label>
                            <select class="form-select" name="branch_id" id="editBranchId">
                                <option value="">No Branch</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Birthday</label>
                            <input type="date" class="form-control" name="birthday" id="editBirthday">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Officer Status</label>
                            <select class="form-select" name="officer_status" id="editStatus">
                                <option value="active">Active</option>
                                <option value="retired">Retired</option>
                                <option value="transferred">Transferred</option>
                                <option value="resigned">Resigned</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Date of Joining Service</label>
                            <input type="date" class="form-control" name="date_of_joining_the_service" id="editJoiningDate">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Date of Current Position</label>
                            <input type="date" class="form-control" name="date_of_assuming_current_position" id="editCurrentPositionDate">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Retirement Date</label>
                            <input type="date" class="form-control" name="retirement_date" id="editRetirementDate">
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Additional Notes</label>
                            <textarea class="form-control" name="additional_notes" id="editNotes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_officer" class="btn btn-success">Update Officer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteOfficerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Officer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This will mark the officer as resigned. This action can be reversed by editing the officer's status.
                </div>
                <p>Are you sure you want to delete this officer?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="officer_id" id="deleteOfficerId">
                    <button type="submit" name="delete_officer" class="btn btn-danger">Delete Officer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.officer-card {
    transition: all 0.3s ease;
}

.officer-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .officer-card img {
        width: 60px !important;
        height: 60px !important;
    }
}
</style>

<script>
// Store officers data for JavaScript functions
const officersData = <?php echo json_encode($officers_data); ?>;
const stationsData = <?php echo json_encode($available_stations); ?>;

// Load branches when station is selected
async function loadBranches(stationId) {
    const branchSelect = document.getElementById('branch_id');
    
    if (!stationId) {
        branchSelect.innerHTML = '<option value="">Select station first</option>';
        return;
    }
    
    try {
        const response = await fetch(`add_officers.php?action=get_branches&station_id=${stationId}`);
        const branches = await response.json();
        
        branchSelect.innerHTML = '<option value="">No Branch Assignment</option>';
        branches.forEach(branch => {
            branchSelect.innerHTML += `<option value="${branch.branch_id}">${branch.branch_name}</option>`;
        });
    } catch (error) {
        console.error('Error loading branches:', error);
        branchSelect.innerHTML = '<option value="">Error loading branches</option>';
    }
}

// Load branches for edit modal
async function loadBranchesForEdit(stationId) {
    const branchSelect = document.getElementById('editBranchId');
    
    if (!stationId) {
        branchSelect.innerHTML = '<option value="">No Branch</option>';
        return;
    }
    
    try {
        const response = await fetch(`add_officers.php?action=get_branches&station_id=${stationId}`);
        const branches = await response.json();
        
        const currentBranchId = branchSelect.getAttribute('data-current-branch');
        branchSelect.innerHTML = '<option value="">No Branch</option>';
        
        branches.forEach(branch => {
            const selected = branch.branch_id == currentBranchId ? 'selected' : '';
            branchSelect.innerHTML += `<option value="${branch.branch_id}" ${selected}>${branch.branch_name}</option>`;
        });
    } catch (error) {
        console.error('Error loading branches for edit:', error);
    }
}

// View officer details
function viewOfficer(officerId) {
    const officer = officersData.find(o => o.officer_id == officerId);
    if (!officer) return;
    
    const modalBody = document.getElementById('officerDetailsBody');
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-4 text-center">
                <img src="${officer.profile_picture ? '<?php echo PROFILE_PICS_PATH; ?>' + officer.profile_picture : '<?php echo PROFILE_PICS_PATH; ?>default_officer.jpg'}" 
                     alt="Profile" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <h5>${officer.first_name} ${officer.last_name}</h5>
                <span class="badge bg-primary fs-6">${officer.rank}</span>
            </div>
            <div class="col-md-8">
                <table class="table table-borderless">
                    <tr><th>Regimental Number:</th><td>${officer.regimental_number}</td></tr>
                    <tr><th>Station:</th><td>${officer.station_name} ${officer.station_code ? '(' + officer.station_code + ')' : ''}</td></tr>
                    <tr><th>Branch:</th><td>${officer.branch_name || 'Not Assigned'}</td></tr>
                    <tr><th>Birthday:</th><td>${officer.birthday ? new Date(officer.birthday).toLocaleDateString() : 'Not specified'}</td></tr>
                    <tr><th>Joining Date:</th><td>${officer.date_of_joining_the_service ? new Date(officer.date_of_joining_the_service).toLocaleDateString() : 'Not specified'}</td></tr>
                    <tr><th>Current Position Date:</th><td>${officer.date_of_assuming_current_position ? new Date(officer.date_of_assuming_current_position).toLocaleDateString() : 'Not specified'}</td></tr>
                    <tr><th>Retirement Date:</th><td>${officer.retirement_date ? new Date(officer.retirement_date).toLocaleDateString() : 'Not specified'}</td></tr>
                    <tr><th>Status:</th><td><span class="badge bg-${officer.officer_status === 'active' ? 'success' : 'secondary'}">${officer.officer_status.charAt(0).toUpperCase() + officer.officer_status.slice(1)}</span></td></tr>
                </table>
                ${officer.additional_notes ? `<div class="mt-3"><h6>Additional Notes:</h6><p class="bg-light p-3 rounded">${officer.additional_notes}</p></div>` : ''}
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('viewOfficerModal'));
    modal.show();
}

// Edit officer
function editOfficer(officerId) {
    const officer = officersData.find(o => o.officer_id == officerId);
    if (!officer) return;
    
    document.getElementById('editOfficerId').value = officer.officer_id;
    document.getElementById('editRank').value = officer.rank;
    document.getElementById('editRegNumber').value = officer.regimental_number;
    document.getElementById('editFirstName').value = officer.first_name;
    document.getElementById('editLastName').value = officer.last_name;
    document.getElementById('editStationId').value = officer.station_id;
    document.getElementById('editBranchId').setAttribute('data-current-branch', officer.branch_id || '');
    document.getElementById('editBirthday').value = officer.birthday || '';
    document.getElementById('editStatus').value = officer.officer_status;
    document.getElementById('editJoiningDate').value = officer.date_of_joining_the_service || '';
    document.getElementById('editCurrentPositionDate').value = officer.date_of_assuming_current_position || '';
    document.getElementById('editRetirementDate').value = officer.retirement_date || '';
    document.getElementById('editNotes').value = officer.additional_notes || '';
    
    // Load branches for the selected station
    loadBranchesForEdit(officer.station_id);
    
    const modal = new bootstrap.Modal(document.getElementById('editOfficerModal'));
    modal.show();
}

// Delete officer
function deleteOfficer(officerId) {
    document.getElementById('deleteOfficerId').value = officerId;
    const modal = new bootstrap.Modal(document.getElementById('deleteOfficerModal'));
    modal.show();
}

// Toggle between card and table view
function toggleView(viewType) {
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');
    const buttons = document.querySelectorAll('[onclick^="toggleView"]');
    
    if (viewType === 'card') {
        cardView.style.display = 'block';
        tableView.style.display = 'none';
        buttons[0].classList.add('active');
        buttons[1].classList.remove('active');
    } else {
        cardView.style.display = 'none';
        tableView.style.display = 'block';
        buttons[0].classList.remove('active');
        buttons[1].classList.add('active');
    }
}

// Export officers to CSV
function exportOfficers() {
    let csv = 'Rank,Regimental Number,First Name,Last Name,Station,Branch,Birthday,Joining Date,Current Position Date,Retirement Date,Status\n';
    
    officersData.forEach(officer => {
        csv += `"${officer.rank}","${officer.regimental_number}","${officer.first_name}","${officer.last_name}","${officer.station_name}","${officer.branch_name || ''}","${officer.birthday || ''}","${officer.date_of_joining_the_service || ''}","${officer.date_of_assuming_current_position || ''}","${officer.retirement_date || ''}","${officer.officer_status}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'officers_list.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
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