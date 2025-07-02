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
$email_for_otp = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        // Registration form
        $first_name = cleanInput($_POST['first_name']);
        $last_name = cleanInput($_POST['last_name']);
        $email = cleanInput($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
            $error_message = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error_message = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirm_password) {
            $error_message = 'Passwords do not match.';
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error_message = 'An account with this email already exists.';
            } else {
                // Generate OTP and create user account
                $otp = generateOTP();
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Handle profile picture upload
                $profile_picture = 'default.jpg';
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = PROFILE_PICS_PATH;
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array(strtolower($file_extension), $allowed_extensions)) {
                        $profile_picture = 'profile_' . time() . '.' . $file_extension;
                        $upload_path = $upload_dir . $profile_picture;
                        
                        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                            $profile_picture = 'default.jpg';
                        }
                    }
                }
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, profile_picture, otp, otp_expires, role) VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 'user')");
                    $stmt->execute([$first_name, $last_name, $email, $hashed_password, $profile_picture, $otp]);
                    
                    // Send OTP email
                    if (sendOTP($email, $otp)) {
                        $show_otp_form = true;
                        $email_for_otp = $email;
                        $success_message = 'Registration successful! Please check your email for the OTP verification code.';
                    } else {
                        $error_message = 'Registration successful, but failed to send OTP email. Please contact support.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Registration failed. Please try again.';
                }
            }
        }
    } elseif (isset($_POST['verify_otp'])) {
        // OTP verification form
        $email = cleanInput($_POST['email']);
        $otp = cleanInput($_POST['otp']);
        
        if (empty($otp)) {
            $error_message = 'Please enter the OTP.';
            $show_otp_form = true;
            $email_for_otp = $email;
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND otp = ? AND otp_expires > NOW() AND email_verified = 0");
            $stmt->execute([$email, $otp]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Verify email
                $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, otp = NULL, otp_expires = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                $_SESSION['registration_success'] = 'Email verified successfully! You can now login.';
                header('Location: login.php');
                exit();
            } else {
                $error_message = 'Invalid or expired OTP. Please try again.';
                $show_otp_form = true;
                $email_for_otp = $email;
            }
        }
    } elseif (isset($_POST['resend_otp'])) {
        // Resend OTP
        $email = cleanInput($_POST['email']);
        $new_otp = generateOTP();
        
        $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expires = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE email = ? AND email_verified = 0");
        $stmt->execute([$new_otp, $email]);
        
        if (sendOTP($email, $new_otp)) {
            $success_message = 'New OTP sent to your email.';
        } else {
            $error_message = 'Failed to send OTP. Please try again.';
        }
        $show_otp_form = true;
        $email_for_otp = $email;
    }
}

$page_title = "Register";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        <?php echo $show_otp_form ? 'Verify Email' : 'Create Account'; ?>
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
                    
                    <?php if ($show_otp_form): ?>
                        <!-- OTP Verification Form -->
                        <div class="text-center mb-4">
                            <i class="fas fa-envelope-open-text fa-3x text-primary mb-3"></i>
                            <p class="text-muted">
                                We've sent a 6-digit verification code to<br>
                                <strong><?php echo htmlspecialchars($email_for_otp); ?></strong>
                            </p>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_for_otp); ?>">
                            
                            <div class="mb-4">
                                <label for="otp" class="form-label fw-semibold text-center d-block">Enter Verification Code</label>
                                <div class="d-flex justify-content-center">
                                    <input type="text" class="form-control text-center fw-bold" 
                                           id="otp" name="otp" maxlength="6" 
                                           style="max-width: 200px; font-size: 1.5rem; letter-spacing: 0.5rem;"
                                           placeholder="000000" required>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-3">
                                <button type="submit" name="verify_otp" class="btn btn-primary py-3 fw-semibold">
                                    <i class="fas fa-check me-2"></i>
                                    Verify Email
                                </button>
                                
                                <button type="submit" name="resend_otp" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-2"></i>
                                    Resend Code
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="register.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Registration
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <!-- Registration Form -->
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label fw-semibold">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" 
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label fw-semibold">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           required>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    We'll send an OTP to verify your email address
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
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
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
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
                            
                            <div class="mb-4">
                                <label for="profile_picture" class="form-label fw-semibold">Profile Picture (Optional)</label>
                                <input type="file" class="form-control" id="profile_picture" name="profile_picture" 
                                       accept="image/*">
                                <div class="form-text">
                                    Accepted formats: JPG, PNG, GIF (Max 5MB)
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agree_terms" required>
                                    <label class="form-check-label" for="agree_terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> 
                                        and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" name="register" class="btn btn-primary w-100 py-3 fw-semibold">
                                <i class="fas fa-user-plus me-2"></i>
                                Create Account
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-muted mb-3">Already have an account?</p>
                            <a href="login.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login
                            </a>
                        </div>
                    <?php endif; ?>
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

#otp {
    border: 2px solid var(--bs-primary);
}

#otp:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
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
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    if (password && confirmPassword) {
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    }
    
    // Auto-focus OTP input and format
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});

// File upload preview
document.getElementById('profile_picture')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Check file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            this.value = '';
            return;
        }
        
        // Show preview (optional)
        const reader = new FileReader();
        reader.onload = function(e) {
            // Could add preview image here
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'includes/footer.php'; ?>