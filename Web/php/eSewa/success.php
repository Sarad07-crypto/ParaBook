<?php

session_start();
require_once 'Web/php/AJAX/bookingNotification.php';

try {
    include "../connection.php";

    // Get eSewa response data
    $transaction_uuid = $_GET['transaction_uuid'] ?? '';
    $total_amount = $_GET['total_amount'] ?? '';
    $transaction_code = $_GET['transaction_code'] ?? '';
    
    if (empty($transaction_uuid) || empty($total_amount) || empty($transaction_code)) {
        throw new Exception('Invalid payment response');
    }

    // Verify payment with eSewa (optional but recommended)
    $merchantId = "EPAYTEST"; // Your merchant ID
    $secretKey = "8gBm/:&EnhH.1/q"; // Your secret key
    
    // Verify transaction status with eSewa API
    $verificationUrl = "https://uat.esewa.com.np/api/epay/transaction/status/?product_code=$merchantId&total_amount=$total_amount&transaction_uuid=$transaction_uuid";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verificationUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Payment verification failed');
    }

    $verificationResult = json_decode($response, true);
    if (!$verificationResult || $verificationResult['status'] !== 'COMPLETE') {
        throw new Exception('Payment not verified');
    }

    // Get temporary booking data
    $tempBookingStmt = $connect->prepare("
        SELECT * FROM temp_bookings 
        WHERE booking_no = ? AND payment_status = 'pending' AND expires_at > NOW()
    ");
    
    if (!$tempBookingStmt) {
        throw new Exception('Database error: ' . $connect->error);
    }

    $tempBookingStmt->bind_param("s", $transaction_uuid);
    $tempBookingStmt->execute();
    $tempBookingResult = $tempBookingStmt->get_result();

    if ($tempBookingResult->num_rows === 0) {
        throw new Exception('Booking not found or expired');
    }

    $tempBooking = $tempBookingResult->fetch_assoc();
    $tempBookingStmt->close();
$droptempBookingStmt =     // Start transaction
    $connect->begin_transaction();

    try {
        // Insert confirmed booking
        $insertBooking = $connect->prepare("
            INSERT INTO bookings (
                booking_no, user_id, date, pickup, flight_type, weight, age, 
                medical_condition, total_amount, payment_status, payment_method, 
                transaction_id, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', 'esewa', ?, NOW())
        ");

        if (!$insertBooking) {
            throw new Exception('Database prepare error: ' . $connect->error);
        }

        $insertBooking->bind_param(
            "sisssdisds", 
            $tempBooking['booking_no'],
            $tempBooking['user_id'],
            $tempBooking['date'],
            $tempBooking['pickup'],
            $tempBooking['flight_type_name'],
            $tempBooking['weight'],
            $tempBooking['age'],
            $tempBooking['notes'],
            $tempBooking['total_amount'],
            $transaction_code
        );

        if (!$insertBooking->execute()) {
            throw new Exception('Failed to create booking: ' . $insertBooking->error);
        }

        $bookingId = $connect->insert_id;
        $insertBooking->close();

        // Update temporary booking status
        $updateTempStmt = $connect->prepare("
            UPDATE temp_bookings SET payment_status = 'paid' WHERE booking_no = ?
        ");
        $updateTempStmt->bind_param("s", $transaction_uuid);
        $updateTempStmt->execute();
        $updateTempStmt->close();

        // Send notifications
        $notificationSystem = new BookingNotificationSystem($connect);
        $notificationsSent = $notificationSystem->sendBookingNotifications(
            $bookingId, 
            $tempBooking['user_id'], 
            $tempBooking['booking_no'], 
            $tempBooking['full_name']
        );

        // Commit transaction
        $connect->commit();
        $droptempBookingStmt = $connect->prepare("DELETE FROM temp_bookings WHERE booking_no = ?");
        if (!$droptempBookingStmt) {
            throw new Exception('Database prepare error: ' . $connect->error);
        }
        $droptempBookingStmt->bind_param("s", $tempBooking['booking_no']);
        // Delete temporary booking
        $droptempBookingStmt->execute();
        if (!$droptempBookingStmt->execute()) {
            throw new Exception('Failed to delete temporary booking: ' . $droptempBookingStmt->error);
        }
        $droptempBookingStmt->close();

        // Redirect to success page with booking details
        $successMessage = urlencode("Payment successful! Your booking has been confirmed. Booking Number: " . $tempBooking['booking_no']);
        header("Location: /booking-success?message=$successMessage&booking_no=" . $tempBooking['booking_no']);
        exit;

    } catch (Exception $e) {
        $connect->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("eSewa success handler error: " . $e->getMessage());
    $errorMessage = urlencode("Payment processing failed: " . $e->getMessage());
    header("Location: /booking-error?message=$errorMessage");
    exit;
} finally {
    if (isset($connect) && $connect) {
        $connect->close();
    }
}
?>