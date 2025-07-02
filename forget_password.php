<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error_message = '';
$success_message = '';
$show_otp_form = false;
$show_reset_form = false;
$email_for_reset = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_otp'])) {
        // Send OTP for password reset
        $email = cleanInput($_POST['email']);
        
        if (empty($email)) {
            $error_message = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address.';
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND email_verified = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate and send OTP
                $otp = generateOTP();
                $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expires = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE email = ?");
                $stmt->execute([$otp, $email]);
                
                if (sendOTP($email, $otp)) {
                    $show_otp_form = true;
                    $email_for_reset = $email;
                    $success_message = 'OTP sent to your email. Please check your inbox.';
                } else {
                    $error_message = 'Failed to send OTP. Please try again later.';
                }
            } else {
                $error_message = 'No account found with this email address.';
            }
        }
    } elseif (isset($_POST['verify_otp'])) {
        // Verify OTP
        $email = cleanInput($_POST['email']);
        $otp = cleanInput($_POST['otp']);
        
        if (empty($otp)) {
            $error_message = 'Please enter the OTP.';
            $show_otp_form = true;
            $email_for_reset = $email;
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND otp = ? AND otp_expires > NOW()");
            $stmt->execute([$email, $otp]);
            $user = $stmt->fetch();
            
            if ($user) {
                $show_reset_form = true;
                $email_for_reset = $email;
                $success_message = 'OTP verified! Please enter your new password.';
            } else {
                $error_message = 'Invalid or expired OTP.';
                $show_otp_form = true;
                $email_for_reset = $email;
            }
        }
    } elseif (isset($_POST['reset_password'])) {
        // Reset password
        $email = cleanInput($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($password) || empty($confirm_password)) {
            $error_message = 'Please fill in all fields.';
            $show_reset_form = true;
            $email_for_reset = $email;
        } elseif (strlen($password) < 6) {
            $error_message = 'Password must be at least 6 characters long.';
            $show_reset_form = true;
            $email_for_reset = $email;
        } elseif ($password !== $confirm_password) {
            $error_message = 'Passwords do not match.';
            $show_reset_form = true;
            $email_for_reset = $email;
        } else {
            // Update password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, otp = NULL, otp_expires = NULL WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);
            
            // Clear all active sessions for this user
            $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = (SELECT id FROM users WHERE email = ?)");
            $stmt->execute([$email]);
            
            $_SESSION['password_reset_success'] = 'Password reset successfully! Please login with your new password.';
            header('Location: login.php');
            exit();
        }
    } elseif (isset($_POST['resend_otp'])) {
        // Resend OTP
        $email = cleanInput($_POST['email']);
        $new_otp = generateOTP();
        
        $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expires = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE email = ?");
        $stmt->execute([$new_otp, $email]);
        
        if (sendOTP($email, $new_otp)) {
            $success_message = 'New OTP sent to your email.';
        } else {
            $error_message = 'Failed to send OTP. Please try again.';
        }
        $show_otp_form = true;
        $email_for_reset = $email;
    }
}

$page_title = "Forget Password";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-warning text-dark text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Reset Password
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
                    
                    <?php if ($show_reset_form): ?>
                        <!-- Password Reset Form -->
                        <div class="text-center mb-4">
                            <i class="fas fa-lock fa-3x text-warning mb-3"></i>
                            <h5>Create New Password</h5>
                            <p class="text-muted">Enter your new password below</p>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_for_reset); ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="6" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" name="reset_password" class="btn btn-warning w-100 py-3 fw-semibold">
                                <i class="fas fa-check me-2"></i>
                                Reset Password
                            </button>
                        </form>
                        
                    <?php elseif ($show_otp_form): ?>
                        <!-- OTP Verification Form -->
                        <div class="text-center mb-4">
                            <i class="fas fa-envelope-open-text fa-3x text-warning mb-3"></i>
                            <h5>Verify OTP</h5>
                            <p class="text-muted">
                                We've sent a verification code to<br>
                                <strong><?php echo htmlspecialchars($email_for_reset); ?></strong>
                            </p>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_for_reset); ?>">
                            
                            <div class="mb-4">
                                <label for="otp" class="form-label fw-semibold text-center d-block">Enter 6-Digit Code</label>
                                <div class="d-flex justify-content-center">
                                    <input type="text" class="form-control text-center fw-bold" 
                                           id="otp" name="otp" maxlength="6" 
                                           style="max-width: 200px; font-size: 1.5rem; letter-spacing: 0.5rem;"
                                           placeholder="000000" required>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-3">
                                <button type="submit" name="verify_otp" class="btn btn-warning py-3 fw-semibold">
                                    <i class="fas fa-check me-2"></i>
                                    Verify OTP
                                </button>
                                
                                <button type="submit" name="resend_otp" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-2"></i>
                                    Resend Code
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="forget_password.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Email Entry
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <!-- Email Entry Form -->
                        <div class="text-center mb-4">
                            <i class="fas fa-envelope fa-3x text-warning mb-3"></i>
                            <h5>Forgot Your Password?</h5>
                            <p class="text-muted">
                                Enter your email address and we'll send you a verification code to reset your password.
                            </p>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           placeholder="Enter your registered email" required>
                                </div>
                            </div>
                            
                            <button type="submit" name="send_otp" class="btn btn-warning w-100 py-3 fw-semibold">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send Verification Code
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="text-muted mb-3">Remember your password?</p>
                        <a href="login.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Back to Login
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

.input-group-text {
    background-color: var(--bs-light);
    border-right: none;
}

.form-control {
    border-left: none;
}

.form-control:focus {
    border-color: var(--bs-warning);
    box-shadow: none;
}

.input-group-text + .form-control:focus {
    border-left: none;
}

.btn-warning {
    background: linear-gradient(45deg, var(--bs-warning), #ffcd39);
    border: none;
    color: #000;
    transition: all 0.3s ease;
}

.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    color: #000;
}

#otp {
    border: 2px solid var(--bs-warning);
}

#otp:focus {
    border-color: var(--bs-warning);
    box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
}

@media (max-width: 768px) {
    .card-body {
        padding: 2rem 1.5rem !important;
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

// Password confirmation validation
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password && confirmPassword) {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }
    
    if (password && confirmPassword) {
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    }
    
    // Auto-format OTP input
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>