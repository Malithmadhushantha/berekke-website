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

// Session Configuration
ini_set('session.gc_maxlifetime', 604800); // 7 days
session_set_cookie_params([
    'lifetime' => 604800, // 7 days
    'path' => '/',
    'domain' => '',  // Use current domain
    'secure' => false, // Set to true on HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ----------- Helper Functions -----------

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['session_token']);
}

/**
 * Redirect user to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['login_required'] = true;
        header('Location: login.php');
        exit();
    }
}

/**
 * Redirect user to homepage if not admin
 */
function requireAdmin() {
    if (!isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
        header('Location: index.php');
        exit();
    }
}

/**
 * Fetch logged-in user's information
 */
function getUserInfo() {
    global $pdo;
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

/**
 * Clean input to prevent XSS and injection
 */
function cleanInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

/**
 * Generate a 6-digit numeric OTP
 */
function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send an OTP email (simple mail implementation)
 */
function sendOTP($email, $otp) {
    $subject = "Berekke Website OTP Verification";
    $message = "Your OTP for Berekke Website is: $otp";
    $headers = "From: " . ADMIN_EMAIL;

    return mail($email, $subject, $message, $headers);
}

/**
 * Validate the current user's session token in DB
 */
function validateSession() {
    global $pdo;

    if (!isLoggedIn()) {
        return false;
    }

    $stmt = $pdo->prepare("SELECT * FROM user_sessions WHERE user_id = ? AND session_token = ? AND expires_at > NOW()");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
    $session = $stmt->fetch();

    if (!$session) {
        // Session invalid — clean up and log out user
        session_unset();
        session_destroy();
        return false;
    }

    // Update last activity timestamp
    $stmt = $pdo->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE id = ?");
    $stmt->execute([$session['id']]);

    return true;
}

// Automatically validate session on every page load
if (isLoggedIn()) {
    validateSession();
}
?>
