<?php
    require_once("f-config.php");
    require_once __DIR__ . '/../connection.php';

    try {
        $accessToken = $handler->getAccessToken();
    } catch (\Facebook\Exceptions\FacebookResponseException $e) {
        echo "Response Exception: " . $e->getMessage();
        exit();
    } catch (\Facebook\Exceptions\FacebookSDKException $e) {
        // echo "SDK Exception: " . $e->getMessage();
        // exit();
    }

    if (!$accessToken) {
        header('Location: /login');
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

    function insertData($userData, $connect) {

        if (!$connect || $connect->connect_error) {
            die("Database connection failed");
        }

        $session = bin2hex(random_bytes(16));

        $email = $userData['email'];
        $f_id = $userData['id'];
        $f_name = $userData['first_name'];
        $l_name = $userData['last_name'];
        $avatar = $userData['picture']['url'];

        // Check if email already exists
        $checkMainUser = mysqli_prepare($connect, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkMainUser, "s", $email);
        mysqli_stmt_execute($checkMainUser);
        $result = mysqli_stmt_get_result($checkMainUser);
        $mainUser = mysqli_fetch_assoc($result);

        // Check if Facebook ID exists
        $checkUser = mysqli_prepare($connect, "SELECT id FROM users WHERE facebook_id = ?");
        mysqli_stmt_bind_param($checkUser, "s", $f_id);
        mysqli_stmt_execute($checkUser);
        $result = mysqli_stmt_get_result($checkUser);
        $existingUser = mysqli_fetch_assoc($result);

        if ($existingUser) {
            $userId = $existingUser['id'];

            // 🔍 Fetch account type from users_info
            $accTypeQuery = mysqli_prepare($connect, "SELECT acc_type FROM users_info WHERE user_id = ?");
            mysqli_stmt_bind_param($accTypeQuery, "i", $userId);
            mysqli_stmt_execute($accTypeQuery);
            $accResult = mysqli_stmt_get_result($accTypeQuery);
            $accRow = mysqli_fetch_assoc($accResult);
            $_SESSION['acc_type'] = $accRow['acc_type'] ?? 'passenger';
            $_SESSION['user_id'] = $userId;

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

            header('Location: /home');
            exit;
        }

        if ($mainUser) {
            unset($_SESSION['userData']);
            unset($_SESSION['access_token']);
            echo "<script>alert('User already registered.'); window.location.href='/login';</script>";
            exit();
        }

        // Insert new user
        $insertUser = mysqli_prepare($connect, "
            INSERT INTO users (email, password, google_id, facebook_id, sign_with)
            VALUES (?, NULL, NULL, ?, 'facebook')
        ");
        mysqli_stmt_bind_param($insertUser, "ss", $email, $f_id);
        mysqli_stmt_execute($insertUser);
        $userId = mysqli_insert_id($connect);

        // 🟡 You can define a default acc_type here (e.g., 'passenger')
        $defaultAccType = 'passenger'; // or get it dynamically from UI later

        // Insert into users_info
        $insertInfo = mysqli_prepare($connect, "
            INSERT INTO users_info (
                user_id, acc_type, firstName, lastName, gender, contact, dob, country, avatar
            ) VALUES (
                ?, ?, ?, ?, NULL, NULL, NULL, NULL, ?
            )
        ");
        mysqli_stmt_bind_param($insertInfo, "issss", $userId, $defaultAccType, $f_name, $l_name, $avatar);
        mysqli_stmt_execute($insertInfo);

        // Set session variable for acc_type
        $_SESSION['acc_type'] = $defaultAccType;

        // Insert verification info
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

        setcookie("id", $userId, time() + 60 * 60 * 24 * 30, "/", NULL);
        setcookie("session", $session, time() + 60 * 60 * 24 * 30, "/", NULL);

        header('Location: /home');
        exit;
    }

    insertData($userData,$connect);

?>