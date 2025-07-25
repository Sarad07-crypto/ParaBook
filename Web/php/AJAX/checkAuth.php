<?php
// =======================================
// checkAuth.php - Check if user is authenticated
// =======================================
session_start();
header('Content-Type: application/json');

echo json_encode([
    'authenticated' => isset($_SESSION['user_id']) && !empty($_SESSION['user_id']),
    'user_id' => $_SESSION['user_id'] ?? null
]);
exit;

?>