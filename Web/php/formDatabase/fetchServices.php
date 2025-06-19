<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../connection.php';

session_start();

// Log the request
error_log("fetchServices.php called at " . date('Y-m-d H:i:s'));

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        error_log("User not logged in - Session data: " . print_r($_SESSION, true));
        throw new Exception('User not logged in');
    }

    $user_id = $_SESSION['user_id'];
    error_log("User ID from session: " . $user_id);

    // Verify database connection
    if (!$connect) {
        error_log("Database connection failed");
        throw new Exception('Database connection failed');
    }

    // Verify user is a company account
    $stmt = $connect->prepare("SELECT acc_type FROM users_info WHERE user_id = ?");
    if (!$stmt) {
        error_log("Failed to prepare user verification query: " . $connect->error);
        throw new Exception('Database query preparation failed');
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        error_log("User not found in database for user_id: " . $user_id);
        throw new Exception('User not found');
    }

    error_log("User account type: " . $user['acc_type']);

    if ($user['acc_type'] !== 'company') {
        error_log("User is not a company account. Account type: " . $user['acc_type']);
        throw new Exception('Only company accounts can view services');
    }

    // Fetch all services for this company
    $stmt = $connect->prepare("
        SELECT cs.*, 
               GROUP_CONCAT(DISTINCT CONCAT(sft.flight_type_name, '|', sft.price) SEPARATOR ';;') as flight_types,
               GROUP_CONCAT(DISTINCT sop.photo_path SEPARATOR ';;') as office_photos
        FROM company_services cs
        LEFT JOIN service_flight_types sft ON cs.id = sft.service_id
        LEFT JOIN service_office_photos sop ON cs.id = sop.service_id
        WHERE cs.user_id = ?
        GROUP BY cs.id
        ORDER BY cs.created_at DESC
    ");
    
    if (!$stmt) {
        error_log("Failed to prepare services query: " . $connect->error);
        throw new Exception('Failed to prepare services query');
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        error_log("Failed to execute services query: " . $stmt->error);
        throw new Exception('Failed to execute services query');
    }
    
    $result = $stmt->get_result();
    error_log("Services query executed. Row count: " . $result->num_rows);

    $services = [];
    while ($row = $result->fetch_assoc()) {
        error_log("Processing service: " . $row['service_title']);
        
        // Process flight types
        $flightTypes = [];
        if (!empty($row['flight_types'])) {
            $types = explode(';;', $row['flight_types']);
            foreach ($types as $type) {
                if (strpos($type, '|') !== false) {
                    list($name, $price) = explode('|', $type, 2);
                    $flightTypes[] = [
                        'name' => trim($name),
                        'price' => trim($price)
                    ];
                }
            }
        }
        error_log("Flight types for service " . $row['service_title'] . ": " . count($flightTypes));

        // Process office photos
        $officePhotos = [];
        if (!empty($row['office_photos'])) {
            $photos = explode(';;', $row['office_photos']);
            foreach ($photos as $photo) {
                if (!empty(trim($photo))) {
                    $officePhotos[] = trim($photo);
                }
            }
        }
        error_log("Office photos for service " . $row['service_title'] . ": " . count($officePhotos));

        $services[] = [
            'id' => $row['id'],
            'company_name' => $row['company_name'] ?? '',
            'service_title' => $row['service_title'] ?? '',
            'service_description' => $row['service_description'] ?? '',
            'thumbnail_path' => $row['thumbnail_path'] ?? '',
            'flight_types' => $flightTypes,
            'office_photos' => $officePhotos,
            'created_at' => $row['created_at'] ?? null
        ];
    }

    error_log("Final services count: " . count($services));

    $response = [
        'success' => true,
        'services' => $services,
        'count' => count($services),
        'user_id' => $user_id
    ];

    error_log("Sending response: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Exception in fetchServices.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $errorResponse = [
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($errorResponse);
} catch (Error $e) {
    error_log("Fatal error in fetchServices.php: " . $e->getMessage());
    
    $errorResponse = [
        'success' => false,
        'message' => 'A fatal error occurred: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($errorResponse);
}
?>