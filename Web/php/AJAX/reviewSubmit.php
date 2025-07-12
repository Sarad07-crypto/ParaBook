<?php
// submit_review.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require '../connection.php';

// Set JSON response headers
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

$userId = $_SESSION['user_id'];
$serviceId = $input['service_id'] ?? null;
$rating = $input['rating'] ?? null;
$reviewText = trim($input['review_text'] ?? '');

// Validate inputs
if (!$serviceId || !$rating || !$reviewText) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Validate rating range
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit();
}

// Validate review text length
if (strlen($reviewText) < 10) {
    echo json_encode(['success' => false, 'message' => 'Review must be at least 10 characters long']);
    exit();
}

if (strlen($reviewText) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Review cannot exceed 1000 characters']);
    exit();
}

try {
    // Check if user can review this service (has completed booking)
    $checkStmt = $connect->prepare("
        SELECT COUNT(*) as booking_count 
        FROM bookings 
        WHERE user_id = ? AND service_id = ? AND status = 'completed'
    ");
    
    if (!$checkStmt) {
        throw new Exception("Failed to prepare booking check query");
    }
    
    $checkStmt->bind_param("ii", $userId, $serviceId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkRow = $checkResult->fetch_assoc();
    $checkStmt->close();
    
    if ($checkRow['booking_count'] == 0) {
        echo json_encode(['success' => false, 'message' => 'You can only review services you have completed bookings for']);
        exit();
    }
    
    // Check if service exists
    $serviceStmt = $connect->prepare("SELECT id FROM company_services WHERE id = ?");
    if (!$serviceStmt) {
        throw new Exception("Failed to prepare service check query");
    }
    
    $serviceStmt->bind_param("i", $serviceId);
    $serviceStmt->execute();
    $serviceResult = $serviceStmt->get_result();
    
    if ($serviceResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Service not found']);
        exit();
    }
    $serviceStmt->close();
    
    // Insert or update review (using ON DUPLICATE KEY UPDATE for the unique constraint)
    $stmt = $connect->prepare("
        INSERT INTO reviews (user_id, service_id, rating, review_text, created_at) 
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        rating = VALUES(rating), 
        review_text = VALUES(review_text), 
        updated_at = NOW()
    ");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare review insert query");
    }
    
    $stmt->bind_param("iiis", $userId, $serviceId, $rating, $reviewText);
    
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
    } else {
        throw new Exception("Failed to execute review insert query");
    }
    
} catch (Exception $e) {
    error_log("Review submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to submit review. Please try again.']);
}
?>