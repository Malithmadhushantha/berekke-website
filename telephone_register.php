<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'telephone_register.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();
$error_message = '';
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_contact'])) {
        // Create new contact entry
        $officer_id = intval($_POST['officer_id']);
        $mobile_no = cleanInput($_POST['mobile_no']);
        $whatsapp_no = cleanInput($_POST['whatsapp_no']);
        $email_address = cleanInput($_POST['email_address']);
        $home_telephone = cleanInput($_POST['home_telephone']);
        $office_extension = cleanInput($_POST['office_extension']);
        $emergency_contact_name = cleanInput($_POST['emergency_contact_name']);
        $emergency_contact_number = cleanInput($_POST['emergency_contact_number']);
        
        if (empty($officer_id)) {
            $error_message = 'Please select an officer.';
        } else {
            try {
                // Verify officer belongs to user's stations
                $stmt = $pdo->prepare("
                    SELECT o.officer_id 
                    FROM officers o 
                    JOIN police_stations ps ON o.station_id = ps.station_id 
                    WHERE o.officer_id = ? AND ps.user_id = ? AND o.officer_status = 'active'
                ");
                $stmt->execute([$officer_id, $_SESSION['user_id']]);
                if (!$stmt->fetch()) {
                    $error_message = 'Invalid officer selected.';
                } else {
                    // Check if contact already exists for this officer
                    $stmt = $pdo->prepare("SELECT register_id FROM telephone_register WHERE officer_id = ? AND contact_status = 'active'");
                    $stmt->execute([$officer_id]);
                    if ($stmt->fetch()) {
                        $error_message = 'Contact information already exists for this officer. Please edit the existing entry.';
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO telephone_register 
                            (user_id, officer_id, mobile_no, whatsapp_no, email_address, home_telephone, office_extension, emergency_contact_name, emergency_contact_number, last_verified, created_by) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)
                        ");
                        $stmt->execute([
                            $_SESSION['user_id'], $officer_id, $mobile_no, $whatsapp_no, $email_address, 
                            $home_telephone, $office_extension, $emergency_contact_name, $emergency_contact_number, $_SESSION['user_id']
                        ]);
                        $success_message = 'Contact information added successfully!';
                    }
                }
            } catch (PDOException $e) {
                $error_message = 'Failed to add contact information. Please try again.';
            }
        }
    } elseif (isset($_POST['update_contact'])) {
        // Update contact entry
        $register_id = intval($_POST['register_id']);
        $mobile_no = cleanInput($_POST['mobile_no']);
        $whatsapp_no = cleanInput($_POST['whatsapp_no']);
        $email_address = cleanInput($_POST['email_address']);
        $home_telephone = cleanInput($_POST['home_telephone']);
        $office_extension = cleanInput($_POST['office_extension']);
        $emergency_contact_name = cleanInput($_POST['emergency_contact_name']);
        $emergency_contact_number = cleanInput($_POST['emergency_contact_number']);
        
        try {
            $stmt = $pdo->prepare("
                UPDATE telephone_register 
                SET mobile_no = ?, whatsapp_no = ?, email_address = ?, home_telephone = ?, office_extension = ?, 
                    emergency_contact_name = ?, emergency_contact_number = ?, last_verified = CURDATE() 
                WHERE register_id = ? AND user_id = ?
            ");
            $stmt->execute([
                $mobile_no, $whatsapp_no, $email_address, $home_telephone, $office_extension, 
                $emergency_contact_name, $emergency_contact_number, $register_id, $_SESSION['user_id']
            ]);
            $success_message = 'Contact information updated successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to update contact information. Please try again.';
        }
    } elseif (isset($_POST['delete_contact'])) {
        // Delete contact entry
        $register_id = intval($_POST['register_id']);
        
        try {
            $stmt = $pdo->prepare("UPDATE telephone_register SET contact_status = 'inactive' WHERE register_id = ? AND user_id = ?");
            $stmt->execute([$register_id, $_SESSION['user_id']]);
            $success_message = 'Contact information deleted successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to delete contact information. Please try again.';
        }
    } elseif (isset($_POST['add_call_log'])) {
        // Add call log entry
        $officer_id = intval($_POST['call_officer_id']) ?: null;
        $call_type = cleanInput($_POST['call_type']);
        $phone_number = cleanInput($_POST['phone_number']);
        $call_duration = cleanInput($_POST['call_duration']);
        $call_date = cleanInput($_POST['call_date']);
        $call_time = cleanInput($_POST['call_time']);
        $call_purpose = cleanInput($_POST['call_purpose']);
        $priority_level = cleanInput($_POST['priority_level']);
        $follow_up_required = cleanInput($_POST['follow_up_required']);
        $follow_up_date = !empty($_POST['follow_up_date']) ? cleanInput($_POST['follow_up_date']) : null;
        $call_notes = cleanInput($_POST['call_notes']);
        
        if (empty($call_type) || empty($phone_number) || empty($call_date) || empty($call_time)) {
            $error_message = 'Please fill in all required fields for call log.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO call_logs 
                    (user_id, officer_id, call_type, phone_number, call_duration, call_date, call_time, call_purpose, priority_level, follow_up_required, follow_up_date, call_notes, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'], $officer_id, $call_type, $phone_number, $call_duration, $call_date, $call_time, 
                    $call_purpose, $priority_level, $follow_up_required, $follow_up_date, $call_notes, $_SESSION['user_id']
                ]);
                $success_message = 'Call log added successfully!';
            } catch (PDOException $e) {
                $error_message = 'Failed to add call log. Please try again.';
            }
        }
    }
}

