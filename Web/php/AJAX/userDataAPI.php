<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    // Database connection (adjust these credentials to match your setup)
    $pdo = new PDO("mysql:host=localhost:3307;dbname=parabook", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $userId = $_SESSION['user_id'];
    
    // Query to get user data from both tables
    $stmt = $pdo->prepare("
        SELECT 
            u.email,
            ui.acc_type,
            ui.firstName,
            ui.lastName,
            ui.gender,
            ui.contact,
            ui.dob,
            ui.country,
            ui.avatar
        FROM users u
        LEFT JOIN users_info ui ON u.id = ui.user_id
        WHERE u.id = ?
    ");
    
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userData) {
        // Format date for HTML date input (YYYY-MM-DD)
        if ($userData['dob'] && $userData['dob'] !== '0000-00-00') {
            $date = new DateTime($userData['dob']);
            $userData['dob'] = $date->format('Y-m-d');
        } else {
            $userData['dob'] = null; // Set to null if invalid date
        }
        
        // Handle null values
        $userData = array_map(function($value) {
            return $value === null ? '' : $value;
        }, $userData);
        
        echo json_encode([
            'success' => true,
            'data' => $userData
        ]);
    } else {
        // User exists but no additional info
        echo json_encode([
            'success' => true,
            'data' => [
                'email' => '',
                'acc_type' => '',
                'firstName' => '',
                'lastName' => '',
                'gender' => '',
                'contact' => '',
                'dob' => '',
                'country' => '',
                'avatar' => ''
            ]
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>