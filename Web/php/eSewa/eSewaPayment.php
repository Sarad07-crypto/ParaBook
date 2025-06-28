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

    .payment-btn:disabled {
        background: #6c757d;
        cursor: not-allowed;
    }

    .loading {
        text-align: center;
        color: #666;
        margin-top: 20px;
    }

    .error {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .debug-info {
        background: #d1ecf1;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        font-size: 12px;
        font-family: monospace;
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
        // Validation
        $errors = [];
        $required_fields = [
            'total_amount' => 'Total Amount',
            'amount' => 'Amount',
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
        if(!empty($esewaData['total_amount']) && (!is_numeric($esewaData['total_amount']) || $esewaData['total_amount'] <= 0)) {
            $errors[] = "Total amount must be a positive number";
        }
        if(!empty($esewaData['amount']) && (!is_numeric($esewaData['amount']) || $esewaData['amount'] <= 0)) {
            $errors[] = "Amount must be a positive number";
        }

        // Set default values for optional fields
        $esewaData['tax_amount'] = $esewaData['tax_amount'] ?? 0;
        $esewaData['product_service_charge'] = $esewaData['product_service_charge'] ?? 0;
        $esewaData['product_delivery_charge'] = $esewaData['product_delivery_charge'] ?? 0;

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

        <?php
        // eSewa Configuration - CRITICAL: These must match your eSewa merchant account
        $product_code = "EPAYTEST"; // Replace with your actual product service code
        $merchant_id = $esewaData['merchant_id'] ?? ""; // Your eSewa merchant ID
        
        // IMPORTANT: Replace with your actual secret key from eSewa merchant dashboard
        $secret_key = "8gBm/:&EnhH.1/q"; // This MUST be your real secret key
        
        // Format amounts properly - eSewa requires specific decimal format
        $total_amount = number_format((float)$esewaData['total_amount'], 2, '.', '');
        $amount = number_format((float)$esewaData['amount'], 2, '.', '');
        $tax_amount = number_format((float)$esewaData['tax_amount'], 2, '.', '');
        $service_charge = number_format((float)$esewaData['product_service_charge'], 2, '.', '');
        $delivery_charge = number_format((float)$esewaData['product_delivery_charge'], 2, '.', '');
        
        // Create the data string for signature - ORDER IS CRITICAL
        $data_to_sign = "total_amount={$total_amount},transaction_uuid={$esewaData['transaction_uuid']},product_code={$product_code}";
        
        // Generate HMAC-SHA256 signature
        $signature = base64_encode(hash_hmac('sha256', $data_to_sign, $secret_key, true));
        ?>

        <!-- Debug Information (REMOVE IN PRODUCTION) -->
        <!-- <div class="debug-info">
            <strong>Debug Information:</strong><br>
            <strong>Merchant ID:</strong> <?php echo htmlspecialchars($merchant_id); ?><br>
            <strong>Product Code:</strong> <?php echo htmlspecialchars($product_code); ?><br>
            <strong>Transaction UUID:</strong> <?php echo htmlspecialchars($esewaData['transaction_uuid']); ?><br>
            <strong>Total Amount:</strong> <?php echo htmlspecialchars($total_amount); ?><br>
            <strong>Amount:</strong> <?php echo htmlspecialchars($amount); ?><br>
            <strong>Tax Amount:</strong> <?php echo htmlspecialchars($tax_amount); ?><br>
            <strong>Service Charge:</strong> <?php echo htmlspecialchars($service_charge); ?><br>
            <strong>Delivery Charge:</strong> <?php echo htmlspecialchars($delivery_charge); ?><br>
            <strong>Success URL:</strong> <?php echo htmlspecialchars($esewaData['success_url']); ?><br>
            <strong>Failure URL:</strong> <?php echo htmlspecialchars($esewaData['failure_url']); ?><br>
            <strong>Data to Sign:</strong> <?php echo htmlspecialchars($data_to_sign); ?><br>
            <strong>Generated Signature:</strong> <?php echo htmlspecialchars($signature); ?><br>
        </div> -->

        <p>Click the button below to proceed to eSewa payment gateway:</p>

        <!-- eSewa Payment Form - Using correct API endpoint and parameters -->
        <form method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" id="esewaForm">
            <!-- Core required fields -->
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
            <input type="hidden" name="transaction_uuid" value="<?php echo htmlspecialchars($esewaData['transaction_uuid']); ?>">
            <input type="hidden" name="product_code" value="<?php echo $product_code; ?>">
            <input type="hidden" name="tax_amount" value="<?php echo $tax_amount; ?>">
            <input type="hidden" name="product_service_charge" value="<?php echo $service_charge; ?>">
            <input type="hidden" name="product_delivery_charge" value="<?php echo $delivery_charge; ?>">
            <input type="hidden" name="success_url" value="<?php echo htmlspecialchars($esewaData['success_url']); ?>">
            <input type="hidden" name="failure_url" value="<?php echo htmlspecialchars($esewaData['failure_url']); ?>">
            <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
            <input type="hidden" name="signature" value="<?php echo $signature; ?>">

            <?php if(empty($errors)): ?>
            <button type="submit" class="payment-btn" id="payBtn">
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
            <p><strong>Important Notes:</strong></p>
            <ul>
                <li>You will be redirected to eSewa's secure payment gateway</li>
                <li>Please complete your payment within 1 hour to confirm your booking</li>
                <li>Ensure you have sufficient balance in your eSewa account</li>
                <li>Keep your transaction ID safe for reference</li>
            </ul>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('esewaForm');
        const payBtn = document.getElementById('payBtn');
        const loading = document.getElementById('loading');
        
        if (payBtn && form) {
            // Add form submission handler
            form.addEventListener('submit', function(e) {
                // Show loading state
                loading.style.display = 'block';
                payBtn.disabled = true;
                payBtn.innerHTML = 'Processing...';
                
                // Log form submission for debugging
                console.log('Form submitting to:', form.action);
                console.log('Form method:', form.method);
                
                // Log all form data for debugging
                const formData = new FormData(form);
                console.log('Form data:');
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }
            });
        }
        
        // Timeout handler - if redirect doesn't happen in 15 seconds
        setTimeout(function() {
            if (loading && loading.style.display === 'block') {
                loading.innerHTML = '<div style="color: red;"><p><strong>Redirect Failed!</strong></p><p>If you are not redirected to eSewa, please:</p><ul><li>Check your internet connection</li><li>Verify your eSewa merchant credentials</li><li>Contact support if the problem persists</li></ul></div>';
                if (payBtn) {
                    payBtn.disabled = false;
                    payBtn.innerHTML = 'Try Again';
                }
            }
        }, 15000);
    });
    </script>
</body>

</html>

<?php
// Clear the session data after displaying the form to prevent reuse
unset($_SESSION['esewa_data']);
// Keep booking_success for potential use in success/failure pages
?>