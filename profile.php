<?php
require_once 'config/config.php';

// Require login
requireLogin();

$error_message = '';
$success_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = cleanInput($_POST['first_name']);
    $last_name = cleanInput($_POST['last_name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($first_name) || empty($last_name)) {
        $error_message = 'First name and last name are required.';
    } else {
        $user = getUserInfo();
        $update_fields = [];
        $update_values = [];
        
        // Check if name needs updating
        if ($first_name !== $user['first_name'] || $last_name !== $user['last_name']) {
            $update_fields[] = 'first_name = ?';
            $update_fields[] = 'last_name = ?';
            $update_values[] = $first_name;
            $update_values[] = $last_name;
        }
        
        // Handle password change
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $error_message = 'Current password is required to change password.';
            } elseif (!password_verify($current_password, $user['password'])) {
                $error_message = 'Current password is incorrect.';
            } elseif (strlen($new_password) < 6) {
                $error_message = 'New password must be at least 6 characters long.';
            } elseif ($new_password !== $confirm_password) {
                $error_message = 'New passwords do not match.';
            } else {
                $update_fields[] = 'password = ?';
                $update_values[] = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }
        
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = PROFILE_PICS_PATH;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array(strtolower($file_extension), $allowed_extensions)) {
                $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                    // Delete old profile picture if it's not the default
                    if ($user['profile_picture'] !== 'default.jpg' && file_exists($upload_dir . $user['profile_picture'])) {
                        unlink($upload_dir . $user['profile_picture']);
                    }
                    
                    $update_fields[] = 'profile_picture = ?';
                    $update_values[] = $new_filename;
                } else {
                    $error_message = 'Failed to upload profile picture.';
                }
            } else {
                $error_message = 'Invalid file type. Please upload JPG, PNG, or GIF files only.';
            }
        }
        
        // Update database if no errors and there are fields to update
        if (empty($error_message) && !empty($update_fields)) {
            try {
                $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
                $update_values[] = $_SESSION['user_id'];
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($update_values);
                
                $success_message = 'Profile updated successfully!';
                
                // Update session name if name was changed
                if (in_array('first_name = ?', $update_fields)) {
                    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                }
                
            } catch (PDOException $e) {
                $error_message = 'Failed to update profile. Please try again.';
            }
        } elseif (empty($error_message) && empty($update_fields)) {
            $error_message = 'No changes detected.';
        }
    }
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $password = $_POST['delete_password'];
    $user = getUserInfo();
    
    if (empty($password)) {
        $error_message = 'Password is required to delete account.';
    } elseif (!password_verify($password, $user['password'])) {
        $error_message = 'Incorrect password.';
    } else {
        try {
            // Delete user data
            $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            $pdo->prepare("DELETE FROM user_bookmarks WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            $pdo->prepare("DELETE FROM blog_likes WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            $pdo->prepare("DELETE FROM blog_comments WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            $pdo->prepare("DELETE FROM blogs WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$_SESSION['user_id']]);
            
            // Delete profile picture
            if ($user['profile_picture'] !== 'default.jpg' && file_exists(PROFILE_PICS_PATH . $user['profile_picture'])) {
                unlink(PROFILE_PICS_PATH . $user['profile_picture']);
            }
            
            // Logout and redirect
            session_destroy();
            header('Location: index.php?deleted=1');
            exit();
            
        } catch (PDOException $e) {
            $error_message = 'Failed to delete account. Please try again.';
        }
    }
}

$user = getUserInfo();

// Get user statistics
$user_stats = [
    'bookmarks' => $pdo->prepare("SELECT COUNT(*) FROM user_bookmarks WHERE user_id = ?"),
    'blogs' => $pdo->prepare("SELECT COUNT(*) FROM blogs WHERE user_id = ?"),
    'comments' => $pdo->prepare("SELECT COUNT(*) FROM blog_comments WHERE user_id = ?"),
    'likes_given' => $pdo->prepare("SELECT COUNT(*) FROM blog_likes WHERE user_id = ?")
];

foreach ($user_stats as $key => $stmt) {
    $stmt->execute([$_SESSION['user_id']]);
    $user_stats[$key] = $stmt->fetchColumn();
}

