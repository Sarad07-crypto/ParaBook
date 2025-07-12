<?php
// getReview.php - Review API Handler
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require '../connection.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$serviceId = 0;

// Check multiple sources for service_id
if (isset($_POST['service_id']) && !empty($_POST['service_id'])) {
    $serviceId = intval($_POST['service_id']);
} elseif (isset($_GET['service_id']) && !empty($_GET['service_id'])) {
    $serviceId = intval($_GET['service_id']);
} elseif (isset($_SESSION['service_id']) && !empty($_SESSION['service_id'])) {
    $serviceId = intval($_SESSION['service_id']);
}

// Store in session for future use
$_SESSION['service_id'] = $serviceId;

// Helper function to send JSON response
function sendResponse($success, $data = null, $message = '') {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

// Helper function to calculate time ago
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    } elseif ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    } elseif ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'Just now';
    }
}

// Function to check if user can review (same logic as main PHP)
function canUserReview($currentUserId, $serviceId, $connect) {
    if (!$currentUserId || !$serviceId) {
        error_log("Missing user ID or service ID");
        return false;
    }
    
    $stmt = $connect->prepare("
        SELECT COUNT(*) as booking_count 
        FROM bookings 
        WHERE user_id = ? AND service_id = ? AND status = 'completed'
    ");
    
    if ($stmt) {
        $stmt->bind_param("ii", $currentUserId, $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        error_log("Completed booking count for passenger $currentUserId: " . $row['booking_count']);
        return $row['booking_count'] > 0;
    } else {
        error_log("Failed to prepare booking query: " . $connect->error);
        return false;
    }
}

// Get the action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_review_stats':
        getReviewStats($connect);
        break;
    
    case 'get_reviews':
        getReviews($connect);
        break;
    
    case 'check_user_review':
        checkUserReview($connect);
        break;
    
    case 'submit_review':
        submitReview($connect);
        break;
    
    default:
        sendResponse(false, null, 'Invalid action');
}

// Get review statistics
function getReviewStats($connect) {
    global $serviceId;
    
    if (empty($serviceId)) {
        sendResponse(false, null, 'Service ID is required');
    }
    
    $stmt = $connect->prepare("SELECT 
                COUNT(*) as total_reviews,
                COALESCE(AVG(rating), 0) as average_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
              FROM reviews 
              WHERE service_id = ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        // Round average rating to 1 decimal place
        $stats['average_rating'] = round($stats['average_rating'], 1);
        
        sendResponse(true, ['stats' => $stats]);
    } else {
        sendResponse(false, null, 'Database error: ' . $connect->error);
    }
}

// Get reviews with pagination
function getReviews($connect) {
    global $serviceId;
    
    $limit = (int)($_GET['limit'] ?? 10);
    $offset = (int)($_GET['offset'] ?? 0);
    
    if (empty($serviceId)) {
        sendResponse(false, null, 'Service ID is required');
    }
    
    $limit = max(1, min(50, $limit)); // Limit between 1 and 50
    $offset = max(0, $offset);
    
    $stmt = $connect->prepare("SELECT 
                r.id,
                r.rating,
                r.review_text,
                r.created_at,
                u.firstName,
                u.lastName,
                u.email
              FROM reviews r
              JOIN users_info u ON r.user_id = u.user_id
              WHERE r.service_id = ?
              ORDER BY r.created_at DESC
              LIMIT ? OFFSET ?");
    
    if ($stmt) {
        $stmt->bind_param("iii", $serviceId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = [
                'id' => $row['id'],
                'rating' => (int)$row['rating'],
                'review_text' => htmlspecialchars($row['review_text']),
                'firstName' => htmlspecialchars($row['firstName']),
                'lastName' => htmlspecialchars($row['lastName']),
                'time_ago' => timeAgo($row['created_at']),
                'created_at' => $row['created_at']
            ];
        }
        $stmt->close();
        
        sendResponse(true, ['reviews' => $reviews]);
    } else {
        sendResponse(false, null, 'Database error: ' . $connect->error);
    }
}

// Check if current user has already reviewed this service
function checkUserReview($connect) {
    global $serviceId;
    
    if (empty($serviceId)) {
        sendResponse(false, null, 'Service ID is required');
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        sendResponse(true, ['has_reviewed' => false, 'can_review' => false]);
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if user has already reviewed this service
    $stmt = $connect->prepare("SELECT id FROM reviews WHERE user_id = ? AND service_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();
        $has_reviewed = $result->num_rows > 0;
        $stmt->close();
        
        // Check if the service exists and is approved
        $service_stmt = $connect->prepare("SELECT id FROM company_services WHERE id = ? AND status = 'approved'");
        if ($service_stmt) {
            $service_stmt->bind_param("i", $serviceId);
            $service_stmt->execute();
            $service_result = $service_stmt->get_result();
            $service_exists = $service_result->num_rows > 0;
            $service_stmt->close();
            
            sendResponse(true, [
                'has_reviewed' => $has_reviewed,
                'can_review' => $service_exists && !$has_reviewed
            ]);
        } else {
            sendResponse(false, null, 'Database error: ' . $connect->error);
        }
    } else {
        sendResponse(false, null, 'Database error: ' . $connect->error);
    }
}

// Submit a new review
function submitReview($connect) {
    global $serviceId;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, null, 'You must be logged in to submit a review');
    }
    
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'] ?? '';
    $review_text = $_POST['review_text'] ?? '';
    
    // Validate inputs
    if (empty($serviceId) || empty($rating) || empty($review_text)) {
        sendResponse(false, null, 'All fields are required. Service ID: ' . $serviceId);
    }
    
    // Validate rating
    $rating = (int)$rating;
    if ($rating < 1 || $rating > 5) {
        sendResponse(false, null, 'Rating must be between 1 and 5');
    }
    
    // Validate review text length
    if (strlen($review_text) < 10) {
        sendResponse(false, null, 'Review must be at least 10 characters long');
    }
    
    if (strlen($review_text) > 1000) {
        sendResponse(false, null, 'Review must be less than 1000 characters');
    }
    
    // Check if service exists and is approved
    $service_stmt = $connect->prepare("SELECT id FROM company_services WHERE id = ? AND status = 'approved'");
    if ($service_stmt) {
        $service_stmt->bind_param("i", $serviceId);
        $service_stmt->execute();
        $service_result = $service_stmt->get_result();
        
        if ($service_result->num_rows == 0) {
            $service_stmt->close();
            sendResponse(false, null, 'Service not found or not available for review');
        }
        $service_stmt->close();
    } else {
        sendResponse(false, null, 'Database error: ' . $connect->error);
    }
    
    // Check if user can review (has completed booking)
    if (!canUserReview($user_id, $serviceId, $connect)) {
        sendResponse(false, null, 'You can only review services you have completed bookings for');
    }
    
    // Check if user has already reviewed this service
    $existing_stmt = $connect->prepare("SELECT id FROM reviews WHERE user_id = ? AND service_id = ?");
    if ($existing_stmt) {
        $existing_stmt->bind_param("ii", $user_id, $serviceId);
        $existing_stmt->execute();
        $existing_result = $existing_stmt->get_result();
        
        if ($existing_result->num_rows > 0) {
            $existing_stmt->close();
            sendResponse(false, null, 'You have already reviewed this service');
        }
        $existing_stmt->close();
    } else {
        sendResponse(false, null, 'Database error: ' . $connect->error);
    }
    
    // Insert the review
    $insert_stmt = $connect->prepare("INSERT INTO reviews (user_id, service_id, rating, review_text, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($insert_stmt) {
        $insert_stmt->bind_param("iiis", $user_id, $serviceId, $rating, $review_text);
        $insert_result = $insert_stmt->execute();
        
        if ($insert_result) {
            $review_id = $connect->insert_id;
            $insert_stmt->close();
            
            // Get the inserted review data to return
            $get_review_stmt = $connect->prepare("SELECT 
                               r.id,
                               r.rating,
                               r.review_text,
                               r.created_at,
                               u.firstName,
                               u.lastName
                             FROM reviews r
                             JOIN users_info u ON r.user_id = u.user_id
                             WHERE r.id = ?");
            
            if ($get_review_stmt) {
                $get_review_stmt->bind_param("i", $review_id);
                $get_review_stmt->execute();
                $get_review_result = $get_review_stmt->get_result();
                
                if ($get_review_result->num_rows > 0) {
                    $review_data = $get_review_result->fetch_assoc();
                    $review_data['time_ago'] = timeAgo($review_data['created_at']);
                    $get_review_stmt->close();
                    
                    sendResponse(true, ['review' => $review_data], 'Review submitted successfully');
                } else {
                    $get_review_stmt->close();
                    sendResponse(true, null, 'Review submitted successfully');
                }
            } else {
                sendResponse(true, null, 'Review submitted successfully');
            }
        } else {
            sendResponse(false, null, 'Database error: ' . $connect->error);
        }
    } else {
        sendResponse(false, null, 'Database error: ' . $connect->error);
    }
}

// Close database connection
$connect->close();
?>