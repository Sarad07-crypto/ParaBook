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

    // First, get all services for this company
    $stmt = $connect->prepare("
        SELECT cs.id, cs.user_id, cs.company_name, cs.service_title, cs.address, 
               cs.contact, cs.pan_number, cs.service_description, cs.thumbnail_path, 
               cs.status, cs.created_at, cs.updated_at
        FROM company_services cs
        WHERE cs.user_id = ?
        ORDER BY 
            CASE cs.status 
                WHEN 'approved' THEN 1
                WHEN 'pending' THEN 2
                WHEN 'rejected' THEN 3
                ELSE 4
            END,
            cs.created_at DESC
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
    $statusCounts = [
        'approved' => 0,
        'pending' => 0,
        'rejected' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        error_log("Processing service: " . $row['service_title'] . " with status: " . ($row['status'] ?? 'NULL'));
        
        $service_id = $row['id'];
        
        // Get flight types for this service
        $flightTypesStmt = $connect->prepare("
            SELECT flight_type_name, price 
            FROM service_flight_types 
            WHERE service_id = ?
        ");
        
        $flightTypes = [];
        if ($flightTypesStmt) {
            $flightTypesStmt->bind_param("i", $service_id);
            $flightTypesStmt->execute();
            $flightResult = $flightTypesStmt->get_result();
            
            while ($flightRow = $flightResult->fetch_assoc()) {
                $flightTypes[] = [
                    'name' => $flightRow['flight_type_name'],
                    'price' => $flightRow['price']
                ];
            }
            $flightTypesStmt->close();
        }
        
        // Get office photos for this service
        $photosStmt = $connect->prepare("
            SELECT photo_path 
            FROM service_office_photos 
            WHERE service_id = ?
        ");
        
        $officePhotos = [];
        if ($photosStmt) {
            $photosStmt->bind_param("i", $service_id);
            $photosStmt->execute();
            $photoResult = $photosStmt->get_result();
            
            while ($photoRow = $photoResult->fetch_assoc()) {
                if (!empty(trim($photoRow['photo_path']))) {
                    $officePhotos[] = trim($photoRow['photo_path']);
                }
            }
            $photosStmt->close();
        }
        
        error_log("Flight types for service " . $row['service_title'] . ": " . count($flightTypes));
        error_log("Office photos for service " . $row['service_title'] . ": " . count($officePhotos));

        // Get status (default to pending if not set)
        $status = $row['status'] ?? 'pending';
        $statusCounts[$status]++;

        $services[] = [
            'id' => $row['id'],
            'company_name' => $row['company_name'] ?? '',
            'service_title' => $row['service_title'] ?? '',
            'service_description' => $row['service_description'] ?? '',
            'thumbnail_path' => $row['thumbnail_path'] ?? '',
            'status' => $status,
            'flight_types' => $flightTypes,
            'office_photos' => $officePhotos,
            'created_at' => $row['created_at'] ?? null
        ];
    }

    error_log("Final services count: " . count($services));
    error_log("Status counts: " . json_encode($statusCounts));

    $response = [
        'success' => true,
        'services' => $services,
        'count' => count($services),
        'status_counts' => $statusCounts,
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