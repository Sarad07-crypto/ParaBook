<?php
// checkTempBooking.php - Helper script to verify data insertion
session_start();
header('Content-Type: application/json');

include "../connection.php";

try {
    if (!isset($_POST['booking_no'])) {
        echo json_encode(['success' => false, 'message' => 'Booking number required']);
        exit;
    }
    
    $bookingNo = trim($_POST['booking_no']);
    
    // Check if the booking exists in temp_bookings
    $stmt = $connect->prepare("SELECT temp_id, booking_no, full_name, email, phone, total_amount, payment_status, created_at, expires_at FROM temp_bookings WHERE booking_no = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connect->error]);
        exit;
    }
    
    $stmt->bind_param("s", $bookingNo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'found' => true,
            'booking' => $booking,
            'message' => 'Booking found in temp_bookings table'
        ]);
    } else {
        // Also check in permanent bookings table
        $stmt2 = $connect->prepare("SELECT booking_id, booking_no, full_name, email, phone, total_amount, payment_status, created_at FROM bookings WHERE booking_no = ?");
        if ($stmt2) {
            $stmt2->bind_param("s", $bookingNo);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            
            if ($result2->num_rows > 0) {
                $booking = $result2->fetch_assoc();
                echo json_encode([
                    'success' => true,
                    'found' => true,
                    'booking' => $booking,
                    'message' => 'Booking found in permanent bookings table'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'found' => false,
                    'message' => 'Booking not found in either table'
                ]);
            }
            $stmt2->close();
        }
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($connect) && $connect) {
        $connect->close();
    }
}
?>