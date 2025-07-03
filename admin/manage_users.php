<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

$error_message = '';
$success_message = '';
$search_query = '';
$role_filter = '';
$current_page = 1;
$items_per_page = 25;

// Handle search and filters
if (isset($_GET['search'])) {
    $search_query = cleanInput($_GET['search']);
}
if (isset($_GET['role'])) {
    $role_filter = cleanInput($_GET['role']);
}
if (isset($_GET['page'])) {
    $current_page = max(1, intval($_GET['page']));
}

$offset = ($current_page - 1) * $items_per_page;

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_status']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $new_status = intval($_POST['new_status']);
        
        // Don't allow admin to disable themselves
        if ($user_id == $_SESSION['user_id']) {
            $error_message = 'You cannot change your own status.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET email_verified = ? WHERE id = ?");
                $stmt->execute([$new_status, $user_id]);
                $success_message = $new_status ? 'User activated successfully.' : 'User deactivated successfully.';
            } catch (PDOException $e) {
                $error_message = 'Error updating user status.';
            }
        }
    }
    
    if (isset($_POST['change_role']) && isset($_POST['user_id']) && isset($_POST['new_role'])) {
        $user_id = intval($_POST['user_id']);
        $new_role = cleanInput($_POST['new_role']);
        
        // Don't allow admin to change their own role
        if ($user_id == $_SESSION['user_id']) {
            $error_message = 'You cannot change your own role.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $user_id]);
                $success_message = 'User role updated successfully.';
            } catch (PDOException $e) {
                $error_message = 'Error updating user role.';
            }
        }
    }
    
    if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        
        // Don't allow admin to delete themselves
        if ($user_id == $_SESSION['user_id']) {
            $error_message = 'You cannot delete your own account.';
        } else {
            try {
                // Delete user sessions first
                $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Delete user bookmarks
                $stmt = $pdo->prepare("DELETE FROM user_bookmarks WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Delete blog likes
                $stmt = $pdo->prepare("DELETE FROM blog_likes WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Delete blog comments
                $stmt = $pdo->prepare("DELETE FROM blog_comments WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Update blogs to show as deleted user
                $stmt = $pdo->prepare("UPDATE blogs SET user_id = NULL WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Delete the user
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                $success_message = 'User deleted successfully.';
            } catch (PDOException $e) {
                $error_message = 'Error deleting user: ' . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['bulk_action']) && isset($_POST['selected_users'])) {
        $action = cleanInput($_POST['bulk_action']);
        $selected_users = array_map('intval', $_POST['selected_users']);
        
        // Remove current admin from selection
        $selected_users = array_filter($selected_users, function($id) {
            return $id != $_SESSION['user_id'];
        });
        
        if (!empty($selected_users)) {
            try {
                $placeholders = str_repeat('?,', count($selected_users) - 1) . '?';
                
                switch ($action) {
                    case 'activate':
                        $stmt = $pdo->prepare("UPDATE users SET email_verified = 1 WHERE id IN ($placeholders)");
                        $stmt->execute($selected_users);
                        $success_message = count($selected_users) . ' users activated.';
                        break;
                        
                    case 'deactivate':
                        $stmt = $pdo->prepare("UPDATE users SET email_verified = 0 WHERE id IN ($placeholders)");
                        $stmt->execute($selected_users);
                        $success_message = count($selected_users) . ' users deactivated.';
                        break;
                        
                    case 'make_admin':
                        $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id IN ($placeholders)");
                        $stmt->execute($selected_users);
                        $success_message = count($selected_users) . ' users promoted to admin.';
                        break;
                        
                    case 'make_user':
                        $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id IN ($placeholders)");
                        $stmt->execute($selected_users);
                        $success_message = count($selected_users) . ' users demoted to regular users.';
                        break;
                }
            } catch (PDOException $e) {
                $error_message = 'Error performing bulk action.';
            }
        }
    }
}

// Build query conditions
$where_conditions = [];
$params = [];

if (!empty($search_query)) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($role_filter)) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM users $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_users = $stmt->fetchColumn();

// Get users
$users_sql = "SELECT u.*, 
              (SELECT COUNT(*) FROM user_sessions WHERE user_id = u.id AND expires_at > NOW()) as active_sessions,
              (SELECT COUNT(*) FROM blogs WHERE user_id = u.id) as blog_count,
              (SELECT COUNT(*) FROM user_bookmarks WHERE user_id = u.id) as bookmark_count
              FROM users u 
              $where_clause 
              ORDER BY u.created_at DESC 
              LIMIT $offset, $items_per_page";
$stmt = $pdo->prepare($users_sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Calculate pagination
$total_pages = ceil($total_users / $items_per_page);

// Get statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'active_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE email_verified = 1")->fetchColumn(),
    'admin_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
    'users_today' => $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn()
];

$page_title = "Manage Users - Admin";
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="admin_index.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Manage Users</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>
                        <i class="fas fa-users me-2 text-primary"></i>
                        Manage Users
                    </h2>
                    <p class="text-muted">View and manage user accounts, roles, and permissions</p>
                </div>
                <div>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus me-1"></i>Add New User
                    </button>
                    <button class="btn btn-info" onclick="exportUsers()">
                        <i class="fas fa-download me-1"></i>Export Users
                    </button>
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

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="mb-0"><?php echo number_format($stats['total_users']); ?></h4>
                    <small class="text-muted">Total Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                    <h4 class="mb-0"><?php echo number_format($stats['active_users']); ?></h4>
                    <small class="text-muted">Active Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-shield fa-2x text-warning mb-2"></i>
                    <h4 class="mb-0"><?php echo number_format($stats['admin_users']); ?></h4>
                    <small class="text-muted">Administrators</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-plus fa-2x text-info mb-2"></i>
                    <h4 class="mb-0"><?php echo number_format($stats['users_today']); ?></h4>
                    <small class="text-muted">New Today</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search_query); ?>"
                                       placeholder="Search by name or email...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="role" class="form-select">
                                <option value="">All Roles</option>
                                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>Users</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admins</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="manage_users.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Users
                    <?php if (!empty($search_query) || !empty($role_filter)): ?>
                        <small class="text-muted">
                            - Filtered results (<?php echo number_format(count($users)); ?> of <?php echo number_format($total_users); ?>)
                        </small>
                    <?php endif; ?>
                </h5>
                <div>
                    <select id="bulkAction" class="form-select form-select-sm d-inline-block w-auto me-2" style="display: none !important;">
                        <option value="">Bulk Actions</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="make_admin">Make Admin</option>
                        <option value="make_user">Make User</option>
                    </select>
                    <button type="button" class="btn btn-warning btn-sm" onclick="applyBulkAction()" id="bulkActionBtn" style="display: none;">
                        <i class="fas fa-cogs me-1"></i>Apply
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($users)): ?>
            <form id="bulkForm" method="POST">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th width="60">Avatar</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th width="100">Role</th>
                                <th width="100">Status</th>
                                <th width="100">Activity</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr <?php echo $user['id'] == $_SESSION['user_id'] ? 'class="table-warning"' : ''; ?>>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <input type="checkbox" class="form-check-input user-checkbox" 
                                           name="selected_users[]" value="<?php echo $user['id']; ?>">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <img src="../<?php echo PROFILE_PICS_PATH . $user['profile_picture']; ?>" 
                                         class="rounded-circle" width="40" height="40" alt="Avatar">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-info ms-1">You</span>
                                    <?php endif; ?>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-blog me-1"></i><?php echo $user['blog_count']; ?> blogs
                                        <i class="fas fa-bookmark ms-2 me-1"></i><?php echo $user['bookmark_count']; ?> bookmarks
                                    </small>
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo htmlspecialchars($user['email']); ?></span>
                                    <br>
                                    <small class="text-muted">
                                        Joined: <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['email_verified'] ? 'success' : 'warning'; ?>">
                                        <?php echo $user['email_verified'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['active_sessions'] > 0): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-circle me-1"></i>Online
                                        </span>
                                        <br><small class="text-muted"><?php echo $user['active_sessions']; ?> sessions</small>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Offline</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button class="dropdown-item" type="button"
                                                        onclick="toggleUserStatus(<?php echo $user['id']; ?>, <?php echo $user['email_verified'] ? 0 : 1; ?>)">
                                                    <i class="fas fa-toggle-<?php echo $user['email_verified'] ? 'off' : 'on'; ?> me-2"></i>
                                                    <?php echo $user['email_verified'] ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" type="button"
                                                        onclick="changeUserRole(<?php echo $user['id']; ?>, '<?php echo $user['role'] === 'admin' ? 'user' : 'admin'; ?>')">
                                                    <i class="fas fa-user-<?php echo $user['role'] === 'admin' ? 'minus' : 'shield'; ?> me-2"></i>
                                                    Make <?php echo $user['role'] === 'admin' ? 'User' : 'Admin'; ?>
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" type="button"
                                                        onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo addslashes($user['first_name'] . ' ' . $user['last_name']); ?>')">
                                                    <i class="fas fa-trash me-2"></i>Delete User
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-muted small">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h5>No users found</h5>
                <p class="text-muted">
                    <?php if (!empty($search_query) || !empty($role_filter)): ?>
                        Try adjusting your search criteria or <a href="manage_users.php">view all users</a>.
                    <?php else: ?>
                        No users have registered yet.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing <?php echo number_format($offset + 1); ?> to 
                    <?php echo number_format(min($offset + $items_per_page, $total_users)); ?> 
                    of <?php echo number_format($total_users); ?> users
                </div>
                <nav aria-label="Users pagination">
                    <ul class="pagination mb-0">
                        <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user?</p>
                <p><strong id="deleteUserName"></strong></p>
                <p class="text-danger small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Warning:</strong> This action cannot be undone. All user data including blogs, comments, and bookmarks will be deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="submit" name="delete_user" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="addUserForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addUserForm" name="add_user" class="btn btn-success">
                    <i class="fas fa-user-plus me-1"></i>Add User
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.875rem;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>

