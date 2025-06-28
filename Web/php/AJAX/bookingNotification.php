<?php

class BookingNotificationSystem {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /* Send notifications for new booking (called from bookingSubmit.php) */
    public function sendBookingNotifications($bookingId, $userId, $bookingNo, $userName = null) {
        try {
            // Get user info if name not provided
            if (!$userName) {
                $userInfo = $this->getUserInfo($userId);
                $userName = trim($userInfo['firstName'] . ' ' . $userInfo['lastName']);
            }
            
            // Send notification to passenger
            $this->createNotification([
                'recipient_id' => $userId,
                'recipient_type' => 'user',
                'title' => 'Booking Confirmed',
                'message' => "Your booking {$bookingNo} has been confirmed, you can check your booking with this number.",
                'type' => 'booking',
                'icon' => 'fas fa-plane',
                'booking_id' => $bookingId
            ]);
            
            // Send notifications to all company users
            $companyUsers = $this->getCompanyUsers();
            foreach ($companyUsers as $companyUser) {
                $this->createNotification([
                    'recipient_id' => $companyUser['user_id'],
                    'recipient_type' => 'company',
                    'title' => 'New Booking Received',
                    'message' => "{$userName} has made a new booking.",
                    'type' => 'booking',
                    'icon' => 'fas fa-user',
                    'booking_id' => $bookingId
                ]);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /* Send notifications for booking updates */
    public function sendBookingUpdateNotifications($bookingId, $userId, $bookingNo, $userName = null, $updateType = 'updated') {
        try {
            // Get user info if name not provided
            if (!$userName) {
                $userInfo = $this->getUserInfo($userId);
                $userName = trim($userInfo['firstName'] . ' ' . $userInfo['lastName']);
            }
            
            // Determine messages based on update type
            $userTitle = '';
            $userMessage = '';
            $companyTitle = '';
            $companyMessage = '';
            $icon = 'fas fa-edit';
            
            switch ($updateType) {
                case 'updated':
                    $userTitle = 'Booking Updated Successfully';
                    $userMessage = "Your booking {$bookingNo} has been updated successfully.";
                    $companyTitle = 'Booking Updated';
                    $companyMessage = "{$userName} has updated their booking. Please review the changes.";
                    break;
                case 'cancelled':
                    $userTitle = 'Booking Cancelled';
                    $userMessage = "Your booking {$bookingNo} has been cancelled successfully.";
                    $companyTitle = 'Booking Cancelled';
                    $companyMessage = "{$userName} has cancelled their booking {$bookingNo}.";
                    $icon = 'fas fa-times-circle';
                    break;
                case 'rescheduled':
                    $userTitle = 'Booking Rescheduled';
                    $userMessage = "Your booking {$bookingNo} has been rescheduled successfully.";
                    $companyTitle = 'Booking Rescheduled';
                    $companyMessage = "{$userName} has rescheduled their booking {$bookingNo}.";
                    $icon = 'fas fa-calendar-alt';
                    break;
            }
            
            // Send notification to the user
            $this->createNotification([
                'recipient_id' => $userId,
                'recipient_type' => 'user',
                'title' => $userTitle,
                'message' => $userMessage,
                'type' => 'booking',
                'icon' => $icon,
                'booking_id' => $bookingId
            ]);
            
            // Send notifications to company users
            $companyUsers = $this->getCompanyUsers();
            foreach ($companyUsers as $companyUser) {
                $this->createNotification([
                    'recipient_id' => $companyUser['user_id'],
                    'recipient_type' => 'company',
                    'title' => $companyTitle,
                    'message' => $companyMessage,
                    'type' => 'booking',
                    'icon' => $icon,
                    'booking_id' => $bookingId
                ]);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Booking update notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a notification record using MySQLi
     */
    public function createNotification($data) {
        try {
            // Check if notifications table exists
            $checkTable = $this->db->query("SHOW TABLES LIKE 'notifications'");
            if ($checkTable->num_rows == 0) {
                error_log("Notifications table does not exist, skipping notification creation");
                return false;
            }
            
            // Insert notification
            $stmt = $this->db->prepare("
                INSERT INTO notifications (
                    recipient_id, recipient_type, title, message, type, icon, booking_id,
                    is_read, created_at, updated_at
                ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())
            ");
            
            if (!$stmt) {
                error_log("Prepare notification statement error: " . $this->db->error);
                return false;
            }
            
            $bookingId = $data['booking_id'] ?? null;
            
            $stmt->bind_param(
                "isssssi",
                $data['recipient_id'],
                $data['recipient_type'],
                $data['title'],
                $data['message'],
                $data['type'],
                $data['icon'],
                $bookingId
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Create notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notifications for a user using MySQLi
     */
    public function getUserNotifications($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT n.*, b.booking_no 
                FROM notifications n 
                LEFT JOIN bookings b ON n.booking_id = b.booking_id 
                WHERE n.recipient_id = ? 
                ORDER BY n.created_at DESC 
                LIMIT ?
            ");
            
            if (!$stmt) {
                error_log("Prepare get notifications error: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $notifications = [];
            
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            
            $stmt->close();
            return $notifications;
            
        } catch (Exception $e) {
            error_log("Get notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unread notification count using MySQLi
     */
    public function getUnreadCount($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE recipient_id = ? AND is_read = 0
            ");
            
            if (!$stmt) {
                error_log("Prepare unread count error: " . $this->db->error);
                return 0;
            }
            
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row['count'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Get unread count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Mark notification as read using MySQLi
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1, updated_at = NOW() 
                WHERE id = ? AND recipient_id = ?
            ");
            
            if (!$stmt) {
                error_log("Prepare mark as read error: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("ii", $notificationId, $userId);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Mark as read error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read for a user using MySQLi
     */
    public function markAllAsRead($userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1, updated_at = NOW() 
                WHERE recipient_id = ? AND is_read = 0
            ");
            
            if (!$stmt) {
                error_log("Prepare mark all as read error: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("i", $userId);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Mark all as read error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user info using MySQLi
     */
    public function getUserInfo($userId) {
        try {
            // First try users_info table
            $stmt = $this->db->prepare("
                SELECT firstName, lastName, acc_type 
                FROM users_info 
                WHERE user_id = ?
            ");
            
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $userInfo = $result->fetch_assoc();
                    $stmt->close();
                    return $userInfo;
                }
                $stmt->close();
            }
            
            // If users_info doesn't exist or has no data, try users table
            $stmt = $this->db->prepare("
                SELECT COALESCE(first_name, name, '') as firstName, 
                       COALESCE(last_name, '') as lastName,
                       COALESCE(user_type, 'passenger') as acc_type
                FROM users 
                WHERE id = ?
            ");
            
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $userInfo = $result->fetch_assoc();
                    $stmt->close();
                    return $userInfo;
                }
                $stmt->close();
            }
            
            return ['firstName' => '', 'lastName' => '', 'acc_type' => 'passenger'];
            
        } catch (Exception $e) {
            error_log("Get user info error: " . $e->getMessage());
            return ['firstName' => '', 'lastName' => '', 'acc_type' => 'passenger'];
        }
    }
    
    /**
     * Get company users who should receive booking notifications using MySQLi
     * Made public so it can be called from update scripts
     */
    public function getCompanyUsers() {
        try {
            // Try to get company users from users_info table
            $stmt = $this->db->prepare("
                SELECT user_id, firstName, lastName 
                FROM users_info 
                WHERE acc_type = 'company'
                LIMIT 10
            ");
            
            $companyUsers = [];
            
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $companyUsers[] = $row;
                }
                $stmt->close();
                
                if (!empty($companyUsers)) {
                    return $companyUsers;
                }
            }
            
            // If no company users found in users_info, try users table
            $stmt = $this->db->prepare("
                SELECT id as user_id, 
                       COALESCE(first_name, name, '') as firstName, 
                       COALESCE(last_name, '') as lastName 
                FROM users 
                WHERE user_type = 'company'
                LIMIT 10
            ");
            
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $companyUsers[] = $row;
                }
                $stmt->close();
            }
            
            // If still no company users found, log warning
            if (empty($companyUsers)) {
                error_log("No company users found for booking notifications");
            }
            
            return $companyUsers;
            
        } catch (Exception $e) {
            error_log("Get company users error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update booking status and send notification using MySQLi
     */
    public function updateBookingStatus($bookingId, $status, $message = null) {
        try {
            $this->db->begin_transaction();
            
            // Update booking status
            $stmt = $this->db->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
            if (!$stmt) {
                throw new Exception("Prepare update booking error: " . $this->db->error);
            }
            
            $stmt->bind_param("si", $status, $bookingId);
            $stmt->execute();
            $stmt->close();
            
            // Get booking info
            $bookingInfo = $this->getBookingInfo($bookingId);
            
            if ($bookingInfo) {
                // Send notification to passenger
                $statusMessage = $message ?? "Your booking {$bookingInfo['booking_no']} status has been updated to: " . ucfirst($status);
                
                $this->createNotification([
                    'recipient_id' => $bookingInfo['user_id'],
                    'recipient_type' => 'user',
                    'title' => 'Booking Status Update',
                    'message' => $statusMessage,
                    'type' => 'booking',
                    'icon' => $this->getStatusIcon($status),
                    'booking_id' => $bookingId
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Update booking status error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get booking info using MySQLi
     */
    public function getBookingInfo($bookingId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
            if (!$stmt) {
                error_log("Prepare get booking info error: " . $this->db->error);
                return null;
            }
            
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $bookingInfo = $result->fetch_assoc();
                $stmt->close();
                return $bookingInfo;
            }
            
            $stmt->close();
            return null;
            
        } catch (Exception $e) {
            error_log("Get booking info error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get icon based on status
     */
    private function getStatusIcon($status) {
        $icons = [
            'confirmed' => 'fas fa-check-circle',
            'cancelled' => 'fas fa-times-circle',
            'pending' => 'fas fa-clock',
            'completed' => 'fas fa-flag-checkered',
            'approved' => 'fas fa-thumbs-up',
            'rejected' => 'fas fa-thumbs-down',
            'updated' => 'fas fa-edit',
            'rescheduled' => 'fas fa-calendar-alt'
        ];
        
        return $icons[$status] ?? 'fas fa-info-circle';
    }
    
    /**
     * Get recent notifications for dashboard using MySQLi
     */
    public function getRecentNotifications($userId, $limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT n.*, b.booking_no,
                       DATE_FORMAT(n.created_at, '%M %d, %Y at %h:%i %p') as formatted_date
                FROM notifications n 
                LEFT JOIN bookings b ON n.booking_id = b.booking_id 
                WHERE n.recipient_id = ? 
                ORDER BY n.created_at DESC 
                LIMIT ?
            ");
            
            if (!$stmt) {
                error_log("Prepare recent notifications error: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $notifications = [];
            
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            
            $stmt->close();
            return $notifications;
            
        } catch (Exception $e) {
            error_log("Get recent notifications error: " . $e->getMessage());
            return [];
        }
    }
}
?>