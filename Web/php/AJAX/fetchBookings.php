<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
include "../connection.php";

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
    // Check if status column exists
    $checkColumnQuery = "SHOW COLUMNS FROM bookings LIKE 'status'";
    $columnResult = mysqli_query($connect, $checkColumnQuery);
    $statusColumnExists = ($columnResult && mysqli_num_rows($columnResult) > 0);
    
    if (!$statusColumnExists) {
        // Add status column if it doesn't exist
        $addColumnQuery = "ALTER TABLE bookings ADD COLUMN status VARCHAR(20) DEFAULT 'pending'";
        $addResult = mysqli_query($connect, $addColumnQuery);
        
        if (!$addResult) {
            throw new Exception('Failed to add status column: ' . mysqli_error($connect));
        }
    }
    
    // Update booking status to confirmed
    $updateQuery = "UPDATE bookings SET status = 'confirmed', updated_at = NOW() WHERE booking_id = ?";
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
    
    if ($affectedRows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Booking completed successfully',
            'booking_id' => $bookingId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Booking not found or already completed',
            'booking_id' => $bookingId
        ]);
    }
}

function fetchBookings($connect) {
    $page = 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    // Check if status column exists
    $statusColumnExists = false;
    $checkColumnQuery = "SHOW COLUMNS FROM bookings LIKE 'status'";
    $columnResult = mysqli_query($connect, $checkColumnQuery);
    if ($columnResult && mysqli_num_rows($columnResult) > 0) {
        $statusColumnExists = true;
    }

    // Fetch bookings with user info including full name and status
    $statusField = $statusColumnExists ? "COALESCE(b.status, 'pending') as status" : "'pending' as status";
    
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
            $statusField,
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

        // Keep the actual status from database (pending/confirmed)
        $row['status'] = $row['status'] ?? 'pending';

        // Remove firstName and lastName from response as we now have user_name
        unset($row['firstName'], $row['lastName']);

        $bookings[] = $row;
    }

    // Statistics - check if status column exists for proper counting
    $statusColumnExists = false;
    $checkColumnQuery = "SHOW COLUMNS FROM bookings LIKE 'status'";
    $columnResult = mysqli_query($connect, $checkColumnQuery);
    if ($columnResult && mysqli_num_rows($columnResult) > 0) {
        $statusColumnExists = true;
    }

    if ($statusColumnExists) {
        $statsQuery = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN DATE(b.date) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN COALESCE(b.status, 'pending') = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN COALESCE(b.status, 'pending') = 'pending' THEN 1 ELSE 0 END) as pending
            FROM bookings b
        ";
    } else {
        // If no status column, treat all as pending
        $statsQuery = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN DATE(b.date) = CURDATE() THEN 1 ELSE 0 END) as today,
                0 as confirmed,
                COUNT(*) as pending
            FROM bookings b
        ";
    }

    $statsResult = mysqli_query($connect, $statsQuery);
    $stats = ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'today' => 0];

    if ($statsResult) {
        $stats = mysqli_fetch_assoc($statsResult);
        $stats['total'] = (int)$stats['total'];
        $stats['confirmed'] = (int)$stats['confirmed'];
        $stats['pending'] = (int)$stats['pending'];
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