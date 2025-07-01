<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../connection.php';
session_start();

error_log("favoritesAPI.php called at " . date('Y-m-d H:i:s'));

try {
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        throw new Exception('User not logged in');
    }

    if (!$connect) {
        throw new Exception('Database connection failed');
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    
    switch ($action) {
        case 'add':
            $service_id = $_POST['service_id'] ?? null;
            if (!$service_id) {
                throw new Exception('Service ID is required');
            }
            
            // Check if already exists
            $stmt = $connect->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND service_id = ?");
            $stmt->bind_param("ii", $user_id, $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Already in favorites'
                ]);
                break;
            }
            
            // Add to favorites
            $stmt = $connect->prepare("INSERT INTO user_favorites (user_id, service_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $service_id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Added to favorites'
                ]);
            } else {
                throw new Exception('Failed to add to favorites');
            }
            break;
            
        case 'remove':
            $service_id = $_POST['service_id'] ?? null;
            if (!$service_id) {
                throw new Exception('Service ID is required');
            }
            
            $stmt = $connect->prepare("DELETE FROM user_favorites WHERE user_id = ? AND service_id = ?");
            $stmt->bind_param("ii", $user_id, $service_id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Removed from favorites'
                ]);
            } else {
                throw new Exception('Failed to remove from favorites');
            }
            break;
            
        case 'list':
            $stmt = $connect->prepare("
                SELECT cs.*, uf.created_at as favorited_at,
                       GROUP_CONCAT(DISTINCT CONCAT(sft.flight_type_name, '|', sft.price) SEPARATOR ';;') as flight_types
                FROM user_favorites uf
                JOIN company_services cs ON uf.service_id = cs.id
                LEFT JOIN service_flight_types sft ON cs.id = sft.service_id
                WHERE uf.user_id = ?
                GROUP BY cs.id
                ORDER BY uf.created_at DESC
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $favorites = [];
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
                
                $favorites[] = [
                    'id' => $row['id'],
                    'company_name' => $row['company_name'],
                    'service_title' => $row['service_title'],
                    'service_description' => $row['service_description'],
                    'thumbnail_path' => $row['thumbnail_path'],
                    'flight_types' => $flightTypes,
                    'favorited_at' => $row['favorited_at']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'favorites' => $favorites,
                'count' => count($favorites)
            ]);
            break;
            
        case 'count':
            $stmt = $connect->prepare("SELECT COUNT(*) as count FROM user_favorites WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'count' => (int)$row['count']
            ]);
            break;
            
        case 'check':
            $service_id = $_GET['service_id'] ?? null;
            if (!$service_id) {
                throw new Exception('Service ID is required');
            }
            
            $stmt = $connect->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND service_id = ?");
            $stmt->bind_param("ii", $user_id, $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo json_encode([
                'success' => true,
                'is_favorite' => $result->num_rows > 0
            ]);
            break;
            
        case 'clear_all':
            $stmt = $connect->prepare("DELETE FROM user_favorites WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'All favorites cleared'
                ]);
            } else {
                throw new Exception('Failed to clear favorites');
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    error_log("Exception in favoritesAPI.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}