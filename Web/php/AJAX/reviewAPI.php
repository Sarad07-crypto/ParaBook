<?php

session_start();
header('Content-Type: application/json');

require_once 'bookingNotification.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    include "../connection.php";

    $userId = (int)$_SESSION['user_id'];
    $action = $_GET['action'] ?? $_POST['action'] ?? 'get';
    
    switch ($action) {
        case 'submit_review':
            // Submit a new review
            $serviceId = (int)($_POST['service_id'] ?? 0);
            $rating = (int)($_POST['rating'] ?? 0);
            $reviewText = trim($_POST['review_text'] ?? '');
            
            if ($serviceId <= 0 || $rating < 1 || $rating > 5 || empty($reviewText)) {
                echo json_encode(['success' => false, 'message' => 'Invalid review data']);
                exit;
            }
            
            // Check if user has already reviewed this service
            $checkStmt = $connect->prepare("SELECT id FROM reviews WHERE user_id = ? AND service_id = ?");
            $checkStmt->bind_param("ii", $userId, $serviceId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'You have already reviewed this service']);
                exit;
            }
            
            // Insert new review
            $insertStmt = $connect->prepare("INSERT INTO reviews (user_id, service_id, rating, review_text) VALUES (?, ?, ?, ?)");
            $insertStmt->bind_param("iiis", $userId, $serviceId, $rating, $reviewText);
            
            if ($insertStmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
            }
            break;
            
        case 'get_reviews':
            // Get reviews for a service
            $serviceId = (int)($_GET['service_id'] ?? 0);
            $limit = (int)($_GET['limit'] ?? 10);
            $offset = (int)($_GET['offset'] ?? 0);
            
            if ($serviceId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
                exit;
            }
            
            $reviewsStmt = $connect->prepare("
                SELECT r.*, u.firstName, u.lastName, 
                       DATE_FORMAT(r.created_at, '%M %d, %Y') as formatted_date,
                       TIMESTAMPDIFF(DAY, r.created_at, NOW()) as days_ago
                FROM reviews r 
                JOIN users_info u ON r.user_id = u.user_id 
                WHERE r.service_id = ? 
                ORDER BY r.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            
            $reviewsStmt->bind_param("iii", $serviceId, $limit, $offset);
            $reviewsStmt->execute();
            $result = $reviewsStmt->get_result();
            
            $reviews = [];
            while ($row = $result->fetch_assoc()) {
                // Format time ago
                if ($row['days_ago'] == 0) {
                    $row['time_ago'] = 'Today';
                } elseif ($row['days_ago'] == 1) {
                    $row['time_ago'] = '1 day ago';
                } elseif ($row['days_ago'] < 7) {
                    $row['time_ago'] = $row['days_ago'] . ' days ago';
                } elseif ($row['days_ago'] < 30) {
                    $weeks = floor($row['days_ago'] / 7);
                    $row['time_ago'] = $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
                } else {
                    $row['time_ago'] = $row['formatted_date'];
                }
                
                $reviews[] = $row;
            }
            
            echo json_encode(['success' => true, 'reviews' => $reviews]);
            break;
            
        case 'get_review_stats':
            // Get review statistics for a service
            $serviceId = (int)($_GET['service_id'] ?? 0);
            
            if ($serviceId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
                exit;
            }
            
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
            
            $statsStmt->bind_param("i", $serviceId);
            $statsStmt->execute();
            $result = $statsStmt->get_result();
            $stats = $result->fetch_assoc();
            
            // Format average rating
            $stats['average_rating'] = round($stats['average_rating'], 1);
            
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        case 'check_user_review':
            // Check if user has already reviewed this service
            $serviceId = (int)($_GET['service_id'] ?? 0);
            
            if ($serviceId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
                exit;
            }
            
            $checkStmt = $connect->prepare("SELECT id FROM reviews WHERE user_id = ? AND service_id = ?");
            $checkStmt->bind_param("ii", $userId, $serviceId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            $hasReviewed = $result->num_rows > 0;
            
            echo json_encode(['success' => true, 'has_reviewed' => $hasReviewed]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }

} catch (Exception $e) {
    error_log("Review operation error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'debug_error' => $e->getMessage()
    ]);
} finally {
    if (isset($connect) && $connect) {
        $connect->close();
    }
}
?>