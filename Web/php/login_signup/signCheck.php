<?php

require_once '../../vendor/autoload.php';
include("../connection.php");

// Collect and sanitize inputs
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendMail_verification($firstName, $email, $_verify, $otp_code)
{

    $mail = new PHPMailer(true);

    try {
        require_once '../env.php';
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();

        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        // Enable SMTP authentication
        $mail->Username = $MAIL;
        $mail->Password = $PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('noreply@gmail.com', $firstName);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification From PARABOOK SYSTEM';
        $email_body = "<h1>Hi $firstName,</h1> <h5> VERIFY YOUR EMAIL</h5><p>YOUR CODE IS: ></h5> <h1>$otp_code</h1>";
        $mail->Body = $email_body;
        $mail->send();
        echo "<script>alert('Verification email sent!');</script>";
        // echo"<script>window.location.href='verify.php';</script>";
    } catch (Exception $e) {
        echo "Mail Error: {$mail->ErrorInfo}";
    }

}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];
    $contact = $_POST["contact"];
    $password = $_POST["password"];
    $dob = $_POST["DOB"];
    $country = $_POST["country"];
    $accountType = $_POST["userType"];
    $gender = $_POST["gender"];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $otp_code = mt_rand(10000, 99999);
    $verify_token = md5(uniqid());
    date_default_timezone_set('Asia/Kathmandu');
    $expiry_time = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    // ********************************************************************************
    // need to check it again after g-ID or f-ID linked *****************************/
    $check = $connect->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.location.href='signup.php';</script>";
        $check->close();
        $connect->close();
        exit();
    }
    $check->close();

    // Insert into 'users' table
    $null = NULL;
    $form = 'form';
    $stmt = $connect->prepare("INSERT INTO users (email, password, google_id, facebook_id, sign_with) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $hashedPassword, $null, $null, $form);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Insert into 'users_info' table
    $stmtInfo = $connect->prepare("INSERT INTO users_info (user_id, acc_type, firstName, lastName, gender, contact, dob, country, avatar)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL)");
    $stmtInfo->bind_param("isssssss", $user_id, $accountType, $firstName, $lastName, $gender, $contact, $dob, $country);
    $stmtInfo->execute();
    $stmtInfo->close();

    // Insert into 'users_verify' table
    $stmtVerify = $connect->prepare("INSERT INTO users_verify (user_id, verify_token, otp, otp_expiry) VALUES (?, ?, ?, ?)");
    $stmtVerify->bind_param("isss", $user_id, $verify_token, $otp_code, $expiry_time);
    $stmtVerify->execute();
    $stmtVerify->close();

    // Send verification email
    sendMail_verification($firstName, $email, $verify_token, $otp_code);

    echo "<script>alert('Registration successful! Please verify your email.');</script>";
    echo "<script>window.location.href='verify.php?token=$verify_token';</script>";

    $connect->close();
}

?>