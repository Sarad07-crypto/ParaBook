<?php

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$_servername = "localhost:3307";
$_username = "root";
$_password = "";
$_database = "parabook";

$connect = mysqli_connect($_servername, $_username, $_password, $_database);

if ($connect->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error_code' => 'CONNECTION_ERROR'
    ]);
    exit;
}

// Response array
$response = array(
    'success' => false,
    'message' => '',
    'data' => null,
    'error_code' => ''
);

try {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        throw new Exception('Authentication required. Please log in to view your bookings.', 401);
    }

    $current_user_id = $_SESSION['user_id'];
    
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 405);
    }
    
    // Get and validate input data
    $booking_number = isset($_POST['booking_number']) ? strtoupper(trim($_POST['booking_number'])) : '';
    $date_of_birth = isset($_POST['date_of_birth']) ? trim($_POST['date_of_birth']) : '';
    
    // Debug: Log received data (remove in production)
    error_log("User ID: " . $current_user_id . " - Searching booking: " . $booking_number);
    error_log("Input DOB: " . $date_of_birth);
    
    // Validation
    if (empty($booking_number)) {
        throw new Exception('Booking number is required', 400);
    }
    
    if (empty($date_of_birth)) {
        throw new Exception('Date of birth is required', 400);
    }
    
    // Validate and normalize date format
    $date_obj = DateTime::createFromFormat('Y-m-d', $date_of_birth);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $date_of_birth) {
        // Try other common formats
        $date_obj = DateTime::createFromFormat('m/d/Y', $date_of_birth);
        if (!$date_obj) {
            $date_obj = DateTime::createFromFormat('d/m/Y', $date_of_birth);
        }
        if (!$date_obj) {
            throw new Exception('Invalid date format. Expected YYYY-MM-DD, MM/DD/YYYY, or DD/MM/YYYY', 400);
        }
    }
    
    // Normalize date to YYYY-MM-DD format for database comparison
    $normalized_dob = $date_obj->format('Y-m-d');
    
    // First, check if booking exists for the current user
    $check_booking_sql = "SELECT booking_id, user_id FROM bookings WHERE UPPER(booking_no) = UPPER(?) AND user_id = ?";
    $check_stmt = $connect->prepare($check_booking_sql);
    
    if (!$check_stmt) {
        throw new Exception('Database prepare error: ' . $connect->error, 500);
    }
    
    $check_stmt->bind_param('si', $booking_number, $current_user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        // Check if booking exists but belongs to another user
        $other_user_check_sql = "SELECT booking_id FROM bookings WHERE UPPER(booking_no) = UPPER(?)";
        $other_user_stmt = $connect->prepare($other_user_check_sql);
        $other_user_stmt->bind_param('s', $booking_number);
        $other_user_stmt->execute();
        $other_user_result = $other_user_stmt->get_result();
        
        if ($other_user_result->num_rows > 0) {
            // Booking exists but belongs to another user
            $response['error_code'] = 'UNAUTHORIZED';
            throw new Exception('You are not authorized to view this booking.', 403);
        } else {
            // Booking doesn't exist at all
            $response['error_code'] = 'NOT_FOUND';
            throw new Exception('Booking not found in your account.', 404);
        }
    }
    
    $check_stmt->close();
    
    $sql = "SELECT 
                b.booking_id,
                b.booking_no,
                b.user_id,
                b.date,
                b.pickup,
                b.flight_type,
                b.weight,
                b.age,
                b.medical_condition,
                b.status,
                COALESCE(ui.firstName, 'N/A') as firstName,
                COALESCE(ui.lastName, 'N/A') as lastName,
                COALESCE(ui.gender, 'N/A') as gender,
                COALESCE(ui.contact, 'N/A') as contact,
                ui.dob,
                COALESCE(ui.country, 'N/A') as country,
                COALESCE(u.email, 'N/A') as email
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN users_info ui ON u.id = ui.user_id
            WHERE UPPER(b.booking_no) = UPPER(?) 
            AND b.user_id = ?";
    
    // Prepare and execute the statement
    $stmt = $connect->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $connect->error, 500);
    }
    
    $stmt->bind_param('si', $booking_number, $current_user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    // Debug: Log the fetched data
    error_log("Fetched booking data: " . print_r($booking, true));
    
    if ($booking) {
        // Get the DOB from database - handle it like other optional fields
        $db_dob = $booking['dob'] ?: null;
        
        // Debug: Log DOB comparison
        error_log("DB DOB: '" . ($db_dob ?? 'NULL') . "' vs Input DOB: '" . $normalized_dob . "'");
        
        // Additional security: Verify date of birth matches
        if ($db_dob && $db_dob !== '0000-00-00') {
            if ($db_dob !== $normalized_dob) {
                $response['success'] = false;
                $response['message'] = 'Date of birth does not match our records.';
                $response['error_code'] = 'DOB_MISMATCH';
                error_log("DOB Mismatch - DB: '" . $db_dob . "' Input: '" . $normalized_dob . "'");
            } else {
                // DOB matches, proceed with success
                $response['success'] = true;
                $response['message'] = 'Booking found successfully';
                $response['data'] = array(
                    'booking_id' => $booking['booking_id'],
                    'booking_no' => $booking['booking_no'],
                    'user_id' => $booking['user_id'],
                    'date' => $booking['date'],
                    'pickup' => $booking['pickup'] ?: 'Not specified',
                    'flight_type' => $booking['flight_type'] ?: 'Not specified',
                    'weight' => $booking['weight'] ?: '0',
                    'age' => $booking['age'] ?: 'Not specified',
                    'medical_condition' => $booking['medical_condition'] ?: 'None',
                    'status' => ucfirst($booking['status'] ?: 'pending'),
                    'firstName' => $booking['firstName'],
                    'lastName' => $booking['lastName'],
                    'gender' => $booking['gender'],
                    'contact' => $booking['contact'],
                    'country' => $booking['country'],
                    'email' => $booking['email'],
                    // Security flag to confirm ownership verification
                    'verified_ownership' => true
                );
            }
        } else {
            // DOB is not set in database, but user provided one - this might be an issue
            $response['success'] = false;
            $response['message'] = 'Date of birth is not set in our records. Please contact support.';
            $response['error_code'] = 'DOB_NOT_SET';
        }
    } else {
        // This shouldn't happen since we checked above, but just in case
        $response['success'] = false;
        $response['message'] = 'Booking data could not be retrieved.';
        $response['error_code'] = 'DATA_ERROR';
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    // Handle different HTTP status codes
    $status_code = $e->getCode();
    
    switch ($status_code) {
        case 401:
            $response['error_code'] = 'NOT_AUTHENTICATED';
            break;
        case 403:
            if (empty($response['error_code'])) {
                $response['error_code'] = 'UNAUTHORIZED';
            }
            break;
        case 404:
            if (empty($response['error_code'])) {
                $response['error_code'] = 'NOT_FOUND';
            }
            break;
        case 400:
            $response['error_code'] = 'VALIDATION_ERROR';
            break;
        case 500:
            $response['error_code'] = 'SERVER_ERROR';
            break;
        default:
            $response['error_code'] = 'GENERAL_ERROR';
    }
    
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Log the actual error for debugging (check your server error logs)
    error_log('Booking Lookup Error - User ID: ' . ($current_user_id ?? 'not set') . ' - Error: ' . $e->getMessage());
    
    // Set appropriate HTTP response code
    if ($status_code >= 400 && $status_code < 600) {
        http_response_code($status_code);
    }
}

// Close database connection
if (isset($connect)) {
    $connect->close();
}

// Output JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
exit;
?>