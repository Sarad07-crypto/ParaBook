<?php
session_start();
include "../connection.php";

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Session expired. Please login again.'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$otp_expiry = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verifyy'])) {
    $input_otp = trim($_POST['otpp'] ?? '');

    if (empty($input_otp)) {
        echo "<script>alert('Please enter OTP'); window.location.href='updateverify.php';</script>";
        exit();
    }

    $stmt = $connect->prepare("SELECT * FROM users_verify WHERE user_id = ? AND is_verified = 0 AND otp = ? AND otp_expiry >= NOW()");
    $stmt->bind_param("is", $user_id, $input_otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $update = $connect->prepare("UPDATE users_verify SET is_verified = 1 WHERE user_id = ?");
        $update->bind_param("i", $user_id);
        $update->execute();

        $infoStmt = $connect->prepare("SELECT firstName, lastName, avatar FROM users_info WHERE user_id = ?");
        $infoStmt->bind_param("i", $user_id);
        $infoStmt->execute();
        $userInfo = $infoStmt->get_result()->fetch_assoc();

        $_SESSION['firstName'] = $userInfo['firstName'];
        $_SESSION['lastName'] = $userInfo['lastName'];
        $_SESSION['avatar'] = $userInfo['avatar'];
        echo "<script>alert('Email verified successfully!'); window.location.href='../home/passenger.php';</script>";
        exit();
    } else {
        echo "<script>alert('Invalid or expired OTP.'); window.location.href='updateverify.php';</script>";
        exit();
    }
}

// Get OTP expiry from DB for countdown
$stmt2 = $connect->prepare("SELECT otp_expiry FROM users_verify WHERE user_id = ? AND is_verified = 0");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
if ($row = $result2->fetch_assoc()) {
    $otp_expiry = $row['otp_expiry'];
} else {
    echo "<script>alert('No OTP found. Please login again.'); window.location.href='login.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
</head>

<body>
    <form method="post" action="updateverify.php">
        <div
            style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
            <h2 style="text-align: center;">Verify Your Email</h2>
            <p style="text-align: center;">
                <?php
                echo "OTP will expire at: " . date("h:i:s A", strtotime($otp_expiry));
                ?>
            </p>
            <p style="text-align: center;">Please enter the OTP sent to your email.</p>
            <input type="text" name="otpp" placeholder="Enter 5-DIGIT OTP" required>
            <input type="submit" name="verifyy" value="Verify"
                style="background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">

            <h2 style="text-align: center;">Countdown Timer</h2>
            <p id="timer" style="color: red; text-align: center;">Loading...</p>
        </div>
    </form>

    <script>
    const expiryTime = new Date("<?php echo $otp_expiry; ?>").getTime();

    function showTime() {
        const now = new Date().getTime();
        const left = Math.floor((expiryTime - now) / 1000);
        const timerEl = document.getElementById("timer");

        if (left > 0) {
            const mins = Math.floor(left / 60);
            const secs = left % 60;
            timerEl.innerText = mins + "m " + secs + "s left";
        } else {
            timerEl.innerText = "OTP has expired!";
            clearInterval(timerInterval);
        }
    }

    showTime();
    const timerInterval = setInterval(showTime, 1000);
    </script>
</body>

</html>