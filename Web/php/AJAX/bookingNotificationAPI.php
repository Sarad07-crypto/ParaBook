<?php
// Example usage file: getNotifications.php
session_start();
header('Content-Type: application/json');

require_once 'bookingNotification.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    include "../connection.php";

    $userId = (int)$_SESSION['user_id'];
    $action = $_GET['action'] ?? 'get';
    
    // Initialize notification system
    $notificationSystem = new BookingNotificationSystem($connect);
    
    switch ($action) {
        case 'get':
            // Get user notifications
            $limit = (int)($_GET['limit'] ?? 10);
            $notifications = $notificationSystem->getUserNotifications($userId, $limit);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications
            ]);
            break;
            
        case 'unread_count':
            // Get unread notification count
            $count = $notificationSystem->getUnreadCount($userId);
            
            echo json_encode([
                'success' => true,
                'unread_count' => $count
            ]);
            break;
            
        case 'mark_read':
            // Mark specific notification as read
            $notificationId = (int)($_POST['notification_id'] ?? 0);
            
            if ($notificationId > 0) {
                $result = $notificationSystem->markAsRead($notificationId, $userId);
                
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Notification marked as read' : 'Failed to mark notification as read'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
            }
            break;
            
        case 'mark_all_read':
            // Mark all notifications as read
            $result = $notificationSystem->markAllAsRead($userId);
            
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'All notifications marked as read' : 'Failed to mark notifications as read'
            ]);
            break;
            
        case 'recent':
            // Get recent notifications for dashboard
            $limit = (int)($_GET['limit'] ?? 5);
            $notifications = $notificationSystem->getRecentNotifications($userId, $limit);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications
            ]);
            break;
            
        case 'update_booking_status':
            // Update booking status (for company users)
            $bookingId = (int)($_POST['booking_id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            if ($bookingId > 0 && !empty($status)) {
                $result = $notificationSystem->updateBookingStatus($bookingId, $status, $message);
                
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Booking status updated successfully' : 'Failed to update booking status'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid booking ID or status']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }

} catch (Exception $e) {
    error_log("Notification operation error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'debug_error' => $e->getMessage() // Remove in production
    ]);
} finally {
    if (isset($connect) && $connect) {
        $connect->close();
    }
}

// Example AJAX calls you can use in your frontend:

/*
// Get notifications
$.get('getNotifications.php?action=get&limit=10', function(response) {
    if (response.success) {
        console.log('Notifications:', response.notifications);
    }
});

// Get unread count
$.get('getNotifications.php?action=unread_count', function(response) {
    if (response.success) {
        console.log('Unread count:', response.unread_count);
    }
});

// Mark notification as read
$.post('getNotifications.php?action=mark_read', {
    notification_id: 123
}, function(response) {
    console.log(response.message);
});

// Mark all as read
$.post('getNotifications.php?action=mark_all_read', function(response) {
    console.log(response.message);
});

// Update booking status (for company users)
$.post('getNotifications.php?action=update_booking_status', {
    booking_id: 456,
    status: 'approved',
    message: 'Your booking has been approved and scheduled.'
}, function(response) {
    console.log(response.message);
});
*/
?>