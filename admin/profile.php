<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

$error_message = '';
$success_message = '';

// Get current user information
$user = getUserInfo();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = cleanInput($_POST['first_name']);
        $last_name = cleanInput($_POST['last_name']);
        $email = cleanInput($_POST['email']);
        
        // Validation
        if (empty($first_name) || empty($last_name) || empty($email)) {
            $error_message = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address.';
        } else {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error_message = 'This email address is already in use by another user.';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$first_name, $last_name, $email, $_SESSION['user_id']]);
                    
                    $success_message = 'Profile updated successfully.';
                    $user = getUserInfo(); // Refresh user data
                } catch (PDOException $e) {
                    $error_message = 'Error updating profile: ' . $e->getMessage();
                }
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = 'Please fill in all password fields.';
        } elseif (strlen($new_password) < 6) {
            $error_message = 'New password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error_message = 'Current password is incorrect.';
        } else {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                $success_message = 'Password changed successfully.';
            } catch (PDOException $e) {
                $error_message = 'Error changing password: ' . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['update_picture'])) {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../' . PROFILE_PICS_PATH;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file = $_FILES['profile_picture'];
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array(strtolower($file_extension), $allowed_extensions)) {
                $error_message = 'Invalid file type. Please upload JPG, PNG, or GIF images only.';
            } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                $error_message = 'File size too large. Maximum size is 5MB.';
            } else {
                $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Delete old profile picture if it's not the default
                    if ($user['profile_picture'] !== 'default.jpg') {
                        $old_file = $upload_dir . $user['profile_picture'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                        $stmt->execute([$new_filename, $_SESSION['user_id']]);
                        
                        $success_message = 'Profile picture updated successfully.';
                        $user = getUserInfo(); // Refresh user data
                    } catch (PDOException $e) {
                        $error_message = 'Error updating profile picture in database.';
                    }
                } else {
                    $error_message = 'Error uploading file. Please try again.';
                }
            }
        } else {
            $error_message = 'Please select a valid image file.';
        }
    }
}

// Get user statistics
$stats = [
    'blogs_count' => $pdo->prepare("SELECT COUNT(*) FROM blogs WHERE user_id = ?"),
    'bookmarks_count' => $pdo->prepare("SELECT COUNT(*) FROM user_bookmarks WHERE user_id = ?"),
    'comments_count' => $pdo->prepare("SELECT COUNT(*) FROM blog_comments WHERE user_id = ?"),
    'likes_given' => $pdo->prepare("SELECT COUNT(*) FROM blog_likes WHERE user_id = ?")
];

foreach ($stats as $key => $stmt) {
    $stmt->execute([$_SESSION['user_id']]);
    $stats[$key] = $stmt->fetchColumn();
}

