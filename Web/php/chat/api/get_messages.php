<?php
/**
 * API endpoint to fetch messages for a specific conversation
 * Updated to fix database connection and parameter handling issues
 */

// Prevent any output before JSON headers
ob_start();

// Error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser
ini_set('log_errors', 1);     // Log errors to file instead

// Set JSON headers first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

function sendResponse($success, $data = null, $error = null, $httpCode = 200) {
    // Clear any output buffer to prevent HTML from being sent
    if (ob_get_length()) ob_clean();
    
    http_response_code($httpCode);
    $response = [
        'success' => $success,
        'timestamp' => time()
    ];
    
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    
    if ($error !== null) {
        $response['error'] = $error;
    }
    
    echo json_encode($response);
    exit;
}

function debugLog($message) {
    error_log("DEBUG [get_messages.php]: " . $message);
}

try {
    // Start session first
    session_start();

    // Debug: Log all incoming data
    debugLog("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    debugLog("GET parameters: " . json_encode($_GET));
    debugLog("POST parameters: " . json_encode($_POST));
    debugLog("Full URL: " . $_SERVER['REQUEST_URI']);
    debugLog("Session data: " . json_encode($_SESSION));

    // Check authentication first
    if (!isset($_SESSION['user_id'])) {
        debugLog("Session check failed - no user_id in session");
        sendResponse(false, null, 'Unauthorized - Please login first', 401);
    }

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, null, 'Method not allowed', 405);
    }

    $user_id = $_SESSION['user_id'];
    debugLog("User ID from session: " . $user_id);
    
    // Check for conversation_id parameter
    if (!isset($_GET['conversation_id']) || empty($_GET['conversation_id'])) {
        debugLog("Missing or empty conversation_id in GET parameters");
        sendResponse(false, null, 'Missing conversation_id parameter', 400);
    }

    $conversation_id = (int)$_GET['conversation_id'];
    if ($conversation_id <= 0) {
        debugLog("Invalid conversation_id: " . $_GET['conversation_id']);
        sendResponse(false, null, 'Invalid conversation_id parameter', 400);
    }

    // Database connection - try multiple approaches
    $db = null;
    
    // Method 1: Try to include config and use existing connection
    $config_path = '../config/database.php';
    if (file_exists($config_path)) {
        try {
            require_once $config_path;
            if (isset($db) && $db !== null) {
                debugLog("Using existing database connection from config");
            } else {
                debugLog("Config loaded but no database connection found, trying getDBConnection()");
                if (function_exists('getDBConnection')) {
                    $db = getDBConnection();
                    debugLog("Got database connection from getDBConnection()");
                }
            }
        } catch (Exception $e) {
            debugLog("Error loading config: " . $e->getMessage());
        }
    }
    
    // Method 2: Create direct connection if config method failed
    if ($db === null) {
        debugLog("Creating direct database connection");
        try {
            $dsn = "mysql:host=localhost:3307;dbname=parabook;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $db = new PDO($dsn, 'root', '', $options);
            debugLog("Direct database connection successful");
        } catch (PDOException $e) {
            debugLog("Direct database connection failed: " . $e->getMessage());
        }
    }
    
    // Final check for database connection
    if ($db === null) {
        debugLog("All database connection methods failed");
        sendResponse(false, null, 'Database connection error', 500);
    }

    // Test database connection
    try {
        $testStmt = $db->query("SELECT 1");
        debugLog("Database connection test successful");
    } catch (Exception $e) {
        debugLog("Database connection test failed: " . $e->getMessage());
        sendResponse(false, null, 'Database connection test failed', 500);
    }

    $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

    debugLog("Conversation ID: " . $conversation_id);
    debugLog("Limit: " . $limit . ", Offset: " . $offset);

    // Check if user has access to this conversation
    debugLog("Checking user access to conversation");
    $accessStmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM chat_conversations 
        WHERE id = ? AND (passenger_user_id = ? OR company_user_id = ?)
    ");
    $accessStmt->execute([$conversation_id, $user_id, $user_id]);
    $accessResult = $accessStmt->fetch();
    
    debugLog("Access check result: " . json_encode($accessResult));
    
    if ($accessResult['count'] == 0) {
        debugLog("Access denied - user not participant in conversation");
        sendResponse(false, null, 'Access denied to conversation', 403);
    }

    // Get messages for this conversation - FIXED QUERY with proper JOIN to users_info
    debugLog("Fetching messages for conversation");
    $sql = "
        SELECT 
            m.id,
            m.message,
            m.message_type,
            m.sender_user_id as sender_id,
            m.created_at,
            m.is_read,
            CONCAT(ui.firstName, ' ', ui.lastName) as sender_name,
            'user' as sender_type
        FROM chat_messages m
        LEFT JOIN users u ON m.sender_user_id = u.id
        LEFT JOIN users_info ui ON u.id = ui.user_id
        WHERE m.conversation_id = ?
        ORDER BY m.created_at ASC
        LIMIT ? OFFSET ?
    ";

    debugLog("Executing message query");
    $stmt = $db->prepare($sql);
    $stmt->execute([$conversation_id, $limit, $offset]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    debugLog("Found " . count($messages) . " messages");

    // Get conversation info - FIXED QUERY: using service_title instead of service_name, address instead of location
    debugLog("Fetching conversation info");
    $conversationStmt = $db->prepare("
        SELECT 
            c.id,
            c.service_id,
            cs.service_title as service_name,
            cs.address as service_location,
            c.passenger_user_id,
            c.company_user_id,
            CONCAT(cui.firstName, ' ', cui.lastName) as company_name,
            CONCAT(pui.firstName, ' ', pui.lastName) as passenger_name
        FROM chat_conversations c
        LEFT JOIN company_services cs ON c.service_id = cs.id
        LEFT JOIN users cu ON c.company_user_id = cu.id
        LEFT JOIN users_info cui ON cu.id = cui.user_id
        LEFT JOIN users pu ON c.passenger_user_id = pu.id
        LEFT JOIN users_info pui ON pu.id = pui.user_id
        WHERE c.id = ?
    ");

    $conversationStmt->execute([$conversation_id]);
    $conversation = $conversationStmt->fetch(PDO::FETCH_ASSOC);

    if (!$conversation) {
        debugLog("Conversation not found in database");
        sendResponse(false, null, 'Conversation not found', 404);
    }

    debugLog("Conversation found: " . json_encode($conversation));

    // Process messages
    $processedMessages = array_map(function($message) {
        return [
            'id' => (int)$message['id'],
            'message' => $message['message'],
            'message_type' => $message['message_type'] ?: 'text',
            'sender_id' => (int)$message['sender_id'],
            'sender_name' => $message['sender_name'] ?: 'Unknown User',
            'sender_type' => $message['sender_type'],
            'created_at' => $message['created_at'],
            'is_read' => (bool)$message['is_read']
        ];
    }, $messages);

    debugLog("Sending successful response with " . count($processedMessages) . " messages");

    // Clear output buffer and send successful response
    sendResponse(true, [
        'messages' => $processedMessages,
        'conversation' => [
            'id' => (int)$conversation['id'],
            'service_id' => (int)$conversation['service_id'],
            'service_name' => $conversation['service_name'] ?: 'Unknown Service',
            'service_location' => $conversation['service_location'] ?: 'Unknown Location',
            'company_name' => $conversation['company_name'] ?: 'Unknown Company',
            'passenger_name' => $conversation['passenger_name'] ?: 'Unknown Passenger'
        ],
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'count' => count($processedMessages)
        ]
    ]);

} catch (PDOException $e) {
    debugLog("Database Error: " . $e->getMessage());
    debugLog("Database Error Details: " . json_encode([
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]));
    sendResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    debugLog("General Error: " . $e->getMessage());
    debugLog("Error Details: " . json_encode([
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]));
    sendResponse(false, null, $e->getMessage(), 500);
}
?>