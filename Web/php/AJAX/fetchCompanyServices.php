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
               GROUP_CONCAT(DISTINCT CONCAT(sft.flight_type_name, '|', COALESCE(sft.price, '0')) SEPARATOR ';;') as flight_types,
               GROUP_CONCAT(DISTINCT sop.photo_path SEPARATOR ';;') as office_photos,
               COALESCE(AVG(CAST(r.rating AS DECIMAL(3,2))), 0) as avg_rating,
               COUNT(DISTINCT r.id) as total_reviews,
               COUNT(DISTINCT CASE WHEN b.status = 'completed' THEN b.booking_id END) as total_flights
        FROM company_services cs
        LEFT JOIN service_flight_types sft ON cs.id = sft.service_id
        LEFT JOIN service_office_photos sop ON cs.id = sop.service_id
        LEFT JOIN reviews r ON cs.id = r.service_id AND r.rating IS NOT NULL AND r.rating > 0
        LEFT JOIN bookings b ON cs.id = b.service_id
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
        // Debug logging
        error_log("Service ID: " . $row['id'] . " - Rating: " . $row['avg_rating'] . " - Flights: " . $row['total_flights']);
        
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

        // Format rating data - ensure proper number conversion
        $avgRating = floatval($row['avg_rating']);
        $totalReviews = intval($row['total_reviews']);
        $totalFlights = intval($row['total_flights']);

        $services[] = [
            'id' => intval($row['id']),
            'company_name' => $row['company_name'] ?? '',
            'service_title' => $row['service_title'] ?? '',
            'service_description' => $row['service_description'] ?? '',
            'thumbnail_path' => $row['thumbnail_path'] ?? '',
            'status' => $row['status'] ?? 'pending',
            'flight_types' => $flightTypes,
            'office_photos' => $officePhotos,
            'avg_rating' => $avgRating,
            'total_reviews' => $totalReviews,
            'total_flights' => $totalFlights,
            'created_at' => $row['created_at'] ?? null
        ];
    }

    // Debug the final response
    error_log("Total services found: " . count($services));
    foreach ($services as $service) {
        error_log("Service: " . $service['company_name'] . " - Rating: " . $service['avg_rating'] . " - Flights: " . $service['total_flights']);
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