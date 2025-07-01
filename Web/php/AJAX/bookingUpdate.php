<?php
    session_start();
    include "../connection.php";
    require_once 'bookingNotification.php'; // Add this line

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
    // $mainFlightType = isset($_POST['mainFlightType']) ? (int)$_POST['mainFlightType'] : 0;
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
        empty($mainDate) || empty($mainPickup) || !$mainAge || empty($gender)) {
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
    if ($mainAge < 5 || $mainAge > 80) {
        echo json_encode(['success' => false, 'message' => 'Age must be between 5 and 80 years']);
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
        // Initialize notification system
        $notificationSystem = new BookingNotificationSystem($connect);
        
        // Start transaction
        $connect->autocommit(FALSE);
        
        // First verify booking ownership and get current price
        $verifyStmt = $connect->prepare("
            SELECT b.booking_id, sft.price 
            FROM bookings b 
            LEFT JOIN service_flight_types sft ON b.flight_type = sft.flight_type_name
            WHERE b.booking_no = ? AND b.user_id = ?
        ");
        if (!$verifyStmt) {
            throw new Exception("Prepare failed: " . $connect->error);
        }
        $verifyStmt->bind_param("si", $booking_no, $_SESSION['user_id']);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        
        if ($verifyResult->num_rows === 0) {
            throw new Exception('Booking not found or you are not authorized to edit it');
        }
        
        // Get the booking_id and price for response
        $bookingData = $verifyResult->fetch_assoc();
        $booking_id = $bookingData['booking_id'];
        $currentPrice = $bookingData['price'];
        
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
        
        // Update booking details - Handle NULL weight properly, but DON'T update flight_type
        if ($mainWeight === null) {
            // When weight is NULL, use a query that sets it to NULL explicitly
            $updateBookingStmt = $connect->prepare("
                UPDATE bookings 
                SET date = ?, 
                    pickup = ?, 
                    weight = NULL, 
                    age = ?, 
                    medical_condition = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE booking_no = ? AND user_id = ?
            ");
            if (!$updateBookingStmt) {
                throw new Exception("Prepare failed: " . $connect->error);
            }
            // Parameters: date(s), pickup(s), age(i), medical_condition(s), booking_no(s), user_id(i)
            $updateBookingStmt->bind_param("ssissi", $mainDate, $mainPickup, $mainAge, $mainNotes, $booking_no, $_SESSION['user_id']);
        } else {
            // When weight has a value
            $updateBookingStmt = $connect->prepare("
                UPDATE bookings 
                SET date = ?, 
                    pickup = ?, 
                    weight = ?, 
                    age = ?, 
                    medical_condition = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE booking_no = ? AND user_id = ?
            ");
            if (!$updateBookingStmt) {
                throw new Exception("Prepare failed: " . $connect->error);
            }
            // Parameters: date(s), pickup(s), weight(d), age(i), medical_condition(s), booking_no(s), user_id(i)
            $updateBookingStmt->bind_param("ssdissi", $mainDate, $mainPickup, $mainWeight, $mainAge, $mainNotes, $booking_no, $_SESSION['user_id']);
        }
        
        if (!$updateBookingStmt->execute()) {
            throw new Exception("Failed to update booking: " . $updateBookingStmt->error);
        }
        
        // Check if any rows were affected
        if ($updateBookingStmt->affected_rows === 0) {
            throw new Exception('No changes were made to the booking');
        }
        
        // Send update notifications - Add this block
        $notificationResult = $notificationSystem->sendBookingUpdateNotifications(
            $booking_id, 
            $_SESSION['user_id'], 
            $booking_no, 
            $mainName, 
            'updated'
        );
        
        // Log notification result (optional, for debugging)
        if (!$notificationResult) {
            error_log("Failed to send booking update notifications for booking: " . $booking_no);
        }
        
        // Commit transaction
        $connect->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Booking updated successfully!',
            'booking_no' => $booking_no,
            'price' => $currentPrice
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
        if (isset($updateUserInfoStmt)) $updateUserInfoStmt->close();
        if (isset($updateEmailStmt)) $updateEmailStmt->close();
        if (isset($updateBookingStmt)) $updateBookingStmt->close();
    }
?>