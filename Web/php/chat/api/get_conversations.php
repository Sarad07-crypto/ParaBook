<?php
/**
 * HTTP API endpoint for fetching user conversations
 * Returns conversations with unread counts and latest messages
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

// Function to send JSON response and exit
function sendResponse($success, $data = null, $error = null, $httpCode = 200) {
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

try {
    error_log("=== GET CONVERSATIONS DEBUG START ===");
    error_log("Current working directory: " . getcwd());
    error_log("__FILE__: " . __FILE__);
    error_log("__DIR__: " . __DIR__);
    
    // Check if required files exist with absolute paths
    $configPath = __DIR__ . '/../config/database.php';
    $chatManagerPath = __DIR__ . '/../classes/ChatManager.php';
    
    error_log("Looking for config at: " . $configPath);
    error_log("Looking for ChatManager at: " . $chatManagerPath);
    
    if (!file_exists($configPath)) {
        // Try alternative paths
        $altConfigPaths = [
            '../config/database.php',
            './config/database.php',
            '../../config/database.php'
        ];
        
        $configFound = false;
        foreach ($altConfigPaths as $altPath) {
            error_log("Trying alternative config path: " . $altPath);
            if (file_exists($altPath)) {
                $configPath = $altPath;
                $configFound = true;
                break;
            }
        }
        
        if (!$configFound) {
            throw new Exception('Database config file not found. Tried: ' . implode(', ', $altConfigPaths));
        }
    }
    
    if (!file_exists($chatManagerPath)) {
        // Try alternative paths
        $altChatPaths = [
            '../classes/ChatManager.php',
            './classes/ChatManager.php',
            '../../classes/ChatManager.php'
        ];
        
        $chatFound = false;
        foreach ($altChatPaths as $altPath) {
            error_log("Trying alternative ChatManager path: " . $altPath);
            if (file_exists($altPath)) {
                $chatManagerPath = $altPath;
                $chatFound = true;
                break;
            }
        }
        
        if (!$chatFound) {
            throw new Exception('ChatManager class file not found. Tried: ' . implode(', ', $altChatPaths));
        }
    }

    error_log("Using config path: " . $configPath);
    error_log("Using ChatManager path: " . $chatManagerPath);
    
    require_once $configPath;
    require_once $chatManagerPath;

    // Start session for authentication
    session_start();

    error_log("Session data: " . json_encode($_SESSION));
    error_log("GET params: " . json_encode($_GET));

    // Check if user is authenticated
    if (!isset($_SESSION['user_id'])) {
        error_log("User not authenticated - no user_id in session");
        sendResponse(false, null, 'Unauthorized - Please login first', 401);
    }

    $user_id = $_SESSION['user_id'];
    error_log("Authenticated user_id: $user_id");
    
    // Validate user_id
    if (!is_numeric($user_id) || $user_id <= 0) {
        error_log("Invalid user_id: $user_id");
        sendResponse(false, null, 'Invalid user ID', 400);
    }
    
    // Only accept GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, null, 'Method not allowed', 405);
    }
    
    // Get database connection
    if (!function_exists('getDBConnection')) {
        throw new Exception('getDBConnection function not found. Check database.php');
    }
    
    $db = getDBConnection();
    if (!$db) {
        throw new Exception('Failed to connect to database');
    }
    
    error_log("Database connection successful");
    
    // Test database connection
    try {
        $testStmt = $db->query("SELECT 1");
        if (!$testStmt) {
            throw new Exception('Database connection test failed');
        }
        error_log("Database connection test passed");
    } catch (PDOException $e) {
        throw new Exception('Database connection test failed: ' . $e->getMessage());
    }
    
    // Get user type from users_info table
    try {
        $stmt = $db->prepare("SELECT acc_type FROM users_info WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare user query: ' . $db->errorInfo()[2]);
        }
        
        $stmt->execute([$user_id]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("User info query result: " . json_encode($userInfo));
        
        if (!$userInfo) {
            error_log("User profile not found for user_id: $user_id");
            sendResponse(false, null, 'User profile not found', 404);
        }
        
        $user_type = $userInfo['acc_type'];
        error_log("User type: $user_type");
        
    } catch (PDOException $e) {
        throw new Exception('Database error while fetching user info: ' . $e->getMessage());
    }

    // Check if ChatManager class exists
    if (!class_exists('ChatManager')) {
        throw new Exception('ChatManager class not found. Check if the class is properly defined.');
    }

    // Initialize ChatManager
    try {
        $chatManager = new ChatManager();
        error_log("ChatManager initialized");
    } catch (Exception $e) {
        throw new Exception('Failed to initialize ChatManager: ' . $e->getMessage());
    }
    
    // Check if getUserConversations method exists
    if (!method_exists($chatManager, 'getUserConversations')) {
        throw new Exception('getUserConversations method not found in ChatManager class');
    }
    
    // Get conversations for this user
    $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 20; // Limit between 1-100
    error_log("Calling getUserConversations with user_id: $user_id, limit: $limit");
    
    try {
        $conversations = $chatManager->getUserConversations($user_id, $limit);
        if (!is_array($conversations)) {
            throw new Exception('getUserConversations did not return an array');
        }
        error_log("getUserConversations returned " . count($conversations) . " conversations");
    } catch (Exception $e) {
        throw new Exception('Error calling getUserConversations: ' . $e->getMessage());
    }
    
    // Calculate total unread count for this user
    $total_unread = 0;
    $processed_conversations = [];
    
    foreach ($conversations as $conversation) {
        try {
            error_log("Processing conversation: " . json_encode($conversation));
            
            // Validate conversation data
            if (!is_array($conversation)) {
                error_log("Invalid conversation data - not an array");
                continue;
            }
            
            // Determine unread count based on user type
            $unread_count = 0;
            if ($user_type === 'company') {
                $unread_count = isset($conversation['company_unread_count']) ? (int)$conversation['company_unread_count'] : 0;
            } else {
                $unread_count = isset($conversation['passenger_unread_count']) ? (int)$conversation['passenger_unread_count'] : 0;
            }
            
            $total_unread += $unread_count;
            
            // Determine the other participant's name
            $other_participant_name = '';
            if ($user_type === 'company') {
                $other_participant_name = $conversation['passenger_name'] ?? 'Unknown';
            } else {
                $other_participant_name = $conversation['company_name'] ?? 'Unknown';
            }
            
            // Get information about who sent the last message
            $last_message_sender = '';
            if (!empty($conversation['last_message_sender_id'])) {
                try {
                    // Query to get the sender's name
                    $senderStmt = $db->prepare("
                        SELECT ui.first_name, ui.last_name, ui.acc_type, ci.company_name
                        FROM users_info ui
                        LEFT JOIN company_info ci ON ui.user_id = ci.user_id
                        WHERE ui.user_id = ?
                    ");
                    $senderStmt->execute([$conversation['last_message_sender_id']]);
                    $senderInfo = $senderStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($senderInfo) {
                        if ($senderInfo['acc_type'] === 'company' && !empty($senderInfo['company_name'])) {
                            $last_message_sender = $senderInfo['company_name'];
                        } else {
                            $last_message_sender = trim($senderInfo['first_name'] . ' ' . $senderInfo['last_name']);
                        }
                        
                        // Determine if the message is from current user or other participant
                        if ($conversation['last_message_sender_id'] == $user_id) {
                            $last_message_sender = 'You';
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Error fetching sender info: " . $e->getMessage());
                    $last_message_sender = 'Unknown';
                }
            }
            
            // Format the conversation data with safe defaults
            $processed_conversations[] = [
                'id' => isset($conversation['id']) ? (int)$conversation['id'] : 0,
                'service_id' => isset($conversation['service_id']) ? (int)$conversation['service_id'] : 0,
                'service_name' => $conversation['service_name'] ?? '',
                'service_location' => $conversation['service_location'] ?? '',
                'other_participant_name' => $other_participant_name,
                'unread_count' => $unread_count,
                'last_message' => $conversation['last_message'] ?? '',
                'last_message_type' => $conversation['last_message_type'] ?? 'text',
                'last_message_time' => $conversation['last_message_time'] ?? date('Y-m-d H:i:s'),
                'last_message_sender' => $last_message_sender,
                'last_message_sender_id' => $conversation['last_message_sender_id'] ?? null,
                'status' => $conversation['status'] ?? 'active',
                'has_unread' => $unread_count > 0
            ];
            
        } catch (Exception $e) {
            error_log("Error processing conversation: " . $e->getMessage());
            // Continue with next conversation instead of failing entirely
            continue;
        }
    }
    
    // Sort conversations by last message time (most recent first)
    usort($processed_conversations, function($a, $b) {
        $timeA = strtotime($a['last_message_time']);
        $timeB = strtotime($b['last_message_time']);
        return $timeB - $timeA;
    });

    error_log("Final processed conversations count: " . count($processed_conversations));
    error_log("Total unread: $total_unread");
    error_log("=== GET CONVERSATIONS DEBUG END ===");

    sendResponse(true, [
        'conversations' => $processed_conversations,
        'total_unread' => $total_unread,
        'user_type' => $user_type
    ]);

} catch (Exception $e) {
    error_log("Error in get_conversations.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Send detailed error in development, generic in production
    $isDevelopment = (error_get_last() !== null || ini_get('display_errors') == 1);
    
    sendResponse(false, null, $isDevelopment ? $e->getMessage() : 'Internal server error', 500);
}
?>