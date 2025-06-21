<?php

    require_once("f-config.php");
    require_once __DIR__ . '/../connection.php';

    use Facebook\Exceptions\FacebookResponseException;
    use Facebook\Exceptions\FacebookSDKException;

    // Get Facebook access token
    try {
        $accessToken = $handler->getAccessToken();
    } catch (FacebookResponseException $e) {
        echo "Response Exception: " . $e->getMessage();
        exit();
    } catch (FacebookSDKException $e) {
        // echo "SDK Exception: " . $e->getMessage();
        // exit();
    }

    if (!$accessToken) {
        header('Location: /login');
        exit();
    }

    // Exchange for long-lived token
    $oAuth2Client = $fbObject->getOAuth2Client();
    if (!$accessToken->isLongLived()) {
        try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        } catch (FacebookSDKException $e) {
            echo "Error getting long-lived access token: " . $e->getMessage();
            exit();
        }
    }

    // Fetch user data
    try {
        $response = $fbObject->get("/me?fields=id,first_name,last_name,email,picture.type(large)", $accessToken);
        $userData = $response->getGraphNode()->asArray();
    } catch (FacebookResponseException $e) {
        echo "Error fetching user data: " . $e->getMessage();
        exit();
    }

    $_SESSION['userData'] = $userData;
    $_SESSION['access_token'] = (string) $accessToken;

    $email = $userData['email'] ?? '';
    $facebookId = $userData['id'] ?? '';
    $firstName = $userData['first_name'] ?? '';
    $lastName = $userData['last_name'] ?? '';
    $avatar = $userData['picture']['url'] ?? '';
    $session = bin2hex(random_bytes(16));

    // Check if Facebook ID already exists
    $checkFbUser = mysqli_prepare($connect, "SELECT id FROM users WHERE facebook_id = ?");
    mysqli_stmt_bind_param($checkFbUser, "s", $facebookId);
    mysqli_stmt_execute($checkFbUser);
    $fbResult = mysqli_stmt_get_result($checkFbUser);
    $existingUser = mysqli_fetch_assoc($fbResult);

    // Check if email already exists
    $checkMainUser = mysqli_prepare($connect, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($checkMainUser, "s", $email);
    mysqli_stmt_execute($checkMainUser);
    $mainResult = mysqli_stmt_get_result($checkMainUser);
    $mainUser = mysqli_fetch_assoc($mainResult);

    if ($existingUser) {
        // User already has Facebook linked
        $userId = $existingUser['id'];
    } elseif ($mainUser) {
        // User exists with same email but no Facebook linked - link the accounts
        $userId = $mainUser['id'];
        
        // Link Facebook ID to existing account
        $linkFacebook = mysqli_prepare($connect, "UPDATE users SET facebook_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($linkFacebook, "si", $facebookId, $userId);
        mysqli_stmt_execute($linkFacebook);
    } else {
        // New user: save temp data for account selection
        $_SESSION['temp_user_data'] = [
            'email' => $email,
            'facebook_id' => $facebookId,
            'givenName' => $firstName,
            'familyName' => $lastName,
            'avatar' => $avatar,
            'sign_with' => 'facebook'
        ];
        $_SESSION['show_account_selection'] = true;

        header("Location: /accountSelection");
        exit();
    }

    // If we reach here, user exists (either with FB linked or just linked)
    // Get account type
    $accTypeQuery = mysqli_prepare($connect, "SELECT acc_type FROM users_info WHERE user_id = ?");
    mysqli_stmt_bind_param($accTypeQuery, "i", $userId);
    mysqli_stmt_execute($accTypeQuery);
    $accResult = mysqli_stmt_get_result($accTypeQuery);
    $accRow = mysqli_fetch_assoc($accResult);
    $_SESSION['acc_type'] = $accRow['acc_type'] ?? 'passenger';
    $_SESSION['user_id'] = $userId;
    $_SESSION['firstName'] = $firstName;
    $_SESSION['lastName'] = $lastName;

    // Update or create session token
    $updateSession = mysqli_prepare($connect, "
        INSERT INTO users_sessions (user_id, session_token, created_at) 
        VALUES (?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE session_token = VALUES(session_token), created_at = VALUES(created_at)
    ");
    mysqli_stmt_bind_param($updateSession, "is", $userId, $session);
    mysqli_stmt_execute($updateSession);

    // Set cookies
    setcookie("id", $userId, time() + 60 * 60 * 24 * 30, "/");
    setcookie("session", $session, time() + 60 * 60 * 24 * 30, "/");

    header('Location: /home');
    exit;
?>