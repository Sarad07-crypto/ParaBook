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

    // Check if booking already exists (prevent duplicate processing)
    $existingBookingStmt = $connect->prepare("SELECT booking_id FROM bookings WHERE booking_no = ?");
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

    
    $userStmt = $connect->prepare("
        SELECT 
            u.email,
            ui.firstName, 
            ui.lastName, 
            ui.contact as phone 
        FROM users u 
        INNER JOIN users_info ui ON u.id = ui.user_id 
        WHERE u.id = ?
    ");
    $userStmt->bind_param("i", $tempBooking['user_id']);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userDetails = $userResult->fetch_assoc();
    $userStmt->close();

    // Start database transaction
    $connect->begin_transaction();

    // Insert confirmed booking into bookings table
    $insertBooking = $connect->prepare("
        INSERT INTO bookings (
            booking_no, user_id, date, pickup, flight_type, weight, age, 
            medical_condition, total_amount, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $insertBooking->bind_param(
        "sisssdisd", 
        $tempBooking['booking_no'],      // s - string
        $tempBooking['user_id'],         // i - integer
        $tempBooking['date'],            // s - string
        $tempBooking['pickup'],          // s - string
        $tempBooking['flight_type_name'], // s - string
        $tempBooking['weight'],          // d - decimal
        $tempBooking['age'],             // i - integer
        $tempBooking['notes'],           // s - string (medical_condition)
        $tempBooking['total_amount']     // d - decimal
    );

    if (!$insertBooking->execute()) {
        throw new Exception('Failed to create booking: ' . $insertBooking->error);
    }

    $bookingId = $connect->insert_id;
    $insertBooking->close();

    // Insert eSewa payment information
    $insertEsewaInfo = $connect->prepare("
        INSERT INTO esewainfo (
            esewaid, booking_no, amount, transaction_code, transaction_date, 
            status, pickup, flight_type
        ) VALUES (?, ?, ?, ?, NOW(), 'completed', ?, ?)
    ");

    $insertEsewaInfo->bind_param(
        "isssss", 
        $tempBooking['user_id'],
        $tempBooking['booking_no'],
        $tempBooking['total_amount'],
        $transaction_code,
        $tempBooking['pickup'],
        $tempBooking['flight_type_name']
    );

    if (!$insertEsewaInfo->execute()) {
        throw new Exception('Failed to save payment info: ' . $insertEsewaInfo->error);
    }

    $insertEsewaInfo->close();

    // CREATE NOTIFICATION FOR USER (Successful Booking)
    $userNotificationTitle = "Booking Confirmed Successfully";
    $userNotificationMessage = "Your booking #{$tempBooking['booking_no']} has been confirmed and payment of Rs. " . number_format($tempBooking['total_amount'], 2) . " has been processed successfully.";
    
    createNotification(
        $connect,
        $tempBooking['user_id'],
        'passenger',
        $userNotificationTitle,
        $userNotificationMessage,
        'booking_confirmed',
        'fas fa-plane',
        $bookingId
    );

    // CREATE NOTIFICATION FOR COMPANY (New Booking Alert)
    $customerName = ($userDetails['firstName'] ?? '') . ' ' . ($userDetails['lastName'] ?? '');
    $customerEmail = $userDetails['email'] ?? 'N/A';
    $customerPhone = $userDetails['phone'] ?? 'N/A';
    
    $companyNotificationTitle = "New Booking Received";
    $companyNotificationMessage = "New booking received from {$customerName}. Amount: Rs. " . number_format($tempBooking['total_amount'], 2);
    
    // Get all company users to notify based on your actual database schema
    $companyUsersStmt = $connect->prepare("
        SELECT u.id 
        FROM users u 
        INNER JOIN users_info ui ON u.id = ui.user_id 
        WHERE ui.acc_type = 'company'
    ");
    $companyUsersStmt->execute();
    $companyUsersResult = $companyUsersStmt->get_result();

    while ($companyUser = $companyUsersResult->fetch_assoc()) {
        createNotification(
            $connect,
            $companyUser['id'],
            'company',
            $companyNotificationTitle,
            $companyNotificationMessage,
            'new_booking',
            'fas fa-plane',
            $bookingId
        );
    }
    $companyUsersStmt->close();

    // Delete temporary booking
    $deleteTempStmt = $connect->prepare("DELETE FROM temp_bookings WHERE booking_no = ?");
    $deleteTempStmt->bind_param("s", $tempBooking['booking_no']);
    $deleteTempStmt->execute();
    $deleteTempStmt->close();

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
    body {
        font-family: Arial, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 20px;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .debug {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        font-family: monospace;
        font-size: 12px;
    }
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

/**
 * Enhanced helper function to create notifications
 */
function createNotification($connect, $recipient_id, $recipient_type, $title, $message, $type = 'general', $icon = 'fas fa-plane', $booking_id = null) {
    $stmt = $connect->prepare("
        INSERT INTO notifications (
            recipient_id, recipient_type, title, message, type, icon, booking_id, is_read, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
    ");
    
    $stmt->bind_param("isssssi", $recipient_id, $recipient_type, $title, $message, $type, $icon, $booking_id);
    
    if (!$stmt->execute()) {
        error_log('Failed to create notification: ' . $stmt->error);
        return false;
    }
    
    $stmt->close();
    return true;
}
?>