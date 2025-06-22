<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    include "../connection.php"; // Adjusted path - check if this is correct

    // Ensure userid is properly typed
    $userid = (int)$_SESSION['user_id'];

    // Get form data with proper sanitization
    $mainName = trim($_POST['mainName'] ?? '');
    $mainEmail = trim($_POST['mainEmail'] ?? '');
    $mainPhone = trim($_POST['mainPhone'] ?? '');
    $mainNationality = trim($_POST['mainNationality'] ?? '');
    $mainDate = trim($_POST['mainDate'] ?? '');
    $mainPickup = trim($_POST['mainPickup'] ?? '');
    $mainFlightType = trim($_POST['mainFlightType'] ?? '');
    $mainWeight = trim($_POST['mainWeight'] ?? '');
    $mainAge = trim($_POST['mainAge'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $mainNotes = trim($_POST['mainNotes'] ?? '');

    // Validate required fields
    if (empty($mainName) || empty($mainEmail) || empty($mainPhone) || empty($mainNationality) || 
        empty($mainDate) || empty($mainPickup) || empty($mainFlightType) || empty($mainAge) || empty($gender)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }

    // Convert values to proper types and ensure they can be passed by reference
    $weightValue = !empty($mainWeight) ? (float)$mainWeight : 0.0;
    $ageValue = (int)$mainAge;
    $flightTypeId = (int)$mainFlightType;

    // Validate flight type ID is numeric
    if ($flightTypeId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid flight type selected']);
        exit;
    }

    // Get flight type name from database
    $flightTypeStmt = $connect->prepare("SELECT flight_type_name FROM service_flight_types WHERE id = ?");
    if (!$flightTypeStmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connect->error]);
        exit;
    }

    $flightTypeStmt->bind_param("i", $flightTypeId);
    $flightTypeStmt->execute();
    $flightTypeResult = $flightTypeStmt->get_result();

    if ($flightTypeResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid flight type selected']);
        exit;
    }

    $flightTypeData = $flightTypeResult->fetch_assoc();
    $flightTypeName = $flightTypeData['flight_type_name'];
    $flightTypeStmt->close();

    // Generate unique booking number
    $bookingNo = 'BK-' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);

    // Check if booking number already exists (very unlikely but good practice)
    $checkBookingNo = $connect->prepare("SELECT booking_id FROM bookings WHERE booking_no = ?");
    if (!$checkBookingNo) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connect->error]);
        exit;
    }
    
    $checkBookingNo->bind_param("s", $bookingNo);
    $checkBookingNo->execute();
    
    if ($checkBookingNo->get_result()->num_rows > 0) {
        // Generate new booking number if duplicate found
        $bookingNo = 'BK-' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
    }
    $checkBookingNo->close();

    // Convert weight to proper format - use 0 instead of null if empty
    // (Already converted above)

    // Insert booking into database
    $insertBooking = $connect->prepare("INSERT INTO bookings (booking_no, user_id, date, pickup, flight_type, weight, age, medical_condition) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$insertBooking) {
        echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $connect->error]);
        exit;
    }

    // Bind parameters: s=string, i=integer, d=double/decimal
    $insertBooking->bind_param("sisssdis", $bookingNo, $userid, $mainDate, $mainPickup, $flightTypeName, $weightValue, $ageValue, $mainNotes);

    if ($insertBooking->execute()) {
        $bookingId = $connect->insert_id;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Booking confirmed successfully!',
            'booking_no' => $bookingNo,
            'booking_id' => $bookingId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create booking: ' . $insertBooking->error]);
    }

    $insertBooking->close();

} catch (Exception $e) {
    // Catch any unexpected errors
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} finally {
    // Close database connection if it exists
    if (isset($connect) && $connect) {
        $connect->close();
    }
}
?>