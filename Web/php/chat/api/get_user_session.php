<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'user_id' => $_SESSION['user_id'],
        'authenticated' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Not authenticated',
        'authenticated' => false
    ]);
}
?>