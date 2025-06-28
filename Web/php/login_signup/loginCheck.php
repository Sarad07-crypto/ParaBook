<?php
session_start();
require 'Web/php/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please enter both email and password.'); window.location.href='/login';</script>";
        exit();
    }

    $stmt = $connect->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    
    if (!$stmt->execute()) {
        echo "<script>alert('Database error.'); window.location.href='/login';</script>";
        exit();
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $userId = $user['id'];
            
            // Set consistent session variables
            $_SESSION['Email'] = $email;
            $_SESSION['user_id'] = $userId;
            $_SESSION['login_type'] = 'form'; // Add this to identify login type

            $verifyStmt = $connect->prepare("SELECT * FROM users_verify WHERE user_id = ? AND is_verified = 0");
            $verifyStmt->bind_param("i", $userId);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();

            if ($verifyResult->num_rows > 0) {
                include "updateotp.php";
            } else {
                $infoStmt = $connect->prepare("SELECT firstName, lastName, avatar, acc_type FROM users_info WHERE user_id = ?");
                $infoStmt->bind_param("i", $userId);
                $infoStmt->execute();
                $userInfoResult = $infoStmt->get_result();
                
                if ($userInfoResult->num_rows > 0) {
                    $userInfo = $userInfoResult->fetch_assoc();
                    
                    $_SESSION['firstName'] = $userInfo['firstName'] ?? 'User';
                    $_SESSION['lastName'] = $userInfo['lastName'] ?? '';
                    $_SESSION['avatar'] = $userInfo['avatar'] ?? 'default-avatar.png';
                    $_SESSION['acc_type'] = $userInfo['acc_type'];
                } else {
                    // Set default values if no user info found
                    $_SESSION['firstName'] = 'User';
                    $_SESSION['lastName'] = '';
                    $_SESSION['avatar'] = 'default-avatar.png';
                    $_SESSION['acc_type'] = 'regular';
                }
        
                $session = bin2hex(random_bytes(16));
                // Update session
                $updateSession = mysqli_prepare($connect, "
                    INSERT INTO users_sessions (user_id, session_token, created_at, expires_at)
                    VALUES (?, ?, NOW(), NOW() + INTERVAL 1 DAY)
                    ON DUPLICATE KEY UPDATE 
                        session_token = VALUES(session_token), 
                        created_at = NOW(),
                        expires_at = NOW() + INTERVAL 1 DAY
                ");

                mysqli_stmt_bind_param($updateSession, "is", $userId, $session);
                mysqli_stmt_execute($updateSession);

                setcookie("id", $userId, time() + 60 * 60 * 24 * 30, "/", NULL);
                setcookie("session", $session, time() + 60 * 60 * 24 * 30, "/", NULL);

                echo "<script>alert('You are already verified 😊'); window.location.href='/home';</script>";
            }
        } else {
            echo "<script>alert('Invalid email or password.'); window.location.href='/login';</script>";
        }
    } else {
        echo "<script>alert('Invalid email or password.'); window.location.href='/login';</script>";
    }
}
?>