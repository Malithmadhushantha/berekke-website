<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } else {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND email_verified = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Check device limit (max 2 devices)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_sessions WHERE user_id = ? AND expires_at > NOW()");
            $stmt->execute([$user['id']]);
            $active_sessions = $stmt->fetchColumn();
            
            if ($active_sessions >= 2) {
                $error_message = 'Maximum device limit reached. Please logout from another device first.';
            } else {
                // Create new session
                $session_token = bin2hex(random_bytes(32));
                $device_info = $_SERVER['HTTP_USER_AGENT'];
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $expires_at = date('Y-m-d H:i:s', time() + 604800); // 7 days
                
                $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, device_info, ip_address, expires_at) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user['id'], $session_token, $device_info, $ip_address, $expires_at]);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['session_token'] = $session_token;
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/admin_index.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            }
        } else {
            $error_message = 'Invalid email or password.';
        }
    }
}

// Check for messages
if (isset($_SESSION['login_required'])) {
    $error_message = 'Please login to access this page.';
    unset($_SESSION['login_required']);
}

if (isset($_SESSION['registration_success'])) {
    $success_message = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

if (isset($_SESSION['password_reset_success'])) {
    $success_message = $_SESSION['password_reset_success'];
    unset($_SESSION['password_reset_success']);
}

$page_title = "Login";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Login to Berekke
                    </h3>
                </div>
                <div class="card-body p-5">
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
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Remember me
                                </label>
                            </div>
                            <a href="forget_password.php" class="text-decoration-none">
                                Forgot Password?
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="text-muted mb-3">Don't have an account?</p>
                        <a href="register.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>
                            Create Account
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Demo Credentials Info -->
            <div class="card mt-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Demo Credentials
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Admin Account:</strong></p>
                    <p class="mb-1">Email: admin@berekke.lk</p>
                    <p class="mb-3">Password: admin123</p>
                    
                    <p class="text-muted small">
                        <i class="fas fa-shield-alt me-1"></i>
                        Your session will remain active for 7 days unless you manually logout.
                        You can be logged in from maximum 2 devices simultaneously.
                    </p>
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

.input-group-text {
    background-color: var(--bs-light);
    border-right: none;
}

.form-control {
    border-left: none;
}

.form-control:focus {
    border-color: var(--bs-primary);
    box-shadow: none;
}

.input-group-text + .form-control:focus {
    border-left: none;
}

.btn-primary {
    background: linear-gradient(45deg, var(--bs-primary), #0066cc);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.alert {
    border-radius: 10px;
    border: none;
}

@media (max-width: 768px) {
    .card-body {
        padding: 2rem 1.5rem !important;
    }
}
</style>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>

<?php include 'includes/footer.php'; ?>