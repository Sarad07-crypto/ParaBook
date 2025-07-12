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

    if (!$connect) {
        error_log("Database connection failed");
        throw new Exception('Database connection failed');
    }

    // For admin dashboard, fetch basic service info for listing including location data
    $query = "
        SELECT cs.id, cs.service_title, cs.company_name, cs.status, cs.created_at,
               sl.latitude, sl.longitude, sl.address, sl.formatted_address, sl.place_id
        FROM company_services cs
        LEFT JOIN service_locations sl ON cs.id = sl.service_id
        ORDER BY cs.created_at DESC
    ";

    $stmt = $connect->prepare($query);
    if (!$stmt) throw new Exception("Failed to prepare services query");

    if (!$stmt->execute()) throw new Exception("Failed to execute service fetch query");
    $result = $stmt->get_result();

    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = [
            'id' => $row['id'],
            'service_title' => $row['service_title'] ?? '',
            'company_name' => $row['company_name'] ?? '',
            'status' => $row['status'] ?? 'pending',
            'created_at' => $row['created_at'] ?? null,
            'location' => [
                'latitude' => $row['latitude'] ? floatval($row['latitude']) : null,
                'longitude' => $row['longitude'] ? floatval($row['longitude']) : null,
                'address' => $row['address'] ?? null,
                'formatted_address' => $row['formatted_address'] ?? null,
                'place_id' => $row['place_id'] ?? null
            ]
        ];
    }

    echo json_encode([
        'success' => true,
        'services' => $services,
        'count' => count($services),
        'admin_role' => $admin_role
    ]);

} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>