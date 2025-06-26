<?php
session_start();
include "../connection.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['authorized' => false, 'message' => 'User not logged in']);
    exit();
}

// Get booking_no from POST data
$booking_no = isset($_POST['booking_no']) ? trim($_POST['booking_no']) : '';
echo $booking_no;
if (empty($booking_no)) {
    echo json_encode(['authorized' => false, 'message' => 'Invalid booking number']);
    exit();
}

try {
    // Verify booking ownership using booking_no
    $stmt = $connect->prepare("SELECT booking_id FROM bookings WHERE booking_no = ? AND user_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connect->error);
    }
    
    $stmt->bind_param("si", $booking_no, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['authorized' => true, 'message' => 'Booking ownership verified']);
    } else {
        echo json_encode(['authorized' => false, 'message' => 'Booking not found or not authorized']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log('Error in verifyBookingOwnership.php: ' . $e->getMessage());
    echo json_encode(['authorized' => false, 'message' => 'Database error occurred']);
}


// =======================================
// cancelBooking.php - Cancel booking with ownership verification
// =======================================
/*
session_start();
header('Content-Type: application/json');

// Database connection
$_servername = "localhost:3307";
$_username = "root";
$_password = "";
$_database = "parabook";

$connect = mysqli_connect($_servername, $_username, $_password, $_database);

function sendCancelResponse($success, $message = '') {
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

// Check authentication
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    sendCancelResponse(false, 'Authentication required');
}

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendCancelResponse(false, 'Invalid request method');
}

$bookingId = isset($_POST['booking_no']) ? trim($_POST['booking_no']) : '';

if (empty($bookingId)) {
    sendCancelResponse(false, 'Missing booking ID');
}

try {
    // Verify ownership and check if booking can be cancelled
    $checkSql = "SELECT status FROM bookings WHERE booking_no = ? AND user_id = ?";
    $checkStmt = $connect->prepare($checkSql);
    $checkStmt->bind_param('si', $bookingId, $_SESSION['user_id']);
    $checkStmt->execute();
    
    $result = $checkStmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking) {
        sendCancelResponse(false, 'Booking not found or you are not authorized');
    }
    
    if (strtolower($booking['status']) === 'cancelled') {
        sendCancelResponse(false, 'Booking is already cancelled');
    }
    
    if (strtolower($booking['status']) === 'completed') {
        sendCancelResponse(false, 'Cannot cancel completed booking');
    }
    
    // Update booking status to cancelled
    $updateSql = "UPDATE bookings SET status = 'cancelled' WHERE booking_no = ? AND user_id = ?";
    $updateStmt = $connect->prepare($updateSql);
    $updateStmt->bind_param('si', $bookingId, $_SESSION['user_id']);
    
    if ($updateStmt->execute() && $updateStmt->affected_rows > 0) {
        sendCancelResponse(true, 'Booking cancelled successfully');
    } else {
        sendCancelResponse(false, 'Failed to cancel booking');
    }
    
} catch (Exception $e) {
    error_log("Error in cancelBooking.php: " . $e->getMessage());
    sendCancelResponse(false, 'Database error occurred');
} finally {
    if (isset($connect)) {
        $connect->close();
    }
}
*/

// =======================================
// Usage Instructions
// =======================================
/*

IMPORTANT SECURITY UPDATES MADE:

1. SESSION AUTHENTICATION:
   - Added session_start() to check user login status
   - Validates $_SESSION['user_id'] before any booking operations
   - Returns appropriate error codes for unauthenticated users

2. USER OWNERSHIP VERIFICATION:
   - Modified SQL queries to include user_id in WHERE clause
   - Prevents users from accessing other users' bookings
   - Added checks to differentiate between "not found" and "unauthorized"

3. ENHANCED ERROR HANDLING:
   - Specific error codes: NOT_AUTHENTICATED, UNAUTHORIZED, NOT_FOUND, etc.
   - Proper HTTP status codes (401, 403, 404, 500)
   - Better error logging without exposing sensitive information

4. ADDITIONAL SECURITY MEASURES:
   - Input validation and sanitization
   - SQL injection prevention using prepared statements
   - Proper error responses without revealing system details

5. IMPLEMENTATION STEPS:
   a) Replace your current bookingLookup.php with the secure version above
   b) Create checkAuth.php file (uncomment the first section)
   c) Create verifyBookingOwnership.php (uncomment the second section)
   d) Create cancelBooking.php (uncomment the third section)
   e) Ensure your login system properly sets $_SESSION['user_id']
   f) Test thoroughly with different user accounts

6. DATABASE REQUIREMENTS:
   - Ensure your 'bookings' table has a 'user_id' column
   - This column should reference the 'users.id' field
   - If missing, add it with: ALTER TABLE bookings ADD COLUMN user_id INT NOT NULL;

7. FRONTEND INTEGRATION:
   - Use the secure JavaScript version provided earlier
   - Handle the new error codes appropriately
   - Show proper authentication errors to users

This implementation ensures that:
- Only authenticated users can search for bookings
- Users can only see their own bookings
- Proper error handling for all scenarios
- Secure database queries prevent SQL injection
- Session management for user authentication

*/
?>