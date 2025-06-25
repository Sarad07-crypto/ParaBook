<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "connection.php";
$amount = null;
$status = '';
$transaction_code = '';
$user_id = $_SESSION['user_id'] ?? null;
// If eSewa sent the data parameter, decode and use it
if (isset($_GET['data'])) {
    $json = base64_decode($_GET['data']);
    $data = json_decode($json, true);

    // You can now access $data['total_amount'] and other fields
    $amount = $data['total_amount'] ?? $amount;
    $status = $data['status'] ?? '';
    $transaction_code = $data['transaction_code'] ?? '';
    

 if ($transaction_code && $amount && $user_id) {
        $stmt = $connect->prepare("INSERT INTO esewainfo (  user_id,amount,transaction_code,transaction_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss",  $user_id,$amount, $transaction_code);
        $stmt->execute();
        $stmt->close();

 }
} else {
    $status = '';
    $transaction_code = '';
}

?>
<h1>Payment Successful!</h1>
<p>Your transaction amount: Rs. <?php echo htmlspecialchars($amount); ?></p>
<?php if ($transaction_code): ?>
    <p>Transaction Code: <?php echo htmlspecialchars($transaction_code); ?></p>
<?php endif; ?>
<?php if ($status): ?>
    <p>Status: <?php echo htmlspecialchars($status); ?></p>
    <p><?php echo $user_id ?></p>
<?php endif; ?>