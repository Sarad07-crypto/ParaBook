<?php
// Web/php/AJAX/notificationAPI.php
session_start();
require '../connection.php';

// Set JSON header
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

// Log the action for debugging
error_log("NotificationAPI - Action: $action, User ID: $user_id");

try {
    switch ($action) {
        case 'get_notification_details':
            getNotificationDetails($connect, $user_id);
            break;
        case 'mark_as_read':
            markAsRead($connect, $user_id);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log("NotificationAPI Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getNotificationDetails($connect, $user_id) {
    $notification_id = $_GET['notification_id'] ?? '';
    
    if (empty($notification_id)) {
        echo json_encode(['success' => false, 'message' => 'Notification ID required']);
        return;
    }
    
    // Log for debugging
    error_log("Getting notification details for ID: $notification_id, User: $user_id");
    
    try {
        // Get notification details with service_id from booking
        // Fixed: Changed s.title to s.service_title to match your schema
        $query = "
            SELECT n.*, 
                   b.service_id,
                   b.booking_no,
                   b.user_id as booking_user_id,
                   s.service_title as service_title
            FROM notifications n
            LEFT JOIN bookings b ON n.booking_id = b.booking_id
            LEFT JOIN company_services s ON b.service_id = s.id
            WHERE n.id = ? AND n.recipient_id = ?
        ";
        
        $stmt = mysqli_prepare($connect, $query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $notification_id, $user_id);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        $notification = mysqli_fetch_assoc($result);
        
        if (!$notification) {
            error_log("Notification not found for ID: $notification_id, User: $user_id");
            echo json_encode(['success' => false, 'message' => 'Notification not found']);
            mysqli_stmt_close($stmt);
            return;
        }
        
        // Security check - make sure user can only access their own notifications
        if ($notification['recipient_id'] != $user_id) {
            error_log("Access denied for notification ID: $notification_id, User: $user_id");
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            mysqli_stmt_close($stmt);
            return;
        }
        
        // Log successful retrieval
        error_log("Notification details retrieved successfully: " . json_encode($notification));
        
        echo json_encode([
            'success' => true,
            'notification' => $notification
        ]);
        
        mysqli_stmt_close($stmt);
        
    } catch (Exception $e) {
        error_log("Database error in getNotificationDetails: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function markAsRead($connect, $user_id) {
    $notification_id = $_POST['notification_id'] ?? '';
    
    if (empty($notification_id)) {
        echo json_encode(['success' => false, 'message' => 'Notification ID required']);
        return;
    }
    
    // Log for debugging
    error_log("Marking notification as read - ID: $notification_id, User: $user_id");
    
    try {
        $query = "
            UPDATE notifications 
            SET is_read = 1, updated_at = NOW() 
            WHERE id = ? AND recipient_id = ?
        ";
        
        $stmt = mysqli_prepare($connect, $query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $notification_id, $user_id);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result && mysqli_stmt_affected_rows($stmt) > 0) {
            error_log("Notification marked as read successfully");
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            error_log("Failed to mark notification as read - no rows affected");
            echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
        }
        
        mysqli_stmt_close($stmt);
        
    } catch (Exception $e) {
        error_log("Database error in markAsRead: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>