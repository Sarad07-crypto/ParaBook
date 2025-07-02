<?php
/**
 * API endpoint to mark specific conversation as read
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

try {
    require_once '../config/database.php';
    require_once '../classes/ChatManager.php';

    session_start();

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized - Please login first'
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ]);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['conversation_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing conversation_id'
        ]);
        exit;
    }

    $conversation_id = (int)$input['conversation_id'];

    $db = getDBConnection();
    if (!$db) {
        throw new Exception('Failed to connect to database');
    }

    // Get user type
    $stmt = $db->prepare("SELECT acc_type FROM users_info WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userInfo) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'User profile not found'
        ]);
        exit;
    }

    $user_type = $userInfo['acc_type'];

    // Verify user has access to this conversation
    $chatManager = new ChatManager();
    if (!$chatManager->hasConversationAccess($user_id, $conversation_id)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Access denied to conversation'
        ]);
        exit;
    }

    // Mark messages as read
    $success = $chatManager->markMessagesAsRead($conversation_id, $user_id);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Messages marked as read',
            'conversation_id' => $conversation_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to mark messages as read'
        ]);
    }

} catch (Exception $e) {
    error_log("Error in mark_read.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'debug' => $e->getMessage()
    ]);
}
?>