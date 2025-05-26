<?php
    require_once("f-config.php");
    require_once("../connection.php");

    try {
        $accessToken = $handler->getAccessToken();
    } catch (\Facebook\Exceptions\FacebookResponseException $e) {
        echo "Response Exception: " . $e->getMessage();
        exit();
    } catch (\Facebook\Exceptions\FacebookSDKException $e) {
        echo "SDK Exception: " . $e->getMessage();
        exit();
    }

    if (!$accessToken) {
        header('Location: ../login_signup/login.php');
        exit();
    }

    $oAuth2Client = $fbObject->getOAuth2Client();

    // Get long-lived access token if not already
    if (!$accessToken->isLongLived()) {
        try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo "Error getting long-lived access token: " . $e->getMessage();
            exit();
        }
    }

    try {
        $response = $fbObject->get("/me?fields=id,first_name,last_name,email,picture.type(large)", $accessToken);
        $userData = $response->getGraphNode()->asArray();
    } catch (\Facebook\Exceptions\FacebookResponseException $e) {
        echo "Error fetching user data: " . $e->getMessage();
        exit();
    }

    $_SESSION['userData'] = $userData;
    $_SESSION['access_token'] = (string) $accessToken;

    function insertData($userData) {
        global $connect; // use mysqli connection from connection.php

        $session = bin2hex(random_bytes(16));

        // Escape values to prevent SQL injection
        $email = mysqli_real_escape_string($connect, $userData['email']);
        $f_id = mysqli_real_escape_string($connect, $userData['id']);
        $f_name = mysqli_real_escape_string($connect, $userData['first_name']);
        $l_name = mysqli_real_escape_string($connect, $userData['last_name']);
        $avatar = mysqli_real_escape_string($connect, $userData['picture']['url']);

        // Check if email exists in users table
        $checkMainUser = mysqli_query($connect, "SELECT * FROM users WHERE email = '$email'");
        $mainUser = mysqli_fetch_assoc($checkMainUser);

        // Check if user with facebook_id already exists
        $checkUser = mysqli_query($connect, "SELECT id FROM users WHERE facebook_id = '$f_id'");
        $existingUser = mysqli_fetch_assoc($checkUser);

        if ($existingUser) {
            $userId = $existingUser['id'];

            // Update session
            $updateSession = mysqli_prepare($connect, "
                UPDATE users_sessions
                SET session_token = ?, created_at = NOW()
                WHERE user_id = ?
            ");
            mysqli_stmt_bind_param($updateSession, "si", $session, $userId);
            mysqli_stmt_execute($updateSession);

            setcookie("id", $userId, time() + 60 * 60 * 24 * 30, "/", NULL);
            setcookie("session", $session, time() + 60 * 60 * 24 * 30, "/", NULL);

            header('Location: ../home/passenger.php');
            exit;
        }

        if ($mainUser) {        
            unset($_SESSION['userData']);
            unset($_SESSION['access_token']);
            echo "<script>alert('User already register!!!'); window.location.href='../login_signup/login.php';</script>";
            exit();
        }

        // Insert into users
        $insertUser = mysqli_prepare($connect, "
            INSERT INTO users (email, password, google_id, facebook_id, sign_with)
            VALUES (?, NULL, NULL, ?, 'facebook')
        ");
        mysqli_stmt_bind_param($insertUser, "ss", $email, $f_id);
        mysqli_stmt_execute($insertUser);
        $userId = mysqli_insert_id($connect);

        // Insert into users_info
        $insertInfo = mysqli_prepare($connect, "
            INSERT INTO users_info (
                user_id, acc_type, firstName, lastName, gender, contact, dob, country, avatar
            ) VALUES (
                ?, NULL, ?, ?, NULL, NULL, NULL, NULL, ?
            )
        ");
        mysqli_stmt_bind_param($insertInfo, "isss", $userId, $f_name, $l_name, $avatar);
        mysqli_stmt_execute($insertInfo);

        // Insert into users_verify
        $insertverify = mysqli_prepare($connect, "
            INSERT INTO users_verify (user_id, verify_token, is_verified, otp, otp_expiry)
            VALUES (?, NULL, 1, NULL, NULL)
        ");
        mysqli_stmt_bind_param($insertverify, "i", $userId);
        mysqli_stmt_execute($insertverify);

        // Insert session
        $insertSession = mysqli_prepare($connect, "
            INSERT INTO users_sessions (user_id, session_token, created_at)
            VALUES (?, ?, NOW())
        ");
        mysqli_stmt_bind_param($insertSession, "is", $userId, $session);
        mysqli_stmt_execute($insertSession);

        // Set cookies
        setcookie("id", $userId, time() + 60 * 60 * 24 * 30, "/", NULL);
        setcookie("session", $session, time() + 60 * 60 * 24 * 30, "/", NULL);

        header('Location: ../home/passenger.php');
        exit;
    }

    insertData($userData);

?>