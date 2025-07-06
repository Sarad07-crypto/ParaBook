<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../connection.php';
session_start();

try {
    // Check if user is logged in as admin
    $admin_id = $_SESSION['admin_id'] ?? null;
    $admin_role = $_SESSION['admin_role'] ?? null;
    
    if (!$admin_id || !$admin_role) {
        throw new Exception('Admin authentication required');
    }

    $service_id = $_GET['id'] ?? null;
    if (!$service_id) {
        throw new Exception('Service ID is required');
    }

    if (!$connect) {
        throw new Exception('Database connection failed');
    }

    // Fetch detailed service information
    $query = "
        SELECT cs.*, 
               GROUP_CONCAT(DISTINCT CONCAT(sft.flight_type_name, '|', sft.price) SEPARATOR ';;') as flight_types,
               GROUP_CONCAT(DISTINCT sop.photo_path SEPARATOR ';;') as office_photos
        FROM company_services cs
        LEFT JOIN service_flight_types sft ON cs.id = sft.service_id
        LEFT JOIN service_office_photos sop ON cs.id = sop.service_id
        WHERE cs.id = ?
        GROUP BY cs.id
    ";

    $stmt = $connect->prepare($query);
    if (!$stmt) throw new Exception("Failed to prepare service details query");
    
    $stmt->bind_param("i", $service_id);
    if (!$stmt->execute()) throw new Exception("Failed to execute service details query");
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        throw new Exception('Service not found');
    }

    // Parse flight types
    $flightTypes = [];
    if (!empty($row['flight_types'])) {
        foreach (explode(';;', $row['flight_types']) as $type) {
            if (strpos($type, '|') !== false) {
                list($name, $price) = explode('|', $type, 2);
                $flightTypes[] = [
                    'name' => trim($name),
                    'price' => trim($price)
                ];
            }
        }
    }

    // Parse photos
    $officePhotos = [];
    if (!empty($row['office_photos'])) {
        foreach (explode(';;', $row['office_photos']) as $photo) {
            if (trim($photo) !== '') {
                $officePhotos[] = trim($photo);
            }
        }
    }

    $service = [
        'id' => $row['id'],
        'company_name' => $row['company_name'] ?? '',
        'service_title' => $row['service_title'] ?? '',
        'service_description' => $row['service_description'] ?? '',
        'thumbnail_path' => $row['thumbnail_path'] ?? '',
        'status' => $row['status'] ?? 'pending',
        'flight_types' => $flightTypes,
        'office_photos' => $officePhotos,
        'created_at' => $row['created_at'] ?? null,
        'address' => $row['address'] ?? '',
        'contact' => $row['contact'] ?? ''
    ];

    echo json_encode([
        'success' => true,
        'service' => $service
    ]);

} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>