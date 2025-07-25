<?php
// Enable error reporting for debugging
//require_once $_SERVER['DOCUMENT_ROOT'] . '/ParaBook/Web/vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('ROOT_PATH', dirname(__DIR__, 1));
//require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../../vendor/autoload.php';
  use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
session_start();

    // Get service_id from URL parameter
    $serviceId = isset($_GET['service_id']) ? intval($_GET['service_id']) :0 ;
try {
    // Include database connection
    include "../connection.php";
    echo "<h1>eSewa Payment Success Service id is $serviceId</h1>";
    
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
            booking_no, user_id, service_id, date, pickup, flight_type, weight, age, 
            medical_condition, total_amount, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'complete', NOW())
    ");

    $insertBooking->bind_param(
        "siisssdisd", 
        $tempBooking['booking_no'],      // s - string
        $tempBooking['user_id'],         // i - integer
        $tempBooking['service_id'],      // i - integer (NEW)
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
    $deleteTempStmt = $connect->prepare("DELETE  FROM temp_bookings");
 
    $deleteTempStmt->execute();
    $deleteTempStmt->close();

    // Commit transaction
    $connect->commit();
// email sent
$emailSent = sendBookingConfirmationEmail($userDetails, $tempBooking, $transaction_code, $bookingId);
   $result = sendBookingConfirmationEmail(...);
if (!$result) {
    echo "<p style='color:red'>Failed to send confirmation email. Check your PHP error log.</p>";
}

// Clear session
   // session_unset();

    // Success page with better styling
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Parabook</title>
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

    .success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .booking-details {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
    }

    .btn {
        background: #007bff;
        color: white;
        padding: 12px 24px;
        text-decoration: none;
        border-radius: 5px;
        display: inline-block;
        margin-top: 20px;
    }

    .notification-info {
        background: #e7f3ff;
        border: 1px solid #b3d9ff;
        color: #0056b3;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="success">
            <h1>✅ Payment Successful!</h1>
            <p>Your booking has been confirmed successfully.</p>
        </div>

        <div class="notification-info">
            <h4>🔔 Notifications Sent</h4>
            <p>Confirmation notifications have been sent to both you and our company team. You can view your
                notification in your notification panel.</p>
        </div>

        <div class="booking-details">
            <h3>Booking Details</h3>
            <p><strong>Booking Number:</strong> <?php echo htmlspecialchars($tempBooking['booking_no']); ?></p>
            <p><strong>Booking ID:</strong> <?php echo $bookingId; ?></p>
            <p><strong>Transaction Code:</strong> <?php echo htmlspecialchars($transaction_code); ?></p>
            <p><strong>Amount Paid:</strong> Rs. <?php echo number_format($tempBooking['total_amount'], 2); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($tempBooking['date']); ?></p>
            <p><strong>Pickup Location:</strong> <?php echo htmlspecialchars($tempBooking['pickup']); ?></p>
            <p><strong>Flight Type:</strong> <?php echo htmlspecialchars($tempBooking['flight_type_name']); ?></p>
            <p><strong>Weight:</strong>
                <?php echo $tempBooking['weight'] ? htmlspecialchars($tempBooking['weight']) . ' kg' : 'Not specified'; ?>
            </p>
            <p><strong>Age:</strong> <?php echo htmlspecialchars($tempBooking['age']); ?> years</p>
            <?php if (!empty($tempBooking['notes'])): ?>
            <p><strong>Medical/Special Notes:</strong> <?php echo htmlspecialchars($tempBooking['notes']); ?></p>
            <?php endif; ?>
            <p><strong>Payment Method:</strong> eSewa</p>
            <p><strong>Status:</strong> ✅ Confirmed</p>
        </div>

        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4>📋 Next Steps:</h4>
            <ul>
                <li>You will receive a confirmation email shortly</li>
                <li>Please save your booking number for future reference</li>
                <li>Arrive at the pickup location 15 minutes before your scheduled time</li>
                <li>Bring a valid ID for verification</li>
                <li>Check your notifications for updates on your booking</li>
                <li>Our team has been notified and will contact you if needed</li>
                <li> User email is <?php echo( $userDetails['email']) ?> </li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <p>✅ Booking confirmed! Redirecting to dashboard in <span id="countdown">5</span> seconds...</p>
        </div>

        <script>
        // Notification update - trigger a refresh of notification count
        if (typeof loadAllNotificationCounts === 'function') {
            loadAllNotificationCounts();
        }

        // Optional: Show a browser notification if supported
        if ("Notification" in window && Notification.permission === "granted") {
            new Notification("Booking Confirmed", {
                body: "Your booking #<?php echo htmlspecialchars($tempBooking['booking_no']); ?> has been confirmed successfully!",
                icon: "/path/to/your/icon.png"
            });
        }

        // Countdown redirect
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        const timer = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;
            if (countdown <= 0) {
                clearInterval(timer);
                //  $emailSent = sendBookingConfirmationEmail($userDetails, $tempBooking, $transaction_code, $bookingId);
                window.location.href = '/home';
            }
        }, 1000);
        </script>

        <a href="/dashboard" class="btn">Go to Dashboard</a>
        <a href="/serviceDescription" class="btn" style="background: #28a745;">View My Bookings</a>
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
function sendBookingConfirmationEmail($userDetails, $tempBooking, $transaction_code, $bookingId) {
    try {
        // require_once ROOT_PATH . '\Parabook\Web\php\env.php';
        $mail = new PHPMailer(true);
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'saradcr7adhikari@gmail.com'; 
        $mail->Password   = '';   
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('noreply@parabook.com', 'Parabook');
        $mail->addAddress($userDetails['email']);
        $mail->isHTML(true);
        $mail->Subject = "Booking Confirmation - Parabook #{$tempBooking['booking_no']}";

        $customerName = trim(($userDetails['firstName'] ?? '') . ' ' . ($userDetails['lastName'] ?? ''));
        $mail->Body = "
    <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Booking Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px; }
                .booking-details { background: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Booking Confirmed!</h1>
                    <p>Your paragliding adventure is confirmed</p>
                </div>
                
                <div class='content'>
                    <div class='success'>
                        <h3>✅ Payment Successful</h3>
                        <p>Dear " . htmlspecialchars($customerName) . ",</p>
                        <p>Thank you for choosing Parabook! Your booking has been confirmed and payment has been processed successfully.</p>
                    </div>
                    
                    <div class='booking-details'>
                        <h3>📋 Booking Details</h3>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Booking Number:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($tempBooking['booking_no']) . "</td></tr>
                            <tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Amount Paid:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>Rs. " . number_format($tempBooking['total_amount'], 2) . "</td></tr>
                            <tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Date:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($tempBooking['date']) . "</td></tr>
                            <tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Pickup Location:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($tempBooking['pickup']) . "</td></tr>
                            <tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Flight Type:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($tempBooking['flight_type_name']) . "</td></tr>
                        </table>
                    </div>
                    
                    <div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h4>📋 Important Instructions:</h4>
                        <ul>
                            <li>Save this email and your booking number for future reference</li>
                            <li>Arrive at the pickup location 15 minutes before your scheduled time</li>
                            <li>Bring a valid government-issued ID for verification</li>
                            <li>Wear comfortable clothing and closed-toe shoes</li>
                        </ul>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>Thank you for choosing Parabook!</p>
                    <p><small>© 2015 - 2025 ParaBook® Global Inc.</small></p>
                </div>
            </div>
        </body>
        </html>";



        $mail->send();
        error_log("Booking confirmation email sent to: " . $userDetails['email']);
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}
?>