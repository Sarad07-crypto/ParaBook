<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

try {
    // Include database connection
    include "../connection.php";
  $transaction_uuid = $_GET['transaction_uuid'] ?? '';
    $total_amount = $_GET['total_amount'] ?? '';
    $transaction_code = $_GET['transaction_code'] ?? '';
    
    if (empty($transaction_uuid) || empty($total_amount) || empty($transaction_code)) {
        throw new Exception('Invalid payment response');
    }

    // Get temporary booking data
    $tempBookingStmt = $connect->prepare("SELECT * FROM temp_bookings WHERE booking_no = ?");
    $tempBookingStmt->bind_param("s", $transaction_uuid);
    $tempBookingStmt->execute();
    $tempBookingResult = $tempBookingStmt->get_result();

    if ($tempBookingResult->num_rows === 0) {
        throw new Exception('Booking not found');
    }

    $tempBooking = $tempBookingResult->fetch_assoc();
    $tempBookingStmt->close();

    // Start transaction
    $connect->begin_transaction();

    // Insert confirmed booking (simplified)
    $insertBooking = $connect->prepare("
        INSERT INTO bookings (
            booking_no, user_id, date, pickup, flight_type, 
            total_amount, payment_status, payment_method, 
            transaction_id, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 'completed', 'esewa', ?, NOW())
    ");

    $insertBooking->bind_param(
        "sisssds", 
        $tempBooking['booking_no'],
        $tempBooking['user_id'],
        $tempBooking['date'],
        $tempBooking['pickup'],
        $tempBooking['flight_type_name'],
        $tempBooking['total_amount'],
        $transaction_code
    );

    if (!$insertBooking->execute()) {
        throw new Exception('Failed to create booking');
    }

    $bookingId = $connect->insert_id;
    $insertBooking->close();

    // Delete temporary booking
    $deleteTempStmt = $connect->prepare("DELETE FROM temp_bookings WHERE booking_no = ?");
    $deleteTempStmt->bind_param("s", $tempBooking['booking_no']);
    $deleteTempStmt->execute();
    $deleteTempStmt->close();

    // Commit transaction
    $connect->commit();

    // Clear session
    session_unset();

    // Simple success message
    echo "<h1>Payment Successful!</h1>";
    echo "<p>Booking Number: " . htmlspecialchars($tempBooking['booking_no']) . "</p>";
    echo "<p>Booking ID: $bookingId</p>";
    echo "<p>Transaction Code: " . htmlspecialchars($transaction_code) . "</p>";

} catch (Exception $e) {
    if (isset($connect)) {
        $connect->rollback();
    }
    
    echo "<h1>Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    error_log("eSewa error: " . $e->getMessage());
}

if (isset($connect)) {
    $connect->close();
}
?>