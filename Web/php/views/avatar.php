<?php
    session_start();

    $loginType = '';
    $avatar = 'default-avatar.png';

    if (isset($_SESSION['user_email'])) {
        $loginType = 'google';
        $avatar = htmlspecialchars($_SESSION['avatar'] ?? 'default-avatar.png');

        $_SESSION['firstName'] = $_SESSION['givenName'];
        $_SESSION['lastName'] = $_SESSION['familyName'];
        $_SESSION['avatar'] = $avatar;

    } elseif (isset($_SESSION['access_token']) && isset($_SESSION['userData'])) {
        $loginType = 'facebook';
        $avatar = htmlspecialchars($_SESSION['userData']['picture']['url'] ?? 'default-avatar.png');

        $_SESSION['firstName'] = $_SESSION['userData']['first_name'] ?? 'User';
        $_SESSION['lastName'] = $_SESSION['userData']['last_name'] ?? '';
        $_SESSION['avatar'] = $avatar;

    } elseif (isset($_SESSION['Email'])) {
        $loginType = 'form';
        $avatar = 'default-avatar.png';

        $_SESSION['firstName'] = $_SESSION['FirstName'] ?? 'User';
        $_SESSION['lastName'] = $_SESSION['LastName'] ?? '';
        $_SESSION['avatar'] = $avatar;
    }
    else {
        echo "<script>alert('Login Failed!!!'); window.location.href='/login';</script>";
        exit;
    }
?>