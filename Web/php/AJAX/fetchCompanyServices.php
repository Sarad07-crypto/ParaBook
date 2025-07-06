<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../connection.php';
session_start();

try {
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$connect) {
        error_log("Database connection failed");
        throw new Exception('Database connection failed');
    }

    $acc_type = 'passenger';
    if ($user_id) {
        $stmt = $connect->prepare("SELECT acc_type FROM users_info WHERE user_id = ?");
        if (!$stmt) throw new Exception("Failed to prepare acc_type query");

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $acc_type = $user['acc_type'] ?? 'passenger';
    }

    $query = "
        SELECT cs.*, 
               GROUP_CONCAT(DISTINCT CONCAT(sft.flight_type_name, '|', sft.price) SEPARATOR ';;') as flight_types,
               GROUP_CONCAT(DISTINCT sop.photo_path SEPARATOR ';;') as office_photos
        FROM company_services cs
        LEFT JOIN service_flight_types sft ON cs.id = sft.service_id
        LEFT JOIN service_office_photos sop ON cs.id = sop.service_id
    ";

    // Filter by company user_id if it's a company, otherwise show only approved services
    if ($acc_type === 'company') {
        // Company users can see all their own services (pending, approved, rejected)
        $query .= " WHERE cs.user_id = ? GROUP BY cs.id ORDER BY cs.created_at DESC";
        $stmt = $connect->prepare($query);
        if (!$stmt) throw new Exception("Failed to prepare company-only services query");
        $stmt->bind_param("i", $user_id);
    } else {
        // Passengers can only see approved services
        $query .= " WHERE cs.status = 'approved' GROUP BY cs.id ORDER BY cs.created_at DESC";
        $stmt = $connect->prepare($query);
        if (!$stmt) throw new Exception("Failed to prepare passenger services query");
    }

    if (!$stmt->execute()) throw new Exception("Failed to execute service fetch query");
    $result = $stmt->get_result();

    $services = [];
    while ($row = $result->fetch_assoc()) {
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

        $services[] = [
            'id' => $row['id'],
            'company_name' => $row['company_name'] ?? '',
            'service_title' => $row['service_title'] ?? '',
            'service_description' => $row['service_description'] ?? '',
            'thumbnail_path' => $row['thumbnail_path'] ?? '',
            'status' => $row['status'] ?? 'pending', // Include status in response
            'flight_types' => $flightTypes,
            'office_photos' => $officePhotos,
            'created_at' => $row['created_at'] ?? null
        ];
    }

    echo json_encode([
        'success' => true,
        'services' => $services,
        'count' => count($services),
        'acc_type' => $acc_type
    ]);

} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>