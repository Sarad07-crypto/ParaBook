<?php
session_start();

if (!isset($_SESSION['selected_flight_price'])) {
    $_SESSION['selected_flight_price'] = 5000;;
}
$userid= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$total_amount = $_SESSION['selected_flight_price'] ?? 0; // fallback to 0 if not set
$amount = $total_amount;
$tax_amount = 0;
$product_service_charge = 0;
$product_delivery_charge = 0;
$transaction_uuid = uniqid('txn_');
$product_code = "EPAYTEST";

// Concatenate the signed fields in order, separated by commas
$data = "total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
$secret_key = "8gBm/:&EnhH.1/q";
$signature = base64_encode(hash_hmac('sha256', $data, $secret_key, true));
?>
<h1>eSewa Payment Form</h1>

<form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
    <input type="text" name="amount" value="<?php echo $amount; ?>" required>
    <input type="text" name="tax_amount" value="<?php echo $tax_amount; ?>" required>
    <input type="text" name="total_amount" value="<?php echo $total_amount; ?>" required>
    <input type="text" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>" required>
    <input type="text" name="product_code" value="<?php echo $product_code; ?>" required>
    <input type="text" name="product_service_charge" value="<?php echo $product_service_charge; ?>" required>
    <input type="text" name="product_delivery_charge" value="<?php echo $product_delivery_charge; ?>" required>
    <input type="text" name="success_url" value="http://localhost:8080/Web/php/esewasuccess.php" required>
    <input type="text" name="failure_url" value="https://developer.esewa.com.np/failure" required>
    <input type="text" name="signed_field_names" value="total_amount,transaction_uuid,product_code" required>
    <input type="text" name="signature" value="<?php echo $signature; ?>" required>
    <input value="Submit" type="submit">
</form>