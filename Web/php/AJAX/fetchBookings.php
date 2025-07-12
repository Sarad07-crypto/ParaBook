<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection and notification system
include "../connection.php";
require_once 'bookingNotification.php';

// Check if connection exists
if (!isset($connect)) {
    echo json_encode(['error' => 'Database connection not available']);
    exit;
}

try {
    // Check if this is a POST request to complete a booking
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action']) && $input['action'] === 'complete' && isset($input['booking_id'])) {
            completeBooking($connect, $input['booking_id']);
        } else {
            echo json_encode(['error' => 'Invalid POST request']);
        }
    } else {
        // GET request - fetch bookings
        fetchBookings($connect);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}

function completeBooking($connect, $bookingId) {
    try {
        // Start transaction
        mysqli_begin_transaction($connect);
        
        // First, get the booking details and user info before updating
        $getBookingQuery = "
            SELECT 
                b.booking_id,
                b.booking_no,
                b.user_id,
                b.status,
                u.email as user_email,
                ui.firstName,
                ui.lastName
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN users_info ui ON b.user_id = ui.user_id
            WHERE b.booking_id = ?
        ";
        
        $stmt = mysqli_prepare($connect, $getBookingQuery);
        if (!$stmt) {
            throw new Exception('Failed to prepare booking details query: ' . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $bookingId);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            throw new Exception('Failed to get booking details: ' . mysqli_stmt_error($stmt));
        }
        
        $bookingResult = mysqli_stmt_get_result($stmt);
        $bookingData = mysqli_fetch_assoc($bookingResult);
        mysqli_stmt_close($stmt);
        
        if (!$bookingData) {
            throw new Exception('Booking not found');
        }
        
        // Check if already completed
        if ($bookingData['status'] === 'completed') {
            echo json_encode([
                'success' => false,
                'message' => 'Booking is already completed',
                'booking_id' => $bookingId
            ]);
            mysqli_rollback($connect);
            return;
        }
        
        // Update booking status to completed
        $updateQuery = "UPDATE bookings SET status = 'completed', updated_at = NOW() WHERE booking_id = ?";
        $stmt = mysqli_prepare($connect, $updateQuery);
        
        if (!$stmt) {
            throw new Exception('Failed to prepare update statement: ' . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $bookingId);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            throw new Exception('Failed to update booking: ' . mysqli_stmt_error($stmt));
        }
        
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affectedRows === 0) {
            throw new Exception('No booking was updated');
        }
        
        // Build user name
        $userName = 'Customer';
        if (!empty($bookingData['firstName']) && !empty($bookingData['lastName'])) {
            $userName = trim($bookingData['firstName'] . ' ' . $bookingData['lastName']);
        } elseif (!empty($bookingData['firstName'])) {
            $userName = $bookingData['firstName'];
        } elseif (!empty($bookingData['lastName'])) {
            $userName = $bookingData['lastName'];
        } elseif (!empty($bookingData['user_email'])) {
            $userName = explode('@', $bookingData['user_email'])[0];
        }
        
        // Initialize notification system and send notifications
        $notificationSystem = new BookingNotificationSystem($connect);
        $notificationSuccess = true;
        $notificationError = false;
        
        try {
            // Send notification to passenger about completion
            $notificationSystem->createNotification([
                'recipient_id' => $bookingData['user_id'],
                'recipient_type' => 'passenger',
                'title' => 'Flight Completed Successfully',
                'message' => "Your flight for booking {$bookingData['booking_no']} has been completed successfully. We hope you enjoyed your experience! Please consider leaving a review.",
                'type' => 'completed',
                'icon' => 'fas fa-check-circle',
                'booking_id' => $bookingId
            ]);
            
            // Send notification to company users about completion
            $companyUsers = $notificationSystem->getCompanyUsers();
            foreach ($companyUsers as $companyUser) {
                $notificationSystem->createNotification([
                    'recipient_id' => $companyUser['user_id'],
                    'recipient_type' => 'company',
                    'title' => 'Flight Completed',
                    'message' => "Flight booking for {$userName} has been completed successfully.",
                    'type' => 'completed',
                    'icon' => 'fas fa-check-circle',
                    'booking_id' => $bookingId
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Notification error for booking completion: " . $e->getMessage());
            $notificationError = true;
            $notificationSuccess = false;
        }
        
        // Commit transaction
        mysqli_commit($connect);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Booking completed successfully',
            'booking_id' => $bookingId,
            'passenger_name' => $userName,
            'notification_sent' => $notificationSuccess,
            'notification_error' => $notificationError
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($connect);
        
        error_log("Complete booking error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'booking_id' => $bookingId
        ]);
    }
}

function fetchBookings($connect) {
    $page = 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    // Fetch bookings with user info including full name and status
    $baseQuery = "
        SELECT 
            b.booking_id,
            b.booking_no,
            b.user_id,
            b.date,
            b.pickup,
            b.flight_type,
            b.weight,
            b.age,
            b.medical_condition,
            b.created_at,
            b.updated_at,
            b.status,
            u.email as user_email,
            ui.firstName,
            ui.lastName,
            ui.contact as user_phone,
            ui.gender,
            ui.country,
            ui.dob
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN users_info ui ON b.user_id = ui.user_id
        ORDER BY b.created_at DESC
        LIMIT $limit OFFSET $offset
    ";

    // Count query
    $countQuery = "SELECT COUNT(*) as total FROM bookings";

    $countResult = mysqli_query($connect, $countQuery);
    if (!$countResult) {
        throw new Exception('Count query failed: ' . mysqli_error($connect));
    }

    $totalBookings = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($totalBookings / $limit);

    // Get bookings
    $result = mysqli_query($connect, $baseQuery);
    if (!$result) {
        throw new Exception('Data query failed: ' . mysqli_error($connect));
    }

    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Build full name from users_info table
        if (!empty($row['firstName']) && !empty($row['lastName'])) {
            $row['user_name'] = trim($row['firstName'] . ' ' . $row['lastName']);
        } elseif (!empty($row['firstName'])) {
            $row['user_name'] = $row['firstName'];
        } elseif (!empty($row['lastName'])) {
            $row['user_name'] = $row['lastName'];
        } else {
            // Fallback to email username if no name in users_info
            $row['user_name'] = isset($row['user_email']) ? explode('@', $row['user_email'])[0] : 'N/A';
        }

        $row['formatted_date'] = $row['date'] ? date('M j, Y', strtotime($row['date'])) : 'N/A';
        $row['formatted_created_at'] = $row['created_at'] ? date('M j, Y g:i A', strtotime($row['created_at'])) : 'N/A';

        $row['user_email'] = $row['user_email'] ?? 'N/A';
        $row['user_phone'] = $row['user_phone'] ?? 'N/A';
        $row['weight'] = $row['weight'] ?? 'N/A';
        $row['age'] = $row['age'] ?? 'N/A';
        $row['medical_condition'] = $row['medical_condition'] ?? 'None';
        $row['pickup'] = $row['pickup'] ?? 'N/A';
        $row['flight_type'] = $row['flight_type'] ?? 'N/A';
        $row['booking_no'] = $row['booking_no'] ?? 'N/A';
        $row['gender'] = $row['gender'] ?? 'N/A';
        $row['country'] = $row['country'] ?? 'N/A';
        $row['dob'] = $row['dob'] ?? 'N/A';

        // Keep the actual status from database and ensure it's properly formatted
        $row['status'] = trim(strtolower($row['status'] ?? 'pending'));

        // Remove firstName and lastName from response as we now have user_name
        unset($row['firstName'], $row['lastName']);

        $bookings[] = $row;
    }

    // Statistics
    $statsQuery = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN DATE(b.date) = CURDATE() THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM bookings b
    ";

    $statsResult = mysqli_query($connect, $statsQuery);
    $stats = ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'completed' => 0, 'today' => 0];

    if ($statsResult) {
        $stats = mysqli_fetch_assoc($statsResult);
        $stats['total'] = (int)$stats['total'];
        $stats['confirmed'] = (int)$stats['confirmed'];
        $stats['pending'] = (int)$stats['pending'];
        $stats['completed'] = (int)$stats['completed'];
        $stats['today'] = (int)$stats['today'];
    }

    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'totalBookings' => $totalBookings,
        'statistics' => $stats,
        'message' => 'Data fetched successfully'
    ]);
}
?>