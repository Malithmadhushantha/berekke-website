<?php
require_once '../config/config.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Remove user session from database
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_token = ?");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
    
    // Clear session data
    session_unset();
    session_destroy();
    
    // Start new session for logout message
    session_start();
    $_SESSION['logout_success'] = 'You have been successfully logged out.';
}

// Redirect to login page
header('Location: ../login.php');
exit();
?>