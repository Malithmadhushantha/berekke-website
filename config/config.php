<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'berekke_website');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_NAME', 'Berekke Website');
define('SITE_URL', 'http://localhost/berekke_website');
define('ADMIN_EMAIL', 'admin@berekke.lk');

// Upload directories
define('UPLOAD_PATH', 'uploads/');
define('PROFILE_PICS_PATH', UPLOAD_PATH . 'profiles/');
define('BLOG_IMAGES_PATH', UPLOAD_PATH . 'blogs/');
define('DOWNLOADS_PATH', UPLOAD_PATH . 'downloads/');

// Session configuration
ini_set('session.gc_maxlifetime', 604800); // 7 days
session_set_cookie_params(604800); // 7 days

// Start session
session_start();

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['session_token']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['login_required'] = true;
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
        header('Location: index.php');
        exit();
    }
}

function getUserInfo() {
    global $pdo;
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateOTP() {
    return sprintf("%06d", mt_rand(1, 999999));
}

function sendOTP($email, $otp) {
    // Simple email function - replace with proper email service
    $subject = "Berekke Website OTP Verification";
    $message = "Your OTP for Berekke Website is: " . $otp;
    $headers = "From: " . ADMIN_EMAIL;
    
    return mail($email, $subject, $message, $headers);
}

function validateSession() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM user_sessions WHERE user_id = ? AND session_token = ? AND expires_at > NOW()");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
    $session = $stmt->fetch();
    
    if (!$session) {
        // Invalid session
        session_destroy();
        return false;
    }
    
    // Update last activity
    $stmt = $pdo->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE id = ?");
    $stmt->execute([$session['id']]);
    
    return true;
}

// Validate session on every page load
if (isLoggedIn()) {
    validateSession();
}
?>