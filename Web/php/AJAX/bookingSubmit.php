<?php
session_start();
header('Content-Type: application/json');
$_SESSION['selected_flight_price'] = $_POST['selectedFlightPrice'] ?? null;
require_once 'bookingNotification.php';

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

    include "../connection.php";

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

    // Convert values to proper types
    $weightValue = !empty($mainWeight) ? (float)$mainWeight : null;
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
    $flightTypeprice= $flightTypeData['price'] ; // Default to 0 if not set
    $flightTypeStmt->close();
  $_SESSION['selected_flight_type'] = $flightTypeId;
$_SESSION['selected_flight_type_name'] = $flightTypeName;
$_SESSION['selected_flight_price'] = $flightTypeprice;

    // Start transaction
    $connect->begin_transaction();

    try {
        // Generate unique booking number
        do {
            $bookingNo = 'BK-' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
            
            // Check if booking number already exists
            $checkBookingNo = $connect->prepare("SELECT booking_id FROM bookings WHERE booking_no = ?");
            if (!$checkBookingNo) {
                throw new Exception('Database error: ' . $connect->error);
            }
 
            $checkBookingNo->bind_param("s", $bookingNo);
            $checkBookingNo->execute();
            $exists = $checkBookingNo->get_result()->num_rows > 0;
            $checkBookingNo->close();
        } while ($exists);

        // Insert booking into database
        $insertBooking = $connect->prepare("
            INSERT INTO bookings (
                booking_no, user_id, date, pickup, flight_type, weight, age, medical_condition
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$insertBooking) {
            throw new Exception('Database prepare error: ' . $connect->error);
        }

        // Bind parameters
        $insertBooking->bind_param(
            "sisssdis", 
            $bookingNo,          // booking_no (string)
            $userid,             // user_id (integer) 
            $mainDate,           // date (string)
            $mainPickup,         // pickup (string)
            $flightTypeName,     // flight_type (string)
            $weightValue,        // weight (decimal - can be null)
            $ageValue,           // age (integer)
            $mainNotes           // medical_condition (text)
        );
        
        if (!$insertBooking->execute()) {
            throw new Exception('Failed to create booking: ' . $insertBooking->error);
        }

        $bookingId = $connect->insert_id;
        $insertBooking->close();

        // Initialize notification system with MySQLi connection
        $notificationSystem = new BookingNotificationSystem($connect);

        // Send notifications with customer name
        $notificationsSent = $notificationSystem->sendBookingNotifications($bookingId, $userid, $bookingNo, $mainName);

        if ($notificationsSent) {
            // Commit transaction if everything is successful
            $connect->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Booking confirmed successfully! Notifications sent.',
                'booking_no' => $bookingNo,
                'booking_id' => $bookingId
            ]);
        } else {
            // Still commit the booking even if notifications fail
            $connect->commit();
            echo json_encode([
                'success' => true, 
                'message' => 'Booking confirmed successfully! (Notifications may have failed)',
                'booking_no' => $bookingNo,
                'booking_id' => $bookingId
            ]);
        }

    } catch (Exception $e) {
        // Rollback transaction on any error
        $connect->rollback();
        throw $e; // Re-throw to be caught by outer try-catch
    }

} catch (Exception $e) {
    // Log the error for debugging
    error_log("Booking submission error: " . $e->getMessage());
    
    // Return user-friendly error message
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred. Please try again later.',
        'debug_error' => $e->getMessage() // Remove this line in production
    ]);
} finally {
    // Close database connections
    if (isset($connect) && $connect) {
        $connect->close();
    }
}
?>