<?php
    require_once('g-config.php');
    session_start();

    if (isset($_GET['provider']) && $_GET['provider'] === 'google') {
        $_SESSION['oAuth_provider'] = 'google';
        header('Location: ' . $login_url);
        exit();
    } else {
        echo "Invalid login request.";
    }
?>