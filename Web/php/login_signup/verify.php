<?php
    require_once __DIR__ . '/../connection.php';

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
                echo "<script>window.location.href='/';</script>";
            } else {
                echo "<script>alert('Invalid OTP!'); window.location.href='/verify?token=$token';</script>";
            }
        } else {
            echo "<script>alert('Invalid or expired token!'); window.location.href='/';</script>";
        }
    } else {
        echo "<script>alert('Missing verification token!'); window.location.href='/';</script>";
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
    body {
        background: #1170e3;
        min-height: 100vh;
        margin: 0;
        font-family: 'Montserrat', Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    form {
        width: 370px;
    }

    .card {
        background: #eaf4fb;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(17, 112, 227, 0.13);
        padding: 36px 28px;
        border: none;
        transition: box-shadow 0.2s;
    }

    .card:hover {
        box-shadow: 0 12px 40px rgba(17, 112, 227, 0.18);
    }

    h2 {
        text-align: center;
        color: #2d3a4b;
        margin-bottom: 14px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    p {
        color: #3a4a5d;
        text-align: center;
        margin-bottom: 18px;
        font-size: 1rem;
    }

    input[type="text"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 18px;
        border: 1.5px solid #dbeafe;
        border-radius: 8px;
        background: #f8fbff;
        font-size: 1rem;
        transition: border 0.2s, box-shadow 0.2s;
        box-shadow: 0 1px 4px rgba(17, 112, 227, 0.07);
    }

    input[type="text"]:focus {
        outline: none;
        border: 1.5px solid #1170e3;
        box-shadow: 0 0 0 2px #b6e0fe;
    }

    input[type="submit"] {
        width: 100%;
        background: #1170e3;
        color: #fff;
        border: none;
        padding: 12px 0;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.18s, transform 0.18s;
        box-shadow: 0 2px 8px rgba(17, 112, 227, 0.13);
    }

    input[type="submit"]:hover {
        background: #0d5bb5;
        transform: translateY(-2px) scale(1.03);
    }

    #timer {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1170e3;
        margin-bottom: 0;
        letter-spacing: 1px;
    }
    </style>
</head>

<body>
    <form action="/verify" method="post">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="card">
            <h2>Verify Your Email</h2>
            <p><?php echo "OTP will expire at: $expiry_time"; ?></p>
            <p>Please enter the OTP sent to your email.</p>

            <input type="text" name="otp" placeholder="Enter 5-DIGIT OTP" required>
            <input type="submit" name="verify" value="Verify">
            <h2 style="font-size:1.1rem; margin-top:24px;">Countdown Timer</h2>
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
        </div>
    </form>
</body>

</html>