$page_title = "My Profile";
include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2>
                <i class="fas fa-user me-2 text-primary"></i>
                My Profile
            </h2>
            <p class="text-muted">Manage your account settings and preferences</p>
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
        <!-- Profile Overview -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="<?php echo PROFILE_PICS_PATH . $user['profile_picture']; ?>" 
                             class="rounded-circle border border-3 border-primary" 
                             width="120" height="120" alt="Profile Picture" style="object-fit: cover;">
                        <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle"
                              style="width: 30px; height: 30px;" title="Active">
                            <i class="fas fa-check text-white" style="font-size: 0.8rem; margin-top: 6px;"></i>
                        </span>
                    </div>
                    <h4 class="mb-1"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?> fs-6">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- User Statistics -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Activity Overview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h4 class="text-warning mb-1"><?php echo number_format($user_stats['bookmarks']); ?></h4>
                            <small class="text-muted">Bookmarks</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-primary mb-1"><?php echo number_format($user_stats['blogs']); ?></h4>
                            <small class="text-muted">Blog Posts</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1"><?php echo number_format($user_stats['comments']); ?></h4>
                            <small class="text-muted">Comments</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info mb-1"><?php echo number_format($user_stats['likes_given']); ?></h4>
                            <small class="text-muted">Likes Given</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Settings -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Profile
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="" enctype="multipart/form-data">
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
                            <input type="email" class="form-control" id="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <div class="form-text">Email cannot be changed. Contact admin if needed.</div>
                        </div>

                        <div class="mb-4">
                            <label for="profile_picture" class="form-label fw-semibold">Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" 
                                   accept="image/*">
                            <div class="form-text">Accepted formats: JPG, PNG, GIF (Max 5MB)</div>
                        </div>

                        <hr class="my-4">

                        <!-- Password Change Section -->
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-lock me-2"></i>
                            Change Password (Optional)
                        </h6>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="6">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       minlength="6">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount()">
                                <i class="fas fa-trash me-1"></i>Delete Account
                            </button>
                            <button type="submit" name="update_profile" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Activity
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    // Get recent user activities
                    $recent_activities = [];
                    
                    // Recent bookmarks
                    $stmt = $pdo->prepare("SELECT 'bookmark' as type, table_name, created_at FROM user_bookmarks WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
                    $stmt->execute([$_SESSION['user_id']]);
                    $recent_activities = array_merge($recent_activities, $stmt->fetchAll());
                    
                    // Recent blogs
                    $stmt = $pdo->prepare("SELECT 'blog' as type, title as table_name, created_at FROM blogs WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
                    $stmt->execute([$_SESSION['user_id']]);
                    $recent_activities = array_merge($recent_activities, $stmt->fetchAll());
                    
                    // Sort by date
                    usort($recent_activities, function($a, $b) {
                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                    });
                    
                    $recent_activities = array_slice($recent_activities, 0, 5);
                    ?>
                    
                    <?php if (!empty($recent_activities)): ?>
                        <?php foreach ($recent_activities as $activity): ?>
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0 me-3">
                                <?php if ($activity['type'] === 'bookmark'): ?>
                                    <i class="fas fa-bookmark text-warning"></i>
                                <?php else: ?>
                                    <i class="fas fa-pen text-primary"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <small>
                                    <?php if ($activity['type'] === 'bookmark'): ?>
                                        Bookmarked a section from <?php echo ucwords(str_replace('_', ' ', $activity['table_name'])); ?>
                                    <?php else: ?>
                                        Published blog: "<?php echo htmlspecialchars(substr($activity['table_name'], 0, 30)) . (strlen($activity['table_name']) > 30 ? '...' : ''); ?>"
                                    <?php endif; ?>
                                </small>
                                <br>
                                <small class="text-muted"><?php echo date('M j, Y', strtotime($activity['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning!</strong> This action cannot be undone. All your data including bookmarks, blogs, and comments will be permanently deleted.
                </div>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Enter your password to confirm:</label>
                        <input type="password" class="form-control" id="delete_password" name="delete_password" required>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_account" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Delete My Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: all 0.3s ease;
}

.border-3 {
    border-width: 3px !important;
}

.position-absolute i {
    line-height: 1;
}

@media (max-width: 768px) {
    .card-body.p-4 {
        padding: 1.5rem !important;
    }
}
</style>

<script>
function confirmDeleteAccount() {
    const modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
    modal.show();
}

// Password confirmation validation
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }
    
    newPassword.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
    
    // Profile picture preview
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (5MB limit)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                // You could show a preview here
                console.log('Profile picture selected:', file.name);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Auto-dismiss alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>

<?php include 'includes/footer.php'; ?>