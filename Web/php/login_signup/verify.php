<?php
include '../connection.php';

$duration = 60;
$token = '';
$expiry_time = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
} elseif (isset($_POST['token'])) {
    $token = $_POST['token'];
}

if (isset($_POST['verify'])) {
    if (!empty($token)) {
        $otp_input = $_POST['otp'];

        $stmt = $connect->prepare("SELECT user_id FROM users_verify WHERE verify_token = ? AND is_verified = 0 AND otp_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $verify_row = $result->fetch_assoc();
            $user_id = $verify_row['user_id'];

            $stmt2 = $connect->prepare("SELECT * FROM users_verify WHERE user_id = ? AND otp = ? AND is_verified = 0");
            $stmt2->bind_param("is", $user_id, $otp_input);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            if ($result2->num_rows > 0) {

                $update = $connect->prepare("UPDATE users_verify SET is_verified = 1 WHERE user_id = ?");
                $update->bind_param("i", $user_id);
                $update->execute();

                echo "<script>alert('Email verified successfully!');</script>";
                echo "<script>window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('Invalid OTP!'); window.location.href='verify.php?token=$token';</script>";
            }
        } else {
            echo "<script>alert('Invalid or expired token!'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Missing verification token!'); window.location.href='login.php';</script>";
    }
}
if (!empty($token)) {
    $stmt = $connect->prepare(" SELECT otp_expiry FROM users_verify WHERE verify_token = ? " );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $expiry_time = $row['otp_expiry'];
        $expiry_timestamp = strtotime($expiry_time);
        $now = time();
        $duration = max(0, $expiry_timestamp - $now);
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body style="background-color: #f0f0f0; display: flex; justify-content: center; align-items: center; height: 100vh;">
    <form action="verify.php" method="post">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div
            style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
            <h2 style="text-align: center;">Verify Your Email</h2>
            <p><?php echo "OTP will expire at: $expiry_time"; ?></p>
            <p style="text-align: center;">Please enter the OTP sent to your email.</p>


            <input type="text" name="otp" placeholder="Enter 5-DIGIT OTP" required>
            <input type="submit" name="verify" value="Verify"
                style="background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            <h2>Countdown Timer</h2>
            <p id="timer">Loading...</p>

            <script>
            const expiryTime = new Date("<?php echo $expiry_time; ?>").getTime();
            const timerElement = document.getElementById("timer");

            function showTime() {
                const now = new Date().getTime();
                const left = Math.max(0, Math.floor((expiryTime - now) / 1000));

                const minutes = Math.floor(left / 60);
                const seconds = left % 60;
                const formatted = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

                timerElement.innerText = left > 0 ? formatted : "Time's up!";

                if (left <= 0) {
                    clearInterval(timer);
                }
            }

            showTime();
            const timer = setInterval(showTime, 1000);
            </script>
    </form>
</body>

</html>