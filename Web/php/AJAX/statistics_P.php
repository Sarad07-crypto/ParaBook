<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Start session before any output
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Database connection
    if (!file_exists('../connection.php')) {
        throw new Exception('Database connection file not found');
    }
    
    require_once '../connection.php';
    
    // Check if database connection exists
    if (!isset($connect) || !$connect) {
        throw new Exception('Database connection failed');
    }

    // Get user_id from session
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        throw new Exception('User not logged in');
    }

    // Validate and sanitize input parameters
    $date_filter = isset($_GET['date_filter']) ? trim($_GET['date_filter']) : 'all';
    $status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Validate date filter values
    $valid_date_filters = ['all', 'thisMonth', 'lastMonth', 'thisYear'];
    if (!in_array($date_filter, $valid_date_filters)) {
        $date_filter = 'all';
    }

    // Base query with joins - Using MySQLi prepared statements
    $query = "
        SELECT 
            b.booking_id,
            b.booking_no,
            b.date as flight_date,
            b.flight_type,
            b.total_amount,
            b.status,
            b.pickup,
            b.weight,
            b.age,
            cs.service_title,
            COALESCE(r.rating, 0) as rating,
            COALESCE(r.review_text, '') as review
        FROM bookings b
        LEFT JOIN company_services cs ON b.service_id = cs.id
        LEFT JOIN reviews r ON (r.user_id = b.user_id AND r.service_id = b.service_id)
        WHERE b.user_id = ?
    ";

    $params = [$user_id];
    $types = "i"; // integer for user_id

    // Add date filter with proper SQL
    if ($date_filter !== 'all') {
        switch ($date_filter) {
            case 'thisMonth':
                $query .= " AND DATE_FORMAT(b.date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
                break;
            case 'lastMonth':
                $query .= " AND DATE_FORMAT(b.date, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')";
                break;
            case 'thisYear':
                $query .= " AND YEAR(b.date) = YEAR(CURDATE())";
                break;
        }
    }

    // Add status filter
    if ($status_filter !== 'all' && !empty($status_filter)) {
        $query .= " AND b.status = ?";
        $params[] = $status_filter;
        $types .= "s"; // string for status
    }

    // Add search filter
    if (!empty($search)) {
        $query .= " AND (b.flight_type LIKE ? OR cs.service_title LIKE ? OR b.booking_no LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "sss"; // three strings for search
    }

    $query .= " ORDER BY b.date DESC";

    // Execute query with MySQLi
    $stmt = $connect->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare SQL statement: ' . $connect->error);
    }
    
    // Bind parameters if any exist
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $execute_result = $stmt->execute();
    if (!$execute_result) {
        throw new Exception('Failed to execute query: ' . $stmt->error);
    }
    
    // Get result set
    $result = $stmt->get_result();
    $bookings = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    }
    
    $stmt->close();

    // Calculate statistics safely
    $completed_bookings = array_filter($bookings, function($b) {
        return isset($b['status']) && $b['status'] === 'completed';
    });

    $total_flights = count($completed_bookings);
    
    // Calculate total spent with proper number handling
    $total_spent = 0;
    foreach ($completed_bookings as $booking) {
        $amount = floatval($booking['total_amount'] ?? 0);
        $total_spent += $amount;
    }
    
    // Calculate average rating
    $ratings = [];
    foreach ($completed_bookings as $booking) {
        $rating = intval($booking['rating'] ?? 0);
        if ($rating > 0) {
            $ratings[] = $rating;
        }
    }
    $avg_rating = count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : 0.0;

    // Format bookings data safely
    $formatted_bookings = [];
    foreach ($bookings as $booking) {
        $formatted_bookings[] = [
            'id' => intval($booking['booking_id'] ?? 0),
            'bookingNo' => $booking['booking_no'] ?? '',
            'flightDate' => $booking['flight_date'] ?? '',
            'flightType' => $booking['flight_type'] ?: ($booking['service_title'] ?? ''),
            'amount' => floatval($booking['total_amount'] ?? 0),
            'status' => $booking['status'] ?? '',
            'rating' => intval($booking['rating'] ?? 0),
            'review' => $booking['review'] ?? '',
            'pickup' => $booking['pickup'] ?? '',
            'weight' => $booking['weight'] ?? '',
            'age' => $booking['age'] ?? ''
        ];
    }

    // Success response
    $response = [
        'success' => true,
        'data' => [
            'statistics' => [
                'total_flights' => $total_flights,
                'total_spent' => $total_spent,
                'avg_rating' => $avg_rating
            ],
            'bookings' => $formatted_bookings
        ]
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (mysqli_sql_exception $e) {
    error_log("Database error in statistics_P.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error in statistics_P.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}