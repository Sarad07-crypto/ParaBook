<?php
session_start();
include "../connection.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get and validate input data
$booking_no = isset($_POST['booking_no']) ? trim($_POST['booking_no']) : '';
$mainName = isset($_POST['mainName']) ? trim($_POST['mainName']) : '';
$mainEmail = isset($_POST['mainEmail']) ? trim($_POST['mainEmail']) : '';
$mainPhone = isset($_POST['mainPhone']) ? trim($_POST['mainPhone']) : '';
$mainNationality = isset($_POST['mainNationality']) ? trim($_POST['mainNationality']) : '';
$mainDate = isset($_POST['mainDate']) ? trim($_POST['mainDate']) : '';
$mainPickup = isset($_POST['mainPickup']) ? trim($_POST['mainPickup']) : '';
$mainFlightType = isset($_POST['mainFlightType']) ? (int)$_POST['mainFlightType'] : 0;
$mainWeight = isset($_POST['mainWeight']) && $_POST['mainWeight'] !== '' ? (float)$_POST['mainWeight'] : null;
$mainAge = isset($_POST['mainAge']) ? (int)$_POST['mainAge'] : 0;
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
$mainNotes = isset($_POST['mainNotes']) ? trim($_POST['mainNotes']) : '';

// Basic validation
if (empty($booking_no)) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking number']);
    exit();
}

if (empty($mainName) || empty($mainEmail) || empty($mainPhone) || empty($mainNationality) || 
    empty($mainDate) || empty($mainPickup) || !$mainFlightType || !$mainAge || empty($gender)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit();
}

// Validate email format
if (!filter_var($mainEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $mainDate)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit();
}

// Validate age and weight ranges
if ($mainAge < 10 || $mainAge > 80) {
    echo json_encode(['success' => false, 'message' => 'Age must be between 10 and 80 years']);
    exit();
}

if ($mainWeight !== null && ($mainWeight < 30 || $mainWeight > 120)) {
    echo json_encode(['success' => false, 'message' => 'Weight must be between 30kg and 120kg']);
    exit();
}

// Validate gender
$valid_genders = ['Male', 'Female', 'Other'];
if (!in_array($gender, $valid_genders)) {
    echo json_encode(['success' => false, 'message' => 'Invalid gender selection']);
    exit();
}

try {
    // Start transaction
    $connect->autocommit(FALSE);
    
    // First verify booking ownership
    $verifyStmt = $connect->prepare("SELECT booking_id FROM bookings WHERE booking_no = ? AND user_id = ?");
    if (!$verifyStmt) {
        throw new Exception("Prepare failed: " . $connect->error);
    }
    $verifyStmt->bind_param("si", $booking_no, $_SESSION['user_id']);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        throw new Exception('Booking not found or you are not authorized to edit it');
    }
    
    // Get the booking_id for internal use
    $bookingData = $verifyResult->fetch_assoc();
    $booking_id = $bookingData['booking_id'];
    
    // Validate flight type exists and get flight type name
    $flightTypeStmt = $connect->prepare("SELECT id, flight_type_name, price FROM service_flight_types WHERE id = ?");
    if (!$flightTypeStmt) {
        throw new Exception("Prepare failed: " . $connect->error);
    }
    $flightTypeStmt->bind_param("i", $mainFlightType);
    $flightTypeStmt->execute();
    $flightTypeResult = $flightTypeStmt->get_result()->fetch_assoc();
    
    if (!$flightTypeResult) {
        throw new Exception('Invalid flight type selected');
    }
    
    $flightTypeName = $flightTypeResult['flight_type_name'];
    
    // Update user info (users_info table)
    $nameParts = explode(' ', $mainName, 2);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
    
    $updateUserInfoStmt = $connect->prepare("
        UPDATE users_info 
        SET firstName = ?, lastName = ?, gender = ?, contact = ?, country = ?
        WHERE user_id = ?
    ");
    if (!$updateUserInfoStmt) {
        throw new Exception("Prepare failed: " . $connect->error);
    }
    $updateUserInfoStmt->bind_param("sssssi", $firstName, $lastName, $gender, $mainPhone, $mainNationality, $_SESSION['user_id']);
    
    if (!$updateUserInfoStmt->execute()) {
        throw new Exception("Failed to update user info: " . $updateUserInfoStmt->error);
    }
    
    // Update user email (users table)
    $updateEmailStmt = $connect->prepare("UPDATE users SET email = ? WHERE id = ?");
    if (!$updateEmailStmt) {
        throw new Exception("Prepare failed: " . $connect->error);
    }
    $updateEmailStmt->bind_param("si", $mainEmail, $_SESSION['user_id']);
    
    if (!$updateEmailStmt->execute()) {
        throw new Exception("Failed to update email: " . $updateEmailStmt->error);
    }
    
    // Update booking details - Handle NULL weight properly
    if ($mainWeight === null) {
        // When weight is NULL, use a query that sets it to NULL explicitly
        $updateBookingStmt = $connect->prepare("
            UPDATE bookings 
            SET date = ?, 
                pickup = ?, 
                flight_type = ?, 
                weight = NULL, 
                age = ?, 
                medical_condition = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE booking_no = ? AND user_id = ?
        ");
        if (!$updateBookingStmt) {
            throw new Exception("Prepare failed: " . $connect->error);
        }
        // Parameters: date(s), pickup(s), flight_type(s), age(i), medical_condition(s), booking_no(s), user_id(i)
        $updateBookingStmt->bind_param("ssisssi", $mainDate, $mainPickup, $flightTypeName, $mainAge, $mainNotes, $booking_no, $_SESSION['user_id']);
    } else {
        // When weight has a value
        $updateBookingStmt = $connect->prepare("
            UPDATE bookings 
            SET date = ?, 
                pickup = ?, 
                flight_type = ?, 
                weight = ?, 
                age = ?, 
                medical_condition = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE booking_no = ? AND user_id = ?
        ");
        if (!$updateBookingStmt) {
            throw new Exception("Prepare failed: " . $connect->error);
        }
        // Parameters: date(s), pickup(s), flight_type(s), weight(d), age(i), medical_condition(s), booking_no(s), user_id(i)
        $updateBookingStmt->bind_param("sssdissi", $mainDate, $mainPickup, $flightTypeName, $mainWeight, $mainAge, $mainNotes, $booking_no, $_SESSION['user_id']);
    }
    
    if (!$updateBookingStmt->execute()) {
        throw new Exception("Failed to update booking: " . $updateBookingStmt->error);
    }
    
    // Check if any rows were affected
    if ($updateBookingStmt->affected_rows === 0) {
        throw new Exception('No changes were made to the booking');
    }
    
    // Commit transaction
    $connect->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Booking updated successfully!',
        'booking_no' => $booking_no,
        'price' => $flightTypeResult['price']
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $connect->rollback();
    error_log('Error in updateBooking.php: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    // Restore autocommit
    $connect->autocommit(TRUE);
    
    // Close prepared statements if they exist
    if (isset($verifyStmt)) $verifyStmt->close();
    if (isset($flightTypeStmt)) $flightTypeStmt->close();
    if (isset($updateUserInfoStmt)) $updateUserInfoStmt->close();
    if (isset($updateEmailStmt)) $updateEmailStmt->close();
    if (isset($updateBookingStmt)) $updateBookingStmt->close();
}
?>