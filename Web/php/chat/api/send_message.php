<?php
/**
 * API endpoint to send a new message in a conversation
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

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

function debugLog($message) {
    error_log("DEBUG [send_message.php]: " . $message);
}

try {
    // Database connection
    $db = null;
    $config_path = '../config/database.php';
    if (file_exists($config_path)) {
        require_once $config_path;
        if (function_exists('getDBConnection')) {
            $db = getDBConnection();
        }
    }
    
    if ($db === null) {
        $dsn = "mysql:host=localhost:3307;dbname=parabook;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $db = new PDO($dsn, 'root', '', $options);
    }

    session_start();

    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, null, 'Unauthorized - Please login first', 401);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, null, 'Method not allowed', 405);
    }

    $user_id = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);

    debugLog("User ID: " . $user_id);
    debugLog("Input data: " . json_encode($input));

    // Validate required fields
    if (!isset($input['conversation_id']) || !isset($input['message'])) {
        sendResponse(false, null, 'Missing required fields: conversation_id, message', 400);
    }

    $conversation_id = (int)$input['conversation_id'];
    $message = trim($input['message']);
    $message_type = isset($input['message_type']) ? $input['message_type'] : 'text';

    // Validate message content
    if (empty($message)) {
        sendResponse(false, null, 'Message cannot be empty', 400);
    }

    if (strlen($message) > 5000) {
        sendResponse(false, null, 'Message too long (max 5000 characters)', 400);
    }

    // Validate message type
    $allowed_types = ['text', 'image', 'file'];
    if (!in_array($message_type, $allowed_types)) {
        sendResponse(false, null, 'Invalid message type', 400);
    }

    // Get user info
    $stmt = $db->prepare("SELECT acc_type FROM users_info WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userInfo) {
        sendResponse(false, null, 'User profile not found', 404);
    }

    $user_type = $userInfo['acc_type'];
    debugLog("User type: " . $user_type);

    // Verify user has access to this conversation and get conversation details
    $conversationStmt = $db->prepare("
        SELECT 
            id,
            passenger_user_id, 
            company_user_id, 
            status 
        FROM chat_conversations 
        WHERE id = ? AND (passenger_user_id = ? OR company_user_id = ?)
    ");
    $conversationStmt->execute([$conversation_id, $user_id, $user_id]);
    $conversation = $conversationStmt->fetch(PDO::FETCH_ASSOC);

    if (!$conversation) {
        sendResponse(false, null, 'Conversation not found or access denied', 404);
    }

    debugLog("Conversation found: " . json_encode($conversation));

    // Check if conversation is active
    if ($conversation['status'] !== 'active') {
        sendResponse(false, null, 'Cannot send message to inactive conversation', 400);
    }

    // Begin transaction
    $db->beginTransaction();

    try {
        // Insert the message (simplified - no recipient_id field in your schema)
        $insertStmt = $db->prepare("
            INSERT INTO chat_messages (
                conversation_id, 
                sender_user_id, 
                message, 
                message_type,
                created_at
            ) VALUES (?, ?, ?, ?, NOW())
        ");

        $insertResult = $insertStmt->execute([
            $conversation_id,
            $user_id,
            $message,
            $message_type
        ]);

        if (!$insertResult) {
            throw new Exception('Failed to insert message');
        }

        $message_id = $db->lastInsertId();
        debugLog("Message inserted with ID: " . $message_id);

        // Update conversation's last message time
        $updateConversationStmt = $db->prepare("
            UPDATE chat_conversations 
            SET 
                last_message_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");

        $updateResult = $updateConversationStmt->execute([$conversation_id]);

        if (!$updateResult) {
            throw new Exception('Failed to update conversation');
        }

        // Update unread counts based on who sent the message
        if ($user_id == $conversation['company_user_id']) {
            // Company sent message, increment passenger unread count
            $readStmt = $db->prepare("
                UPDATE chat_conversations 
                SET passenger_unread_count = passenger_unread_count + 1
                WHERE id = ?
            ");
        } else {
            // Passenger sent message, increment company unread count
            $readStmt = $db->prepare("
                UPDATE chat_conversations 
                SET company_unread_count = company_unread_count + 1
                WHERE id = ?
            ");
        }

        $readStmt->execute([$conversation_id]);

        // Commit transaction
        $db->commit();
        debugLog("Transaction committed successfully");

        // Get the created message with sender info for response
        $messageStmt = $db->prepare("
            SELECT 
                m.id,
                m.message,
                m.message_type,
                m.sender_user_id as sender_id,
                m.created_at,
                m.is_read,
                CONCAT(ui.firstName, ' ', ui.lastName) as sender_name,
                ui.acc_type as sender_type
            FROM chat_messages m
            LEFT JOIN users u ON m.sender_user_id = u.id
            LEFT JOIN users_info ui ON u.id = ui.user_id
            WHERE m.id = ?
        ");

        $messageStmt->execute([$message_id]);
        $createdMessage = $messageStmt->fetch(PDO::FETCH_ASSOC);

        if (!$createdMessage) {
            throw new Exception('Could not retrieve created message');
        }

        debugLog("Created message retrieved: " . json_encode($createdMessage));

        sendResponse(true, [
            'message' => [
                'id' => (int)$createdMessage['id'],
                'message' => $createdMessage['message'],
                'message_type' => $createdMessage['message_type'],
                'sender_id' => (int)$createdMessage['sender_id'],
                'sender_name' => $createdMessage['sender_name'] ?: 'Unknown User',
                'sender_type' => $createdMessage['sender_type'],
                'created_at' => $createdMessage['created_at'],
                'is_read' => (bool)$createdMessage['is_read']
            ],
            'conversation_id' => $conversation_id
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        debugLog("Transaction rolled back due to error: " . $e->getMessage());
        throw $e;
    }

} catch (PDOException $e) {
    debugLog("Database Error: " . $e->getMessage());
    sendResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    debugLog("Error in send_message.php: " . $e->getMessage());
    sendResponse(false, null, 'Internal server error: ' . $e->getMessage(), 500);
}
?>