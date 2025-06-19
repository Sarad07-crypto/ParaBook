<?php
    session_start();

    $loginType = '';
    $avatar = 'default-avatar.png';

    if (isset($_SESSION['user_email'])) {
        $loginType = 'google';
        $avatar = htmlspecialchars($_SESSION['avatar'] ?? 'default-avatar.png');

    } elseif (isset($_SESSION['access_token']) && isset($_SESSION['userData'])) {
        $loginType = 'facebook';
        $avatar = htmlspecialchars($_SESSION['userData']['picture']['url'] ?? 'default-avatar.png');

    } elseif (isset($_SESSION['Email'])) {
        $loginType = 'form';
        $avatar = 'default-avatar.png';

    } else {
        echo "<script>alert('Login Failed!!!'); window.location.href='/login';</script>";
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<style>
body {
    display: block !important;
    position: static !important;
    margin: 0;
    padding: 0;
    overflow: auto !important;
}
</style>

<body>

    <?php
        $accType = $_SESSION['acc_type'] ?? 'passenger';
        if ($accType === 'company') {
            require('partials/header_C.php');
            require('addServices.php');
        } else {
            require('partials/header_P.php');
            require('servicePassenger.php');
        }
    ?>

    <?php require('partials/footer.php'); ?>
    <script>
    if (window.location.hash && window.location.hash === '#_=_') {
        // Remove hash without reloading
        history.replaceState(null, null, window.location.href.split('#')[0]);
    }
    </script>
</body>

</html>