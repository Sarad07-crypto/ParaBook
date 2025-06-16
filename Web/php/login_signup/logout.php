<?php
    session_start();
    
    $_SESSION = [];

    session_destroy();

    setcookie('id', '', time() - 3600, '/');
    setcookie('session', '', time() - 3600, '/');

    // Optionally clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    header('Location: /login');
    exit;
?>