// Get officers from user's stations for dropdown
$stmt = $pdo->prepare("
    SELECT o.officer_id, o.rank, o.first_name, o.last_name, o.regimental_number, ps.station_name
    FROM officers o
    JOIN police_stations ps ON o.station_id = ps.station_id
    WHERE ps.user_id = ? AND o.officer_status = 'active'
    ORDER BY o.rank ASC, o.first_name ASC
");
$stmt->execute([$_SESSION['user_id']]);
$available_officers = $stmt->fetchAll();

// Get telephone register data with officer details
$stmt = $pdo->prepare("
    SELECT 
        tr.register_id, tr.mobile_no, tr.whatsapp_no, tr.email_address, tr.home_telephone, 
        tr.office_extension, tr.emergency_contact_name, tr.emergency_contact_number, 
        tr.last_verified, tr.created_at,
        o.officer_id, o.rank, o.first_name, o.last_name, o.regimental_number,
        ps.station_name, bt.branch_name
    FROM telephone_register tr
    JOIN officers o ON tr.officer_id = o.officer_id
    JOIN police_stations ps ON o.station_id = ps.station_id
    LEFT JOIN branch_table bt ON o.branch_id = bt.branch_id
    WHERE tr.user_id = ? AND tr.contact_status = 'active'
    ORDER BY o.rank ASC, o.first_name ASC
");
$stmt->execute([$_SESSION['user_id']]);
$telephone_register = $stmt->fetchAll();

// Get recent call logs
$stmt = $pdo->prepare("
    SELECT 
        cl.log_id, cl.call_type, cl.phone_number, cl.call_duration, cl.call_date, cl.call_time, 
        cl.call_purpose, cl.priority_level, cl.follow_up_required, cl.follow_up_date, cl.call_notes,
        o.rank, o.first_name, o.last_name, o.regimental_number
    FROM call_logs cl
    LEFT JOIN officers o ON cl.officer_id = o.officer_id
    WHERE cl.user_id = ?
    ORDER BY cl.call_date DESC, cl.call_time DESC
    LIMIT 20
");
$stmt->execute([$_SESSION['user_id']]);
$recent_calls = $stmt->fetchAll();

$page_title = "Telephone Register";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="bg-danger text-white rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-phone me-3"></i>
                            Telephone Register
                        </h1>
                        <p class="mb-0 opacity-75">
                            Manage officer contact information, maintain call logs, and track communication records efficiently.
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

            <!-- Navigation Tabs -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <ul class="nav nav-tabs card-header-tabs" id="telephoneTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="contacts-tab" data-bs-toggle="tab" 
                                            data-bs-target="#contacts" type="button" role="tab">
                                        <i class="fas fa-address-book me-2"></i>Contact Directory
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="add-contact-tab" data-bs-toggle="tab" 
                                            data-bs-target="#add-contact" type="button" role="tab">
                                        <i class="fas fa-user-plus me-2"></i>Add Contact
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="call-logs-tab" data-bs-toggle="tab" 
                                            data-bs-target="#call-logs" type="button" role="tab">
                                        <i class="fas fa-phone-alt me-2"></i>Call Logs
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="add-call-tab" data-bs-toggle="tab" 
                                            data-bs-target="#add-call" type="button" role="tab">
                                        <i class="fas fa-plus me-2"></i>Log Call
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="telephoneTabContent">
                                
                                <!-- Contact Directory Tab -->
                                <div class="tab-pane fade show active" id="contacts" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="mb-0">
                                            <i class="fas fa-address-book text-danger me-2"></i>
                                            Officer Contact Directory (<?php echo count($telephone_register); ?> contacts)
                                        </h5>
                                        <div>
                                            <button class="btn btn-outline-primary btn-sm me-2" onclick="exportContacts()">
                                                <i class="fas fa-download me-1"></i>Export
                                            </button>
                                            <input type="text" class="form-control form-control-sm d-inline-block w-auto" 
                                                   id="contactSearch" placeholder="Search contacts..." onkeyup="filterContacts()">
                                        </div>
                                    </div>
                                    
                                    <?php if (empty($telephone_register)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-address-book fa-4x text-muted mb-3"></i>
                                        <h5>No contacts found</h5>
                                        <p class="text-muted">Add officer contact information to get started.</p>
                                        <button class="btn btn-danger" onclick="document.getElementById('add-contact-tab').click()">
                                            <i class="fas fa-plus me-2"></i>Add First Contact
                                        </button>
                                    </div>
                                    <?php else: ?>
                                    <div class="row g-3" id="contactsGrid">
                                        <?php foreach ($telephone_register as $contact): ?>
                                        <div class="col-lg-6 col-md-12 contact-card" 
                                             data-search="<?php echo strtolower($contact['first_name'] . ' ' . $contact['last_name'] . ' ' . $contact['rank'] . ' ' . $contact['regimental_number'] . ' ' . $contact['mobile_no'] . ' ' . $contact['email_address']); ?>">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-user text-primary me-2"></i>
                                                            <?php echo htmlspecialchars($contact['rank'] . ' ' . $contact['first_name'] . ' ' . $contact['last_name']); ?>
                                                        </h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($contact['regimental_number']); ?></small>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                                data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="editContact(<?php echo $contact['register_id']; ?>)">
                                                                <i class="fas fa-edit me-2"></i>Edit
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="deleteContact(<?php echo $contact['register_id']; ?>)">
                                                                <i class="fas fa-trash me-2"></i>Delete
                                                            </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6 class="text-primary">Contact Information</h6>
                                                            <?php if ($contact['mobile_no']): ?>
                                                            <p class="mb-1">
                                                                <i class="fas fa-mobile-alt text-success me-2"></i>
                                                                <a href="tel:<?php echo $contact['mobile_no']; ?>" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($contact['mobile_no']); ?>
                                                                </a>
                                                            </p>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($contact['whatsapp_no']): ?>
                                                            <p class="mb-1">
                                                                <i class="fab fa-whatsapp text-success me-2"></i>
                                                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact['whatsapp_no']); ?>" target="_blank" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($contact['whatsapp_no']); ?>
                                                                </a>
                                                            </p>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($contact['email_address']): ?>
                                                            <p class="mb-1">
                                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                                <a href="mailto:<?php echo $contact['email_address']; ?>" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($contact['email_address']); ?>
                                                                </a>
                                                            </p>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($contact['home_telephone']): ?>
                                                            <p class="mb-1">
                                                                <i class="fas fa-home text-info me-2"></i>
                                                                <a href="tel:<?php echo $contact['home_telephone']; ?>" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($contact['home_telephone']); ?>
                                                                </a>
                                                            </p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="text-secondary">Work Details</h6>
                                                            <p class="mb-1">
                                                                <i class="fas fa-building text-primary me-2"></i>
                                                                <?php echo htmlspecialchars($contact['station_name']); ?>
                                                            </p>
                                                            <?php if ($contact['branch_name']): ?>
                                                            <p class="mb-1">
                                                                <i class="fas fa-sitemap text-success me-2"></i>
                                                                <?php echo htmlspecialchars($contact['branch_name']); ?>
                                                            </p>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($contact['office_extension']): ?>
                                                            <p class="mb-1">
                                                                <i class="fas fa-phone text-warning me-2"></i>
                                                                Ext: <?php echo htmlspecialchars($contact['office_extension']); ?>
                                                            </p>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($contact['emergency_contact_name']): ?>
                                                            <hr class="my-2">
                                                            <h6 class="text-danger">Emergency Contact</h6>
                                                            <p class="mb-1">
                                                                <i class="fas fa-user-shield text-danger me-2"></i>
                                                                <?php echo htmlspecialchars($contact['emergency_contact_name']); ?>
                                                            </p>
                                                            <?php if ($contact['emergency_contact_number']): ?>
                                                            <p class="mb-1">
                                                                <i class="fas fa-phone-alt text-danger me-2"></i>
                                                                <a href="tel:<?php echo $contact['emergency_contact_number']; ?>" class="text-decoration-none text-danger">
                                                                    <?php echo htmlspecialchars($contact['emergency_contact_number']); ?>
                                                                </a>
                                                            </p>
                                                            <?php endif; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 pt-2 border-top">
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            Last verified: <?php echo date('M j, Y', strtotime($contact['last_verified'])); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Add Contact Tab -->
                                <div class="tab-pane fade" id="add-contact" role="tabpanel">
                                    <div class="row justify-content-center">
                                        <div class="col-lg-8">
                                            <h5 class="mb-4">
                                                <i class="fas fa-user-plus text-danger me-2"></i>
                                                Add Officer Contact Information
                                            </h5>
                                            
                                            <?php if (empty($available_officers)): ?>
                                            <div class="text-center py-5">
                                                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                                <h5>No officers available</h5>
                                                <p class="text-muted">Please add officers first before creating contact entries.</p>
                                                <a href="add_officers.php" class="btn btn-primary">
                                                    <i class="fas fa-user-plus me-2"></i>Add Officers
                                                </a>
                                            </div>
                                            <?php else: ?>
                                            <form method="POST" action="">
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="officer_id" class="form-label fw-semibold">
                                                                    Select Officer <span class="text-danger">*</span>
                                                                </label>
                                                                <select class="form-select" id="officer_id" name="officer_id" required>
                                                                    <option value="">Choose an officer...</option>
                                                                    <?php foreach ($available_officers as $officer): ?>
                                                                    <option value="<?php echo $officer['officer_id']; ?>">
                                                                        <?php echo htmlspecialchars($officer['rank'] . ' ' . $officer['first_name'] . ' ' . $officer['last_name'] . ' (' . $officer['regimental_number'] . ') - ' . $officer['station_name']); ?>
                                                                    </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="mobile_no" class="form-label fw-semibold">Mobile Number</label>
                                                                <input type="text" class="form-control" id="mobile_no" name="mobile_no" 
                                                                       placeholder="e.g., 0771234567">
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="whatsapp_no" class="form-label fw-semibold">WhatsApp Number</label>
                                                                <input type="text" class="form-control" id="whatsapp_no" name="whatsapp_no" 
                                                                       placeholder="e.g., 0771234567">
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="email_address" class="form-label fw-semibold">Email Address</label>
                                                                <input type="email" class="form-control" id="email_address" name="email_address" 
                                                                       placeholder="e.g., officer@police.lk">
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="home_telephone" class="form-label fw-semibold">Home Telephone</label>
                                                                <input type="text" class="form-control" id="home_telephone" name="home_telephone" 
                                                                       placeholder="e.g., 011-2345678">
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="office_extension" class="form-label fw-semibold">Office Extension</label>
                                                                <input type="text" class="form-control" id="office_extension" name="office_extension" 
                                                                       placeholder="e.g., 123">
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="emergency_contact_name" class="form-label fw-semibold">Emergency Contact Name</label>
                                                                <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" 
                                                                       placeholder="e.g., John Doe">
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="emergency_contact_number" class="form-label fw-semibold">Emergency Contact Number</label>
                                                                <input type="text" class="form-control" id="emergency_contact_number" name="emergency_contact_number" 
                                                                       placeholder="e.g., 0771234567">
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="text-center">
                                                            <button type="submit" name="create_contact" class="btn btn-danger btn-lg px-5">
                                                                <i class="fas fa-save me-2"></i>Add Contact Information
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Call Logs Tab -->
                                <div class="tab-pane fade" id="call-logs" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="mb-0">
                                            <i class="fas fa-phone-alt text-danger me-2"></i>
                                            Recent Call Logs (<?php echo count($recent_calls); ?> calls)
                                        </h5>
                                        <div>
                                            <button class="btn btn-outline-primary btn-sm me-2" onclick="exportCallLogs()">
                                                <i class="fas fa-download me-1"></i>Export
                                            </button>
                                            <input type="text" class="form-control form-control-sm d-inline-block w-auto" 
                                                   id="callSearch" placeholder="Search calls..." onkeyup="filterCalls()">
                                        </div>
                                    </div>
                                    
                                    <?php if (empty($recent_calls)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-phone-alt fa-4x text-muted mb-3"></i>
                                        <h5>No call logs found</h5>
                                        <p class="text-muted">Start logging important calls and communications.</p>
                                        <button class="btn btn-danger" onclick="document.getElementById('add-call-tab').click()">
                                            <i class="fas fa-plus me-2"></i>Log First Call
                                        </button>
                                    </div>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="callLogsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date & Time</th>
                                                    <th>Type</th>
                                                    <th>Phone Number</th>
                                                    <th>Officer</th>
                                                    <th>Duration</th>
                                                    <th>Priority</th>
                                                    <th>Purpose</th>
                                                    <th>Follow-up</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_calls as $call): ?>
                                                <tr class="call-row" 
                                                    data-search="<?php echo strtolower($call['phone_number'] . ' ' . $call['call_purpose'] . ' ' . ($call['first_name'] ? $call['first_name'] . ' ' . $call['last_name'] : '')); ?>">
                                                    <td>
                                                        <div class="fw-semibold"><?php echo date('M j, Y', strtotime($call['call_date'])); ?></div>
                                                        <small class="text-muted"><?php echo date('g:i A', strtotime($call['call_time'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $call['call_type'] === 'incoming' ? 'success' : 
                                                                ($call['call_type'] === 'outgoing' ? 'primary' : 'warning'); 
                                                        ?>">
                                                            <i class="fas fa-<?php 
                                                                echo $call['call_type'] === 'incoming' ? 'arrow-down' : 
                                                                    ($call['call_type'] === 'outgoing' ? 'arrow-up' : 'phone-slash'); 
                                                            ?> me-1"></i>
                                                            <?php echo ucfirst($call['call_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="tel:<?php echo $call['phone_number']; ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($call['phone_number']); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php if ($call['first_name']): ?>
                                                            <div class="fw-semibold"><?php echo htmlspecialchars($call['rank'] . ' ' . $call['first_name'] . ' ' . $call['last_name']); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($call['regimental_number']); ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">External Call</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($call['call_duration']) ?: '-'; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $call['priority_level'] === 'urgent' ? 'danger' : 
                                                                ($call['priority_level'] === 'high' ? 'warning' : 
                                                                ($call['priority_level'] === 'medium' ? 'info' : 'secondary')); 
                                                        ?>">
                                                            <?php echo ucfirst($call['priority_level']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span title="<?php echo htmlspecialchars($call['call_purpose']); ?>">
                                                            <?php echo htmlspecialchars(substr($call['call_purpose'], 0, 30)) . (strlen($call['call_purpose']) > 30 ? '...' : ''); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($call['follow_up_required'] === 'yes'): ?>
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-calendar-check me-1"></i>
                                                                <?php echo $call['follow_up_date'] ? date('M j', strtotime($call['follow_up_date'])) : 'Required'; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Add Call Log Tab -->
                                <div class="tab-pane fade" id="add-call" role="tabpanel">
                                    <div class="row justify-content-center">
                                        <div class="col-lg-8">
                                            <h5 class="mb-4">
                                                <i class="fas fa-plus text-danger me-2"></i>
                                                Log New Call
                                            </h5>
                                            
                                            <form method="POST" action="">
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="call_type" class="form-label fw-semibold">
                                                                    Call Type <span class="text-danger">*</span>
                                                                </label>
                                                                <select class="form-select" id="call_type" name="call_type" required>
                                                                    <option value="">Select call type...</option>
                                                                    <option value="incoming">Incoming Call</option>
                                                                    <option value="outgoing">Outgoing Call</option>
                                                                    <option value="missed">Missed Call</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="phone_number" class="form-label fw-semibold">
                                                                    Phone Number <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" class="form-control" id="phone_number" name="phone_number" 
                                                                       placeholder="e.g., 0771234567" required>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="call_date" class="form-label fw-semibold">
                                                                    Call Date <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="date" class="form-control" id="call_date" name="call_date" 
                                                                       value="<?php echo date('Y-m-d'); ?>" required>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="call_time" class="form-label fw-semibold">
                                                                    Call Time <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="time" class="form-control" id="call_time" name="call_time" 
                                                                       value="<?php echo date('H:i'); ?>" required>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="call_officer_id" class="form-label fw-semibold">Related Officer</label>
                                                                <select class="form-select" id="call_officer_id" name="call_officer_id">
                                                                    <option value="">No specific officer...</option>
                                                                    <?php foreach ($available_officers as $officer): ?>
                                                                    <option value="<?php echo $officer['officer_id']; ?>">
                                                                        <?php echo htmlspecialchars($officer['rank'] . ' ' . $officer['first_name'] . ' ' . $officer['last_name']); ?>
                                                                    </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="call_duration" class="form-label fw-semibold">Call Duration</label>
                                                                <input type="text" class="form-control" id="call_duration" name="call_duration" 
                                                                       placeholder="e.g., 5 min">
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="priority_level" class="form-label fw-semibold">Priority Level</label>
                                                                <select class="form-select" id="priority_level" name="priority_level">
                                                                    <option value="low">Low</option>
                                                                    <option value="medium" selected>Medium</option>
                                                                    <option value="high">High</option>
                                                                    <option value="urgent">Urgent</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="follow_up_required" class="form-label fw-semibold">Follow-up Required</label>
                                                                <select class="form-select" id="follow_up_required" name="follow_up_required" onchange="toggleFollowUpDate()">
                                                                    <option value="no">No</option>
                                                                    <option value="yes">Yes</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3" id="followUpDateDiv" style="display: none;">
                                                                <label for="follow_up_date" class="form-label fw-semibold">Follow-up Date</label>
                                                                <input type="date" class="form-control" id="follow_up_date" name="follow_up_date">
                                                            </div>
                                                            
                                                            <div class="col-12 mb-3">
                                                                <label for="call_purpose" class="form-label fw-semibold">Call Purpose</label>
                                                                <textarea class="form-control" id="call_purpose" name="call_purpose" rows="2" 
                                                                          placeholder="Brief description of the call purpose"></textarea>
                                                            </div>
                                                            
                                                            <div class="col-12 mb-3">
                                                                <label for="call_notes" class="form-label fw-semibold">Call Notes</label>
                                                                <textarea class="form-control" id="call_notes" name="call_notes" rows="3" 
                                                                          placeholder="Detailed notes about the call"></textarea>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="text-center">
                                                            <button type="submit" name="add_call_log" class="btn btn-danger btn-lg px-5">
                                                                <i class="fas fa-phone-alt me-2"></i>Log Call
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
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

<!-- Edit Contact Modal -->
<div class="modal fade" id="editContactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Contact Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="register_id" id="editContactId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Mobile Number</label>
                            <input type="text" class="form-control" name="mobile_no" id="editMobileNo">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">WhatsApp Number</label>
                            <input type="text" class="form-control" name="whatsapp_no" id="editWhatsappNo">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" name="email_address" id="editEmailAddress">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Home Telephone</label>
                            <input type="text" class="form-control" name="home_telephone" id="editHomeTelephone">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Office Extension</label>
                            <input type="text" class="form-control" name="office_extension" id="editOfficeExtension">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Emergency Contact Name</label>
                            <input type="text" class="form-control" name="emergency_contact_name" id="editEmergencyContactName">
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Emergency Contact Number</label>
                            <input type="text" class="form-control" name="emergency_contact_number" id="editEmergencyContactNumber">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_contact" class="btn btn-danger">Update Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Contact Modal -->
<div class="modal fade" id="deleteContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this contact information?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="register_id" id="deleteContactId">
                    <button type="submit" name="delete_contact" class="btn btn-danger">Delete Contact</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: #dc3545;
    border-bottom: 3px solid #dc3545;
    background: none;
}

.contact-card {
    transition: all 0.3s ease;
}

.contact-card:hover {
    transform: translateY(-2px);
}

.call-row {
    transition: all 0.3s ease;
}

.call-row:hover {
    background-color: rgba(220, 53, 69, 0.05);
}

@media (max-width: 768px) {
    .nav-tabs {
        flex-direction: column;
    }
    
    .nav-tabs .nav-link {
        text-align: center;
        border-bottom: 1px solid #dee2e6;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
}
</style>

<script>
// Store contact data for editing
const contactsData = <?php echo json_encode($telephone_register); ?>;

function editContact(registerId) {
    const contact = contactsData.find(c => c.register_id == registerId);
    if (contact) {
        document.getElementById('editContactId').value = contact.register_id;
        document.getElementById('editMobileNo').value = contact.mobile_no || '';
        document.getElementById('editWhatsappNo').value = contact.whatsapp_no || '';
        document.getElementById('editEmailAddress').value = contact.email_address || '';
        document.getElementById('editHomeTelephone').value = contact.home_telephone || '';
        document.getElementById('editOfficeExtension').value = contact.office_extension || '';
        document.getElementById('editEmergencyContactName').value = contact.emergency_contact_name || '';
        document.getElementById('editEmergencyContactNumber').value = contact.emergency_contact_number || '';
        
        const modal = new bootstrap.Modal(document.getElementById('editContactModal'));
        modal.show();
    }
}

function deleteContact(registerId) {
    document.getElementById('deleteContactId').value = registerId;
    const modal = new bootstrap.Modal(document.getElementById('deleteContactModal'));
    modal.show();
}

function toggleFollowUpDate() {
    const followUpRequired = document.getElementById('follow_up_required').value;
    const followUpDateDiv = document.getElementById('followUpDateDiv');
    
    if (followUpRequired === 'yes') {
        followUpDateDiv.style.display = 'block';
        document.getElementById('follow_up_date').required = true;
    } else {
        followUpDateDiv.style.display = 'none';
        document.getElementById('follow_up_date').required = false;
        document.getElementById('follow_up_date').value = '';
    }
}

function filterContacts() {
    const searchTerm = document.getElementById('contactSearch').value.toLowerCase();
    const contactCards = document.querySelectorAll('.contact-card');
    
    contactCards.forEach(card => {
        const searchData = card.getAttribute('data-search');
        if (searchData.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterCalls() {
    const searchTerm = document.getElementById('callSearch').value.toLowerCase();
    const callRows = document.querySelectorAll('.call-row');
    
    callRows.forEach(row => {
        const searchData = row.getAttribute('data-search');
        if (searchData.includes(searchTerm)) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
}

function exportContacts() {
    let csv = 'Rank,Name,Regimental Number,Mobile,WhatsApp,Email,Home Phone,Station,Branch\n';
    
    contactsData.forEach(contact => {
        csv += `"${contact.rank}","${contact.first_name} ${contact.last_name}","${contact.regimental_number}","${contact.mobile_no || ''}","${contact.whatsapp_no || ''}","${contact.email_address || ''}","${contact.home_telephone || ''}","${contact.station_name}","${contact.branch_name || ''}"\n`;
    });
    
    downloadCSV(csv, 'contacts_directory.csv');
}

function exportCallLogs() {
    const calls = <?php echo json_encode($recent_calls); ?>;
    let csv = 'Date,Time,Type,Phone Number,Officer,Duration,Priority,Purpose\n';
    
    calls.forEach(call => {
        const officerName = call.first_name ? `${call.rank} ${call.first_name} ${call.last_name}` : 'External Call';
        csv += `"${call.call_date}","${call.call_time}","${call.call_type}","${call.phone_number}","${officerName}","${call.call_duration || ''}","${call.priority_level}","${call.call_purpose || ''}"\n`;
    });
    
    downloadCSV(csv, 'call_logs.csv');
}

function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
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