<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include your database connection
require_once '../connection.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get service_id from query parameters
    $serviceId = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

    if (!$serviceId) {
        echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
        exit();
    }

    // Check if database connection exists
    if (!isset($connect) || !$connect) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }

    // Test if service exists
    $serviceCheckStmt = $connect->prepare("SELECT id FROM company_services WHERE id = ?");
    if (!$serviceCheckStmt) {
        throw new Exception("Failed to prepare service check query: " . $connect->error);
    }
    
    $serviceCheckStmt->bind_param("i", $serviceId);
    $serviceCheckStmt->execute();
    $serviceCheckResult = $serviceCheckStmt->get_result();
    
    if ($serviceCheckResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Service not found']);
        exit();
    }
    $serviceCheckStmt->close();

    // Function to format avatar URL
    function formatAvatarUrl($avatar) {
        // Log the original avatar value for debugging
        error_log("Original avatar value: " . var_export($avatar, true));
        
        if (empty($avatar) || $avatar === null) {
            $result = 'Assets/uploads/avatars/default-avatar.png';
            error_log("Avatar is empty, using default: " . $result);
            return $result;
        }
        
        // Check if it's an external URL (starts with http:// or https://)
        if (preg_match('/^https?:\/\//', $avatar)) {
            error_log("External URL detected: " . $avatar);
            return $avatar;
        }
        
        // Check if it already has the full path
        if (strpos($avatar, 'Assets/uploads/avatars/') === 0) {
            error_log("Full path already present: " . $avatar);
            return $avatar;
        }
        
        // Otherwise, prepend the local avatar path
        $result = 'Assets/uploads/avatars/' . $avatar;
        error_log("Local file, prepending path: " . $result);
        return $result;
    }

    // Get reviews with user information - FIXED COLUMN NAMES
    $reviewsStmt = $connect->prepare("
        SELECT 
            r.id,
            r.rating,
            r.review_text,
            r.created_at,
            r.updated_at,
            u.firstName,
            u.lastName,
            u.avatar
        FROM reviews r
        JOIN users_info u ON r.user_id = u.user_id
        WHERE r.service_id = ?
        ORDER BY r.created_at DESC
    ");
    
    if (!$reviewsStmt) {
        throw new Exception("Failed to prepare reviews query: " . $connect->error);
    }
    
    $reviewsStmt->bind_param("i", $serviceId);
    $reviewsStmt->execute();
    $reviewsResult = $reviewsStmt->get_result();
    
    $reviews = [];
    while ($row = $reviewsResult->fetch_assoc()) {
        // Log the raw database row for debugging
        error_log("Raw database row: " . print_r($row, true));
        
        $formattedAvatar = formatAvatarUrl($row['avatar']);
        
        $reviews[] = [
            'id' => $row['id'],
            'rating' => intval($row['rating']),
            'review_text' => $row['review_text'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
            'user_name' => trim($row['firstName'] . ' ' . $row['lastName']),
            'user_avatar' => $formattedAvatar,
            'debug_avatar' => [
                'original' => $row['avatar'],
                'formatted' => $formattedAvatar
            ]
        ];
    }
    $reviewsStmt->close();
    
    // Get review statistics
    $statsStmt = $connect->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as average_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM reviews 
        WHERE service_id = ?
    ");
    
    if (!$statsStmt) {
        throw new Exception("Failed to prepare stats query: " . $connect->error);
    }
    
    $statsStmt->bind_param("i", $serviceId);
    $statsStmt->execute();
    $statsResult = $statsStmt->get_result();
    $stats = $statsResult->fetch_assoc();
    $statsStmt->close();
    
    // Format statistics
    $statistics = [
        'total_reviews' => intval($stats['total_reviews'] ?? 0),
        'average_rating' => $stats['average_rating'] ? round(floatval($stats['average_rating']), 1) : 0,
        'rating_distribution' => [
            '5' => intval($stats['five_star'] ?? 0),
            '4' => intval($stats['four_star'] ?? 0),
            '3' => intval($stats['three_star'] ?? 0),
            '2' => intval($stats['two_star'] ?? 0),
            '1' => intval($stats['one_star'] ?? 0)
        ]
    ];
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'statistics' => $statistics,
        'debug' => [
            'service_id' => $serviceId,
            'total_reviews' => count($reviews)
        ]
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Load reviews error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to load reviews: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>