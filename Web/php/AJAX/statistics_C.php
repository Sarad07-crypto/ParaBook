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

    // Get user_id from session (company owner)
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        throw new Exception('User not logged in');
    }

    // Get time filter parameter
    $time_filter = isset($_GET['time_filter']) ? trim($_GET['time_filter']) : 'today';
    
    // Validate time filter
    $valid_filters = ['today', 'week', 'month', 'year'];
    if (!in_array($time_filter, $valid_filters)) {
        $time_filter = 'today';
    }

    // Build date condition based on filter
    $date_condition = '';
    switch ($time_filter) {
        case 'today':
            $date_condition = "AND DATE(b.date) = CURDATE()";
            break;
        case 'week':
            $date_condition = "AND YEARWEEK(b.date, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $date_condition = "AND DATE_FORMAT(b.date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
            break;
        case 'year':
            $date_condition = "AND YEAR(b.date) = YEAR(CURDATE())";
            break;
    }

    // 1. Get Total Bookings
    $bookings_query = "
        SELECT COUNT(*) as total_bookings 
        FROM bookings b 
        INNER JOIN company_services cs ON b.service_id = cs.id 
        WHERE cs.user_id = ? $date_condition
    ";
    $stmt = $connect->prepare($bookings_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $bookings_result = $stmt->get_result();
    $total_bookings = $bookings_result->fetch_assoc()['total_bookings'] ?? 0;
    $stmt->close();

    // 2. Get Total Revenue
    $revenue_query = "
        SELECT COALESCE(SUM(b.total_amount), 0) as total_revenue 
        FROM bookings b 
        INNER JOIN company_services cs ON b.service_id = cs.id 
        WHERE cs.user_id = ? AND b.status = 'completed' $date_condition
    ";
    $stmt = $connect->prepare($revenue_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $revenue_result = $stmt->get_result();
    $total_revenue = $revenue_result->fetch_assoc()['total_revenue'] ?? 0;
    $stmt->close();

    // 3. Get Total Flights (completed bookings)
    $flights_query = "
        SELECT COUNT(*) as total_flights 
        FROM bookings b 
        INNER JOIN company_services cs ON b.service_id = cs.id 
        WHERE cs.user_id = ? AND b.status = 'completed' $date_condition
    ";
    $stmt = $connect->prepare($flights_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $flights_result = $stmt->get_result();
    $total_flights = $flights_result->fetch_assoc()['total_flights'] ?? 0;
    $stmt->close();

    // 4. Get Total Cancelled Bookings
    $cancelled_query = "
    SELECT COUNT(*) as total_cancelled 
    FROM bookings b 
    INNER JOIN company_services cs ON b.service_id = cs.id 
    WHERE cs.user_id = ? AND b.status = 'cancelled' $date_condition
    ";
    $stmt = $connect->prepare($cancelled_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cancelled_result = $stmt->get_result();
    $total_cancelled = $cancelled_result->fetch_assoc()['total_cancelled'] ?? 0;
    $stmt->close();

    // 5. Get All Services with titles and ratings
    $services_query = "
        SELECT 
            cs.id, 
            cs.service_title, 
            cs.company_name,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(r.rating) as rating_count
        FROM company_services cs
        LEFT JOIN reviews r ON cs.id = r.service_id
        WHERE cs.user_id = ? AND cs.status = 'approved'
        GROUP BY cs.id, cs.service_title, cs.company_name
        ORDER BY cs.service_title ASC
    ";
    $stmt = $connect->prepare($services_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $services_result = $stmt->get_result();
    $services = [];
    while ($row = $services_result->fetch_assoc()) {
        $services[] = [
            'id' => $row['id'],
            'title' => $row['service_title'],
            'company' => $row['company_name'],
            'avg_rating' => round(floatval($row['avg_rating']), 1),
            'rating_count' => intval($row['rating_count'])
        ];
    }
    $stmt->close();

    // 6. Get Flight Types with Prices (booking distribution)
    $flight_types_query = "
        SELECT 
            b.flight_type,
            COUNT(*) as count,
            AVG(b.total_amount) as avg_price,
            SUM(b.total_amount) as total_amount
        FROM bookings b 
        INNER JOIN company_services cs ON b.service_id = cs.id 
        WHERE cs.user_id = ? $date_condition
        GROUP BY b.flight_type
        ORDER BY count DESC
    ";
    $stmt = $connect->prepare($flight_types_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $flight_types_result = $stmt->get_result();
    $flight_types = [];
    while ($row = $flight_types_result->fetch_assoc()) {
        $flight_types[] = [
            'type' => $row['flight_type'],
            'count' => intval($row['count']),
            'avg_price' => floatval($row['avg_price']),
            'total_amount' => floatval($row['total_amount'])
        ];
    }
    $stmt->close();

    // 7. Get Today's Flight Status (always current date regardless of filter)
    // Fixed: Using users_info table and concatenating firstName + lastName
    $todays_flights_query = "
        SELECT 
            b.booking_id,
            b.booking_no,
            b.flight_type,
            b.status,
            b.pickup,
            b.weight,
            b.age,
            cs.service_title,
            CONCAT(ui.firstName, ' ', ui.lastName) as customer_name,
            TIME(b.created_at) as booking_time
        FROM bookings b 
        INNER JOIN company_services cs ON b.service_id = cs.id 
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN users_info ui ON u.id = ui.user_id
        WHERE cs.user_id = ? AND DATE(b.date) = CURDATE()
        ORDER BY b.created_at ASC
    ";
    $stmt = $connect->prepare($todays_flights_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $todays_flights_result = $stmt->get_result();
    $todays_flights = [];
    while ($row = $todays_flights_result->fetch_assoc()) {
        $todays_flights[] = [
            'id' => $row['booking_id'],
            'booking_no' => $row['booking_no'],
            'flight_type' => $row['flight_type'],
            'service_title' => $row['service_title'],
            'customer_name' => $row['customer_name'] ?? 'N/A',
            'status' => $row['status'],
            'pickup' => $row['pickup'],
            'weight' => $row['weight'],
            'age' => $row['age'],
            'booking_time' => $row['booking_time']
        ];
    }
    $stmt->close();

    // Calculate percentage changes (simplified - you can enhance this with historical data)
    $booking_change = rand(5, 25); // Placeholder - implement actual calculation
    $revenue_change = rand(8, 30);
    $flight_change = rand(-5, 20);
    $cancelled_change = rand(-10, 15); // Change for cancelled bookings

    // Update the response data structure
    $response = [
        'success' => true,
        'time_filter' => $time_filter,
        'data' => [
            'statistics' => [
                'total_bookings' => intval($total_bookings),
                'total_revenue' => floatval($total_revenue),
                'total_flights' => intval($total_flights),
                'total_cancelled' => intval($total_cancelled), // Changed from avg_rating
                'changes' => [
                    'bookings' => $booking_change,
                    'revenue' => $revenue_change,
                    'flights' => $flight_change,
                    'cancelled' => $cancelled_change // Changed from rating
                ]
            ],
            'services' => $services,
            'flight_types' => $flight_types,
            'todays_flights' => $todays_flights
        ]
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (mysqli_sql_exception $e) {
    error_log("Database error in statistics_company.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'debug_error' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    error_log("Error in statistics_company.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>