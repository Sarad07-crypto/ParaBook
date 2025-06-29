<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

try {
    // Include database connection
    include "../connection.php";
    
    // eSewa sends data as base64 encoded JSON in 'data' parameter
    $encoded_data = $_GET['data'] ?? $_POST['data'] ?? '';
    
    if (empty($encoded_data)) {
        throw new Exception('No payment data received from eSewa');
    }
    
    // Decode the base64 data
    $decoded_data = base64_decode($encoded_data);
    if ($decoded_data === false) {
        throw new Exception('Failed to decode eSewa payment data');
    }
    
    // Parse JSON data
    $payment_data = json_decode($decoded_data, true);
    if ($payment_data === null) {
        throw new Exception('Failed to parse eSewa payment data JSON');
    }
    
    // Extract payment information
    $transaction_uuid = $payment_data['transaction_uuid'] ?? '';
    $total_amount = $payment_data['total_amount'] ?? '';
    $transaction_code = $payment_data['transaction_code'] ?? '';
    $status = $payment_data['status'] ?? '';
    $product_code = $payment_data['product_code'] ?? '';
    $signature = $payment_data['signature'] ?? '';
    $signed_field_names = $payment_data['signed_field_names'] ?? '';
    
    // Debug output
    echo "<h2>Payment Data Received:</h2>";
    echo "<p><strong>Transaction UUID:</strong> " . htmlspecialchars($transaction_uuid) . "</p>";
    echo "<p><strong>Total Amount:</strong> " . htmlspecialchars($total_amount) . "</p>";
    echo "<p><strong>Transaction Code:</strong> " . htmlspecialchars($transaction_code) . "</p>";
    echo "<p><strong>Status:</strong> " . htmlspecialchars($status) . "</p>";
    echo "<p><strong>Product Code:</strong> " . htmlspecialchars($product_code) . "</p>";
    echo "<hr>";
    
    // Validate required fields
    if (empty($transaction_uuid)) {
        throw new Exception('Missing transaction UUID in payment data');
    }
    
    if (empty($total_amount)) {
        throw new Exception('Missing total amount in payment data');
    }
    
    if (empty($transaction_code)) {
        throw new Exception('Missing transaction code in payment data');
    }
    
    // Check if payment status is complete
    if (strtoupper($status) !== 'COMPLETE') {
        throw new Exception('Payment not completed. Status: ' . $status);
    }

    // Verify signature (IMPORTANT for security)
    $secret_key = "8gBm/:&EnhH.1/q"; // Your eSewa secret key
    if (!verifyEsewaSignature($payment_data, $secret_key)) {
        throw new Exception('Payment signature verification failed');
    }

    // Get temporary booking data
    $tempBookingStmt = $connect->prepare("SELECT * FROM temp_bookings WHERE booking_no = ?");
    $tempBookingStmt->bind_param("s", $transaction_uuid);
    $tempBookingStmt->execute();
    $tempBookingResult = $tempBookingStmt->get_result();

    if ($tempBookingResult->num_rows === 0) {
        throw new Exception('Booking not found for transaction UUID: ' . $transaction_uuid);
    }

    $tempBooking = $tempBookingResult->fetch_assoc();
    $tempBookingStmt->close();

    // Verify amounts match (allowing small floating point differences)
    // if (abs(floatval($tempBooking['total_amount']) - floatval($total_amount)) > 0.01) {
    //     throw new Exception('Amount mismatch. Expected: ' . $tempBooking['total_amount'] . 
    //                       ', Received: ' . $total_amount);
    // }

    // Check if booking already exists (prevent duplicate processing)
    $existingBookingStmt = $connect->prepare("SELECT user_id FROM bookings WHERE booking_no = ?");
    $existingBookingStmt->bind_param("s", $tempBooking['booking_no']);
    $existingBookingStmt->execute();
    $existingResult = $existingBookingStmt->get_result();
    
    if ($existingResult->num_rows > 0) {
        $existingBookingStmt->close();
        echo "<h1>✅ Booking Already Processed!</h1>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<p><strong>Your booking has already been confirmed.</strong></p>";
        echo "<p><strong>Booking Number:</strong> " . htmlspecialchars($tempBooking['booking_no']) . "</p>";
        echo "<p><strong>Amount:</strong> Rs. " . number_format($tempBooking['total_amount'], 2) . "</p>";
        echo "</div>";
        exit;
    }
    $existingBookingStmt->close();

    // Start database transaction
    $connect->begin_transaction();
$tempbookingno= $tempBooking['booking_no'];
    // Insert confirmed booking
    $insertBooking = $connect->prepare("
        INSERT INTO esewainfo (
           esewaid,booking_no,amount,transaction_code,transaction_date,status,pickup,flight_type
        ) VALUES (?, ?, ?, ?, NOW(),'completed', ?, ?)
    ");

    $insertBooking->bind_param(
        "isssss", 
        
        $tempBooking['user_id'],
$tempbookingno,
        
              $tempBooking['total_amount'],
               $transaction_code,
       
        $tempBooking['pickup'],
        $tempBooking['flight_type_name'],
  
       
    );

    if (!$insertBooking->execute()) {
        throw new Exception('Failed to create booking: ' . $insertBooking->error);
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

    // Success page with better styling
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Successful - Parabook</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .booking-details { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="success">
                <h1>✅ Payment Successful!</h1>
                <p>Your booking has been confirmed successfully.</p>
            </div>
            
            <div class="booking-details">
                <h3>Booking Details</h3>

                <p><strong>Booking Number:</strong> <?php echo htmlspecialchars($tempBooking['booking_no']); ?></p>
                <!-- <p><strong>Booking ID:</strong> <?php echo $bookingId; ?></p> -->
                <p><strong>Transaction Code:</strong> <?php echo htmlspecialchars($transaction_code); ?></p>
                <p><strong>Amount Paid:</strong> Rs. <?php echo number_format($tempBooking['total_amount'], 2); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($tempBooking['date']); ?></p>
                <p><strong>Pickup Location:</strong> <?php echo htmlspecialchars($tempBooking['pickup']); ?></p>
                <p><strong>Flight Type:</strong> <?php echo htmlspecialchars($tempBooking['flight_type_name']); ?></p>
                <p><strong>Payment Method:</strong> eSewa</p>
                     <p>✅ Booking confirmed! Redirecting to email confirmation in <span id="countdown">5</span> seconds...</p>
            </div>
            <script>
let countdown = 5;
const countdownElement = document.getElementById('countdown');

const timer = setInterval(function() {
    countdown--;
    countdownElement.textContent = countdown;
    
    if (countdown <= 0) {
        clearInterval(timer);
        window.location.href = 'email.php';
    }
}, 1000);
</script>
            <div style="background: #e7f3ff; padding: 15px; border-radius: 5px;">
                <h4>📋 Next Steps:</h4>
                <ul>
                    <li>You will receive a confirmation email shortly</li>
                    <li>Please save your booking number for future reference</li>
                    <li>Arrive at the pickup location 15 minutes before your scheduled time</li>
                    <li>Bring a valid ID for verification</li>
                </ul>
            </div>
            
            <a href="/dashboard" class="btn">Go to Dashboard</a>
            <a href="/bookings" class="btn" style="background: #28a745;">View My Bookings</a>
        </div>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    if (isset($connect)) {
        $connect->rollback();
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Error - Parabook</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .debug { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="error">
                <h1>❌ Payment Processing Error</h1>
                <p><strong>Error:</strong> <?php echo htmlspecialchars($e->getMessage()); ?></p>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4>🔧 What to do:</h4>
                <ul>
                    <li>If payment was deducted, please contact support immediately</li>
                    <li>Do not attempt payment again until this issue is resolved</li>
                    <li>Keep a screenshot of this page for reference</li>
                    <li>Contact support with your transaction details</li>
                </ul>
            </div>
            
            <?php if (isset($encoded_data)): ?>
            <div class="debug">
                <strong>Raw eSewa Data:</strong><br>
                <?php echo htmlspecialchars($encoded_data); ?>
            </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    
    error_log("eSewa payment error: " . $e->getMessage());
    error_log("eSewa data: " . ($encoded_data ?? 'No data'));
}

if (isset($connect)) {
    $connect->close();
}

/**
 * Verify eSewa payment signature for security
 */
function verifyEsewaSignature($payment_data, $secret_key) {
    $signed_field_names = explode(',', $payment_data['signed_field_names']);
    $data_to_sign = '';
    
    foreach ($signed_field_names as $field) {
        $field = trim($field);
        if (isset($payment_data[$field])) {
            $data_to_sign .= $field . '=' . $payment_data[$field] . ',';
        }
    }
    
    // Remove trailing comma
    $data_to_sign = rtrim($data_to_sign, ',');
    
    // Generate signature
    $generated_signature = base64_encode(hash_hmac('sha256', $data_to_sign, $secret_key, true));
    
    // Compare signatures
    return hash_equals($payment_data['signature'], $generated_signature);
}
?>