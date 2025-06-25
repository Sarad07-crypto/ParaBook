<?php
session_start();

$loginType = '';
$avatar = 'default-avatar.png';
$firstName = 'User';
$lastName = '';

// Check for Google login
if (isset($_SESSION['user_email'])) {
    $loginType = 'google';
    $avatar = htmlspecialchars($_SESSION['avatar'] ?? 'default-avatar.png');
    $firstName = $_SESSION['givenName'] ?? 'User';
    $lastName = $_SESSION['familyName'] ?? '';
    
    // Update session variables for consistency
    $_SESSION['firstName'] = $firstName;
    $_SESSION['lastName'] = $lastName;

// Check for Facebook login
} elseif (isset($_SESSION['access_token']) && isset($_SESSION['userData'])) {
    $loginType = 'facebook';
    $avatar = htmlspecialchars($_SESSION['userData']['picture']['url'] ?? 'default-avatar.png');
    $firstName = $_SESSION['userData']['first_name'] ?? 'User';
    $lastName = $_SESSION['userData']['last_name'] ?? '';
    
    // Update session variables for consistency
    $_SESSION['firstName'] = $firstName;
    $_SESSION['lastName'] = $lastName;
    $_SESSION['avatar'] = $avatar;

// Check for form login
} elseif (isset($_SESSION['Email']) && isset($_SESSION['user_id'])) {
    $loginType = 'form';
    $firstName = $_SESSION['firstName'] ?? 'User';
    $lastName = $_SESSION['lastName'] ?? '';
    
    // Handle avatar for form login
    if (isset($_SESSION['avatar']) && !empty($_SESSION['avatar']) && $_SESSION['avatar'] !== 'default-avatar.png') {
        $avatar = 'Assets/uploads/avatars/' . $_SESSION['avatar'];
    } else {
        $avatar = 'Assets/uploads/avatars/default-avatar.png';
    }

} else {
    // No valid session found
    echo "<script>alert('Login Failed!!! Please login again.'); window.location.href='/login';</script>";
    exit;
}

// Set global variables for use in other files
$fullName = trim($firstName . ' ' . $lastName);
if (empty($fullName) || $fullName === ' ') {
    $fullName = 'User';
}
?>