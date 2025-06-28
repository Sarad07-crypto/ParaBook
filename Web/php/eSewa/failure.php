<?php
// File: Web/php/esewa/failure.php
session_start();

try {
    include "../connection.php";

    $transaction_uuid = $_GET['transaction_uuid'] ?? '';
    
    if (!empty($transaction_uuid)) {
        // Mark temporary booking as expired
        $updateTempStmt = $connect->prepare("
            UPDATE temp_bookings SET payment_status = 'expired' 
            WHERE booking_no = ? AND payment_status = 'pending'
        ");
        
        if ($updateTempStmt) {
            $updateTempStmt->bind_param("s", $transaction_uuid);
            $updateTempStmt->execute();
            $updateTempStmt->close();
        }
    }

    // Redirect to failure page
    $errorMessage = urlencode("Payment was cancelled or failed. Please try again.");
    header("Location: /booking-error?message=$errorMessage");
    exit;

} catch (Exception $e) {
    error_log("eSewa failure handler error: " . $e->getMessage());
    $errorMessage = urlencode("An error occurred while processing your request.");
    header("Location: /booking-error?message=$errorMessage");
    exit;
} finally {
    if (isset($connect) && $connect) {
        $connect->close();
    }
}
?>