<?php
session_start();

// Check if user has valid eSewa data in session
if (!isset($_SESSION['esewa_data']) || !isset($_SESSION['booking_success'])) {
    $_SESSION['booking_error'] = 'Invalid payment session. Please try booking again.';
    header('Location: /booking');
    exit;
}

$esewaData = $_SESSION['esewa_data'];
$bookingData = $_SESSION['booking_success'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eSewa Payment - Parabook</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 20px;
    }

    .payment-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .booking-summary {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .payment-btn {
        background: #28a745;
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
    }

    .payment-btn:hover {
        background: #218838;
    }

    .loading {
        text-align: center;
        color: #666;
        margin-top: 20px;
    }
    </style>
</head>

<body>
    <div class="payment-container">
        <h2>Complete Your Payment</h2>

        <div class="booking-summary">
            <h3>Booking Summary</h3>
            <p><strong>Booking Number:</strong> <?php echo htmlspecialchars($bookingData['booking_no']); ?></p>
            <p><strong>Flight Type:</strong> <?php echo htmlspecialchars($bookingData['flight_type']); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($bookingData['date']); ?></p>
            <p><strong>Pickup:</strong> <?php echo htmlspecialchars($bookingData['pickup']); ?></p>
            <p><strong>Total Amount:</strong> Rs. <?php echo number_format($bookingData['total_amount'], 2); ?></p>
        </div>

        <?php
        // Validation and debugging
        $errors = [];
        $required_fields = [
            'total_amount' => 'Total Amount',
            'amount' => 'Amount',
            'merchant_id' => 'Merchant ID',
            'transaction_uuid' => 'Transaction ID',
            'success_url' => 'Success URL',
            'failure_url' => 'Failure URL'
        ];

        foreach($required_fields as $field => $label) {
            if(empty($esewaData[$field])) {
                $errors[] = "Missing required field: $label";
            }
        }

        // Validate URLs
        if(!empty($esewaData['success_url']) && !filter_var($esewaData['success_url'], FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid Success URL format";
        }
        if(!empty($esewaData['failure_url']) && !filter_var($esewaData['failure_url'], FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid Failure URL format";
        }

        // Validate amounts
        if(!empty($esewaData['total_amount']) && !is_numeric($esewaData['total_amount'])) {
            $errors[] = "Total amount must be numeric";
        }

        if(!empty($errors)): ?>
        <div class="error">
            <h4>Configuration Errors:</h4>
            <ul>
                <?php foreach($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Debug Information (remove in production) -->
        <div class="debug-info">
            <strong>Debug Info:</strong><br>
            Merchant ID: <?php echo htmlspecialchars($esewaData['merchant_id'] ?? 'NOT SET'); ?><br>
            Transaction UUID: <?php echo htmlspecialchars($esewaData['transaction_uuid'] ?? 'NOT SET'); ?><br>
            Total Amount: <?php echo htmlspecialchars($esewaData['total_amount'] ?? 'NOT SET'); ?><br>
            Success URL: <?php echo htmlspecialchars($esewaData['success_url'] ?? 'NOT SET'); ?><br>
            Failure URL: <?php echo htmlspecialchars($esewaData['failure_url'] ?? 'NOT SET'); ?>
        </div>

        <p>Click the button below to proceed to eSewa payment gateway:</p>
        <?php
        $total_amount = $esewaData['total_amount'] ?? 0;
        $transaction_uuid = $esewaData['transaction_uuid'] ?? uniqid('txn_');
        $product_code = "EPAYTEST";
        $signed_field_names = "tAmt,amt,txAmt,psc,pdc,scd,pid,su,fu";
        $data = "tAmt={$esewaData['total_amount']},amt={$esewaData['amount']},txAmt={$esewaData['tax_amount']},psc={$esewaData['product_service_charge']},pdc={$esewaData['product_delivery_charge']},scd=$product_code,pid={$esewaData['transaction_uuid']},su={$esewaData['success_url']},fu={$esewaData['failure_url']}";
        $secret_key = "8gBm/:&EnhH.1/q";
        $signature = base64_encode(hash_hmac('sha256', $data, $secret_key, true));
        ?>
        <!-- eSewa Payment Form -->
        <form method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" id="esewaForm">
            <input type="hidden" name="tAmt" value="<?php echo htmlspecialchars($esewaData['total_amount']); ?>">
            <input type="hidden" name="amt" value="<?php echo htmlspecialchars($esewaData['amount']); ?>">
            <input type="hidden" name="txAmt" value="<?php echo htmlspecialchars($esewaData['tax_amount']); ?>">
            <input type="hidden" name="psc"
                value="<?php echo htmlspecialchars($esewaData['product_service_charge']); ?>">
            <input type="hidden" name="pdc"
                value="<?php echo htmlspecialchars($esewaData['product_delivery_charge']); ?>">
            <input type="hidden" name="scd" value="<?php echo $product_code; ?>">
            <input type="hidden" name="pid" value="<?php echo htmlspecialchars($esewaData['transaction_uuid']); ?>">
            <input type="hidden" name="su" value="<?php echo htmlspecialchars($esewaData['success_url']); ?>">
            <input type="hidden" name="fu" value="<?php echo htmlspecialchars($esewaData['failure_url']); ?>">
            <input type="hidden" name="signed_field_names" value="<?php echo $signed_field_names; ?>">
            <input type="hidden" name="signature" value="<?php echo $signature; ?>">

            <?php if(empty($errors)): ?>
            <button type="submit" class="payment-btn" onclick="showLoading()">
                Pay with eSewa - Rs. <?php echo number_format($bookingData['total_amount'], 2); ?>
            </button>
            <?php else: ?>
            <button type="button" class="payment-btn" disabled>
                Fix Configuration Errors First
            </button>
            <?php endif; ?>
        </form>

        <div id="loading" class="loading" style="display: none;">
            <p>Redirecting to eSewa payment gateway...</p>
            <p>Please wait, do not close this window.</p>
        </div>

        <div style="margin-top: 20px; font-size: 14px; color: #666;">
            <p><strong>Note:</strong> You will be redirected to eSewa's secure payment gateway. Please complete your
                payment within 1 hour to confirm your booking.</p>
        </div>
    </div>

    <script>
    function showLoading() {
        document.getElementById('loading').style.display = 'block';
        document.querySelector('.payment-btn').disabled = true;
        document.querySelector('.payment-btn').innerHTML = 'Processing...';
    }

    // Auto-submit after 3 seconds for better user experience
    setTimeout(function() {
        if (confirm(
                'Ready to proceed to eSewa payment? Click OK to continue or Cancel to review your booking.')) {
            showLoading();
            document.getElementById('esewaForm').submit();
        }
    }, 2000);
    </script>
</body>

</html>

<?php
// Clear the session data after displaying the form to prevent reuse
unset($_SESSION['esewa_data']);
// Keep booking_success for potential use in success/failure pages
?>