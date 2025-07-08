<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../config/ai_config.php';
 
// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['query']) || empty(trim($input['query']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Query is required']);
    exit();
}

$userQuery = trim($input['query']);
$userId = $_SESSION['user_id'];

try {
    // Create AI chat logs table if it doesn't exist
    createAIChatTable($pdo);
    
    // Process the query with AI
    $result = processLegalAIQuery($userQuery, $pdo);
    
    // Log the interaction
    logAIInteraction($userId, $userQuery, $result, $pdo);
    
    // Return the result
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("AI Chat Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'Failed to process AI request'
    ]);
}

/**
 * Create AI chat logs table if it doesn't exist
 */
function createAIChatTable($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS ai_chat_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        query TEXT NOT NULL,
        response JSON,
        sections_found INT DEFAULT 0,
        response_time_ms INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at)
    )";
    
    $pdo->exec($sql);
}
?>