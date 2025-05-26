<?php
    require '../../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    session_start();

    $duration = 60;

    function sendMailVerification($firstName, $email, $otpCode)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = 'saradcr7adhikari@gmail.com';
            $mail->Password = 'vtsl wtoo zusf ojbp';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('noreply@parabook.com', 'PARABOOK SYSTEM');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification From PARABOOK SYSTEM';

            $mail->Body = "<h1>Hi $firstName,</h1><h5>VERIFY YOUR EMAIL</h5><p>YOUR CODE IS:</p><h1>$otpCode</h1>";

            $mail->send();
        } catch (Exception $e) {
            echo "<script>alert('Mail error: {$mail->ErrorInfo}');</script>";
            return false;
        }

        return true;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include "connection.php";

        if (!isset($_SESSION['Email'], $_SESSION['user_id'])) {
            echo "<script>alert('Session expired. Please login again.');</script>";
            echo "<script>window.location.href='login.php';</script>";
            exit();
        }

        $email = $_SESSION['Email'];
        $userId = $_SESSION['user_id'];

        // Generate OTP
        $otpCode = rand(10000, 99999);
        $_SESSION['otp'] = $otpCode;

        // Fetch user first name
        $queryUser = "SELECT firstName FROM users_info WHERE user_id = ?";
        $stmt = $connect->prepare($queryUser);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $firstName = $row['firstName'];
        } else {
            echo "<script>alert('User information not found.');</script>";
            exit();
        }

        // Send OTP Email
        if (sendMailVerification($firstName, $email, $otpCode)) {
            date_default_timezone_set('Asia/Kathmandu');
            $expiryTime = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            $token = md5(uniqid());

            // Update OTP and token in DB
            $queryUpdate = "UPDATE users_verify SET otp = ?, otp_expiry = ?, verify_token = ? WHERE user_id = ? AND is_verified = 0";
            $stmt = $connect->prepare($queryUpdate);
            $stmt->bind_param("issi", $otpCode, $expiryTime, $token, $userId);

            if ($stmt->execute()) {
                echo "<script>alert('OTP sent to your email. Please verify.');</script>";
                echo "<script>window.location.href='updateverify.php';</script>";
                exit();
            } else {
                echo "<script>alert('Failed to update OTP: " . $stmt->error . "');</script>";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>
<body>
    <form action="" method="post">
        <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
            <h2 style="text-align: center;">Verify Your Email</h2>
            <p><?php if (isset($expiryTime)) echo "OTP will expire at: $expiryTime"; ?></p>
            <p style="text-align: center;">Please enter the OTP sent to your email.</p>
            <input type="text" name="otpp" placeholder="Enter 5-DIGIT OTP" required>
            <input type="submit" name="verifyy" value="Verify"
                style="background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            <h2>Countdown Timer</h2>
            <p id="timer" style="color: red;">Loading...</p>
        </div>
    </form>

    <script>
        const duration = <?php echo $duration; ?>;
        const key = "endTime";
        const now = Date.now();

        let end = localStorage.getItem(key);

        if (!end || now > end) {
            end = now + duration * 1000;
            localStorage.setItem(key, end);
        } else {
            end = parseInt(end);
        }

        function showTime() {
            const left = Math.max(0, Math.floor((end - Date.now()) / 1000));
            document.getElementById("timer").innerText = left > 0 ? left + "s" : "Time's up!";
            if (left <= 0) {
                clearInterval(timer);
                localStorage.removeItem(key);
            }
        }

        showTime();
        const timer = setInterval(showTime, 1000);
    </script>
</body>
</html>