<script>
// Hidden form for actions
function createHiddenForm(action, userId, value = null) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = action;
    actionInput.value = '1';
    form.appendChild(actionInput);
    
    const userIdInput = document.createElement('input');
    userIdInput.type = 'hidden';
    userIdInput.name = 'user_id';
    userIdInput.value = userId;
    form.appendChild(userIdInput);
    
    if (value !== null) {
        const valueInput = document.createElement('input');
        valueInput.type = 'hidden';
        if (action === 'toggle_status') {
            valueInput.name = 'new_status';
        } else if (action === 'change_role') {
            valueInput.name = 'new_role';
        }
        valueInput.value = value;
        form.appendChild(valueInput);
    }
    
    document.body.appendChild(form);
    form.submit();
}

function toggleUserStatus(userId, newStatus) {
    const action = newStatus ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        createHiddenForm('toggle_status', userId, newStatus);
    }
}

function changeUserRole(userId, newRole) {
    const action = newRole === 'admin' ? 'promote to admin' : 'demote to user';
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        createHiddenForm('change_role', userId, newRole);
    }
}

function deleteUser(userId, userName) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = userName;
    const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    modal.show();
}

// Bulk actions
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

document.querySelectorAll('.user-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkActions);
});

function toggleBulkActions() {
    const selectedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkAction = document.getElementById('bulkAction');
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    
    if (selectedBoxes.length > 0) {
        bulkAction.style.display = 'inline-block';
        bulkActionBtn.style.display = 'inline-block';
    } else {
        bulkAction.style.display = 'none';
        bulkActionBtn.style.display = 'none';
    }
}

function applyBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const selectedBoxes = document.querySelectorAll('.user-checkbox:checked');
    
    if (!action) {
        alert('Please select an action.');
        return;
    }
    
    if (selectedBoxes.length === 0) {
        alert('Please select users to perform the action on.');
        return;
    }
    
    if (confirm(`Are you sure you want to ${action} ${selectedBoxes.length} selected users?`)) {
        const form = document.getElementById('bulkForm');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'bulk_action';
        input.value = action;
        form.appendChild(input);
        form.submit();
    }
}

function exportUsers() {
    alert('Export functionality will be implemented. This will export user data as CSV/Excel.');
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
</script>

<?php include '../includes/footer.php'; ?>