// Get recent activity
$recent_blogs = $pdo->prepare("SELECT * FROM blogs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recent_blogs->execute([$_SESSION['user_id']]);
$recent_blogs = $recent_blogs->fetchAll();

$recent_bookmarks = $pdo->prepare("
    SELECT ub.*, 
           CASE 
               WHEN ub.table_name = 'penal_code' THEN pc.section_name
               WHEN ub.table_name = 'criminal_procedure_code' THEN cpc.section_name
               WHEN ub.table_name = 'evidence_ordinance' THEN eo.section_name
           END as section_name,
           CASE 
               WHEN ub.table_name = 'penal_code' THEN pc.section_number
               WHEN ub.table_name = 'criminal_procedure_code' THEN cpc.section_number
               WHEN ub.table_name = 'evidence_ordinance' THEN eo.section_number
           END as section_number
    FROM user_bookmarks ub
    LEFT JOIN penal_code pc ON ub.table_name = 'penal_code' AND ub.section_id = pc.id
    LEFT JOIN criminal_procedure_code cpc ON ub.table_name = 'criminal_procedure_code' AND ub.section_id = cpc.id
    LEFT JOIN evidence_ordinance eo ON ub.table_name = 'evidence_ordinance' AND ub.section_id = eo.id
    WHERE ub.user_id = ? 
    ORDER BY ub.created_at DESC 
    LIMIT 5
");
$recent_bookmarks->execute([$_SESSION['user_id']]);
$recent_bookmarks = $recent_bookmarks->fetchAll();

$page_title = "Admin Profile";
include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="admin_index.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <img src="../<?php echo PROFILE_PICS_PATH . $user['profile_picture']; ?>" 
                     class="rounded-circle me-3" width="80" height="80" alt="Profile Picture">
                <div>
                    <h2 class="mb-1">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        <span class="badge bg-danger ms-2">Administrator</span>
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar me-1"></i>Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
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

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Profile Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-semibold">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label fw-semibold">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role</label>
                            <input type="text" class="form-control" value="Administrator" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Account Status</label>
                            <input type="text" class="form-control" value="Active" disabled>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-2"></i>
                        Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="passwordForm">
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-info">Recent Blogs</h6>
                            <?php if (!empty($recent_blogs)): ?>
                                <?php foreach ($recent_blogs as $blog): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-blog text-muted me-2"></i>
                                    <div class="flex-grow-1">
                                        <a href="../blog_detail.php?id=<?php echo $blog['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($blog['title']); ?>
                                        </a>
                                        <br><small class="text-muted"><?php echo date('M j, Y', strtotime($blog['created_at'])); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small">No blogs published yet.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">Recent Bookmarks</h6>
                            <?php if (!empty($recent_bookmarks)): ?>
                                <?php foreach ($recent_bookmarks as $bookmark): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-bookmark text-muted me-2"></i>
                                    <div class="flex-grow-1">
                                        <span class="small fw-semibold">
                                            Section <?php echo htmlspecialchars($bookmark['section_number']); ?>
                                        </span>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($bookmark['section_name']); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small">No bookmarks saved yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Profile Picture -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-camera me-2"></i>
                        Profile Picture
                    </h6>
                </div>
                <div class="card-body text-center">
                    <img src="../<?php echo PROFILE_PICS_PATH . $user['profile_picture']; ?>" 
                         class="rounded-circle mb-3" width="150" height="150" alt="Profile Picture" id="profilePreview">
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" class="form-control" name="profile_picture" accept="image/*" 
                                   onchange="previewImage(this)">
                            <div class="form-text">JPG, PNG, GIF (Max 5MB)</div>
                        </div>
                        <button type="submit" name="update_picture" class="btn btn-success">
                            <i class="fas fa-upload me-2"></i>Update Picture
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Your Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h4 class="text-primary mb-0"><?php echo number_format($stats['blogs_count']); ?></h4>
                            <small class="text-muted">Blogs</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success mb-0"><?php echo number_format($stats['bookmarks_count']); ?></h4>
                            <small class="text-muted">Bookmarks</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning mb-0"><?php echo number_format($stats['comments_count']); ?></h4>
                            <small class="text-muted">Comments</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info mb-0"><?php echo number_format($stats['likes_given']); ?></h4>
                            <small class="text-muted">Likes Given</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Settings -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Account Settings
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="../my_bookmarks.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-bookmark me-2"></i>
                            Manage Bookmarks
                        </a>
                        <a href="../my_blogs.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-blog me-2"></i>
                            My Blogs
                        </a>
                        <a href="admin_index.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Admin Dashboard
                        </a>
                        <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 15px;
    overflow: hidden;
}

.card-header {
    border-bottom: none;
}

#profilePreview {
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.list-group-item {
    border: none;
    padding: 0.75rem 0;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item-action:hover {
    background-color: rgba(0,0,0,0.05);
}

@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    #profilePreview {
        width: 120px;
        height: 120px;
    }
}
</style>

<script>
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleButton = passwordField.nextElementSibling.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleButton.classList.remove('fa-eye');
        toggleButton.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleButton.classList.remove('fa-eye-slash');
        toggleButton.classList.add('fa-eye');
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Password confirmation validation
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.getElementById('passwordForm');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePasswords() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    newPassword.addEventListener('change', validatePasswords);
    confirmPassword.addEventListener('keyup', validatePasswords);
    
    // Password strength indicator
    newPassword.addEventListener('input', function() {
        const password = this.value;
        const strength = getPasswordStrength(password);
        
        // Remove existing strength indicators
        const existingIndicator = this.parentElement.parentElement.querySelector('.password-strength');
        if (existingIndicator) {
            existingIndicator.remove();
        }
        
        if (password.length > 0) {
            const indicator = document.createElement('div');
            indicator.className = 'password-strength mt-1';
            indicator.innerHTML = `
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-${strength.color}" style="width: ${strength.percentage}%"></div>
                </div>
                <small class="text-${strength.color}">${strength.text}</small>
            `;
            this.parentElement.parentElement.appendChild(indicator);
        }
    });
});

function getPasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 6) score++;
    if (password.length >= 8) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^a-zA-Z\d]/.test(password)) score++;
    
    switch (score) {
        case 0:
        case 1:
            return { color: 'danger', percentage: 20, text: 'Very Weak' };
        case 2:
            return { color: 'warning', percentage: 40, text: 'Weak' };
        case 3:
            return { color: 'info', percentage: 60, text: 'Medium' };
        case 4:
            return { color: 'primary', percentage: 80, text: 'Strong' };
        case 5:
            return { color: 'success', percentage: 100, text: 'Very Strong' };
        default:
            return { color: 'danger', percentage: 0, text: 'Very Weak' };
    }
}

// Auto-dismiss alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert.querySelector('.btn-close')) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000);

// Smooth scroll to sections on hash change
window.addEventListener('hashchange', function() {
    const target = document.querySelector(window.location.hash);
    if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<?php include '../includes/footer.php'; ?>