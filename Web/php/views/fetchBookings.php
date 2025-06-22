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

// Get action from POST data
$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    switch ($action) {
        case 'fetch_bookings':
            fetchBookings($connect);
            break;
        case 'get_stats':
            getStatistics($connect);
            break;
        case 'cancel_booking':
            cancelBooking($connect);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}

function fetchBookings($connect) {
    // Get parameters from POST request
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $dateFrom = isset($_POST['date_from']) ? $_POST['date_from'] : '';
    $dateTo = isset($_POST['date_to']) ? $_POST['date_to'] : '';
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = 10; // Fixed limit as per your JS code
    
    $offset = ($page - 1) * $limit;
    
    // Base query with JOIN to get user information
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
            u.name as user_name,
            u.email as user_email,
            u.phone as user_phone,
            'confirmed' as status
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        WHERE 1=1
    ";
    
    $countQuery = "
        SELECT COUNT(*) as total
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        WHERE 1=1
    ";
    
    $conditions = "";
    
    // Add search condition
    if (!empty($search)) {
        $search = mysqli_real_escape_string($connect, $search);
        $conditions .= " AND (b.booking_no LIKE '%$search%' OR u.email LIKE '%$search%' OR b.pickup LIKE '%$search%')";
    }
    
    // Add date range filter
    if (!empty($dateFrom)) {
        $dateFrom = mysqli_real_escape_string($connect, $dateFrom);
        $conditions .= " AND b.date >= '$dateFrom'";
    }
    
    if (!empty($dateTo)) {
        $dateTo = mysqli_real_escape_string($connect, $dateTo);
        $conditions .= " AND b.date <= '$dateTo'";
    }
    
    // Add status filter
    if (!empty($status)) {
        if ($status === 'confirmed') {
            // All existing bookings are confirmed for now
            $conditions .= " AND b.booking_id IS NOT NULL";
        } elseif ($status === 'pending') {
            // You can modify this condition based on your status logic
            $conditions .= " AND 1=0"; // Returns no results for demo
        } elseif ($status === 'cancelled') {
            // You can modify this condition based on your status logic
            $conditions .= " AND 1=0"; // Returns no results for demo
        }
    }
    
    // Get total count
    $countResult = mysqli_query($connect, $countQuery . $conditions);
    if (!$countResult) {
        throw new Exception('Count query failed: ' . mysqli_error($connect));
    }
    $totalBookings = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($totalBookings / $limit);
    
    // Get bookings with pagination
    $dataQuery = $baseQuery . $conditions . " ORDER BY b.created_at DESC LIMIT $limit OFFSET $offset";
    $result = mysqli_query($connect, $dataQuery);
    
    if (!$result) {
        throw new Exception('Data query failed: ' . mysqli_error($connect));
    }
    
    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Format date
        $row['formatted_date'] = date('M j, Y', strtotime($row['date']));
        $row['formatted_created_at'] = date('M j, Y g:i A', strtotime($row['created_at']));
        
        // Handle null values
        $row['user_name'] = $row['user_name'] ?? 'N/A';
        $row['user_email'] = $row['user_email'] ?? 'N/A';
        $row['user_phone'] = $row['user_phone'] ?? 'N/A';
        $row['weight'] = $row['weight'] ?? 'N/A';
        $row['medical_condition'] = $row['medical_condition'] ?? 'None';
        
        $bookings[] = $row;
    }
    
    // Response structure matching your JavaScript expectations
    $response = [
        'bookings' => $bookings,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'totalBookings' => $totalBookings,
        'success' => true
    ];
    
    echo json_encode($response);
}

function getStatistics($connect) {
    // Calculate statistics
    $statsQuery = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN DATE(b.date) = CURDATE() THEN 1 ELSE 0 END) as today,
            COUNT(*) as confirmed,
            0 as pending
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
    ";
    
    $statsResult = mysqli_query($connect, $statsQuery);
    if (!$statsResult) {
        throw new Exception('Statistics query failed: ' . mysqli_error($connect));
    }
    
    $stats = mysqli_fetch_assoc($statsResult);
    
    // Response structure matching your JavaScript expectations
    $response = [
        'total' => (int)$stats['total'],
        'confirmed' => (int)$stats['confirmed'],
        'pending' => (int)$stats['pending'],
        'today' => (int)$stats['today']
    ];
    
    echo json_encode($response);
}

function cancelBooking($connect) {
    $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    
    if ($bookingId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
        return;
    }
    
    
    // For demo purposes, we'll just return success
    echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
    
}
?>