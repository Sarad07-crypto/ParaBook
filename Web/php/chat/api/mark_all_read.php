<?php
/**
 * API endpoint to mark all conversations as read for current user
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

    // Get all conversations for this user
    $chatManager = new ChatManager();
    $conversations = $chatManager->getUserConversations($user_id);

    $marked_count = 0;
    $errors = [];

    // Mark each conversation as read
    foreach ($conversations as $conversation) {
        try {
            $success = $chatManager->markMessagesAsRead($conversation['id'], $user_id);
            if ($success) {
                $marked_count++;
            } else {
                $errors[] = "Failed to mark conversation {$conversation['id']} as read";
            }
        } catch (Exception $e) {
            $errors[] = "Error marking conversation {$conversation['id']}: " . $e->getMessage();
        }
    }

    // Response
    $response = [
        'success' => true,
        'message' => "Marked {$marked_count} conversations as read",
        'marked_count' => $marked_count,
        'total_conversations' => count($conversations)
    ];

    if (!empty($errors)) {
        $response['warnings'] = $errors;
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in mark_all_read.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'debug' => $e->getMessage()
    ]);
}
?>