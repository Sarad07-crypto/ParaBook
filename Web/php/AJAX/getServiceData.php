<?php
require_once '../connection.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['service_id'])) {
        throw new Exception('No service_id provided');
    }

    $serviceId = intval($_GET['service_id']);

    // 1. Fetch service
    $stmt = $connect->prepare("SELECT * FROM company_services WHERE id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();

    if (!$service) {
        throw new Exception("Service not found");
    }

    // 2. Fetch flight types
    $stmt = $connect->prepare("SELECT flight_type_name AS name, price FROM service_flight_types WHERE service_id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $flightTypes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // 3. Fetch office photos
    $stmt = $connect->prepare("SELECT photo_path FROM service_office_photos WHERE service_id = ? ORDER BY photo_order ASC");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();

    $officePhotos = [];
    while ($row = $result->fetch_assoc()) {
        $officePhotos[] = $row['photo_path'];
    }

    // 4. Return data
    echo json_encode([
        'success' => true,
        'data' => [
            'service' => $service,
            'flightTypes' => $flightTypes,
            'officePhotos' => $officePhotos
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}