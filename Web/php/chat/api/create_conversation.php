<?php
/**
 * HTTP API endpoint for creating conversations
 * Separate from WebSocket server
 */

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Check if required files exist
    if (!file_exists('../config/database.php')) {
        throw new Exception('Database config file not found');
    }
    if (!file_exists('../classes/ChatManager.php')) {
        throw new Exception('ChatManager class file not found');
    }
    if (!file_exists('../classes/AuthManager.php')) {
        throw new Exception('AuthManager class file not found');
    }

    require_once '../config/database.php';
    require_once '../classes/ChatManager.php';
    require_once '../classes/AuthManager.php';

    // Start session for authentication
    session_start();

    // Check if user is authenticated (adjust based on your auth method)
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized - Please login first',
            'timestamp' => time()
        ]);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    
    // Get database connection
    $db = getDBConnection();
    if (!$db) {
        throw new Exception('Failed to connect to database');
    }
    
    // Get user type from users_info table
    $stmt = $db->prepare("SELECT acc_type FROM users_info WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare user query: ' . $db->errorInfo()[2]);
    }
    
    $stmt->execute([$user_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userInfo) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'User profile not found',
            'debug' => 'User ID: ' . $user_id,
            'timestamp' => time()
        ]);
        exit;
    }
    
    $user_type = $userInfo['acc_type'];

    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed',
            'timestamp' => time()
        ]);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Debug: Log the received input
    error_log('Received input: ' . print_r($input, true));
    
    // Only require service_id now
    if (!$input || !isset($input['service_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing service_id',
            'debug' => 'Received: ' . json_encode($input),
            'timestamp' => time()
        ]);
        exit;
    }

    $service_id = (int) $input['service_id'];
    
    if ($service_id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid service_id',
            'debug' => 'Service ID: ' . $service_id,
            'timestamp' => time()
        ]);
        exit;
    }
    
    // Initialize managers
    $chatManager = new ChatManager();
    $authManager = new AuthManager();
    
    // Only passengers can initiate conversations
    if ($user_type !== 'passenger') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Only passengers can initiate conversations',
            'debug' => 'User type: ' . $user_type,
            'timestamp' => time()
        ]);
        exit;
    }
    
    // Find the company user ID from the service_id
    $stmt = $db->prepare("
        SELECT cs.user_id, ui.acc_type 
        FROM company_services cs
        JOIN users_info ui ON cs.user_id = ui.user_id 
        WHERE cs.id = ?
    ");
    
    if (!$stmt) {
        throw new Exception('Failed to prepare service query: ' . $db->errorInfo()[2]);
    }
    
    $stmt->execute([$service_id]);
    $serviceInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$serviceInfo) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Service not found',
            'debug' => 'Service ID: ' . $service_id,
            'timestamp' => time()
        ]);
        exit;
    }
    
    // Verify it's actually a company account
    if ($serviceInfo['acc_type'] !== 'company') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Service owner is not a company account',
            'debug' => 'Account type: ' . $serviceInfo['acc_type'],
            'timestamp' => time()
        ]);
        exit;
    }
    
    $company_user_id = $serviceInfo['user_id'];
    $passenger_user_id = $user_id;
    
    // Prevent self-conversations
    if ($passenger_user_id === $company_user_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Cannot create conversation with yourself',
            'timestamp' => time()
        ]);
        exit;
    }
    
    // Check for existing conversation
    $existing_conversation = $chatManager->findExistingConversation(
        $service_id, 
        $passenger_user_id, 
        $company_user_id
    );
    
    if ($existing_conversation) {
        // Return existing conversation
        echo json_encode([
            'success' => true,
            'conversation_id' => $existing_conversation['id'],
            'existing' => true,
            'message' => 'Existing conversation found',
            'timestamp' => time()
        ]);
        exit;
    }
    
    // Create new conversation
    $conversation_id = $chatManager->createConversation(
        $service_id,
        $passenger_user_id,
        $company_user_id
    );
    
    if ($conversation_id) {
        echo json_encode([
            'success' => true,
            'conversation_id' => $conversation_id,
            'existing' => false,
            'message' => 'Conversation created successfully',
            'timestamp' => time()
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to create conversation',
            'timestamp' => time()
        ]);
    }

} catch (Exception $e) {
    error_log("Error in create_conversation.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'debug' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => time()
    ]);
}
?>