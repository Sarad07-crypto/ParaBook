<?php

session_start();
require 'Web/php/connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get booking ID from request
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid booking ID'
    ]);
    exit();
}

try {
    // First try to get service_id from bookings table
    $stmt = $db->prepare("
        SELECT service_id, user_id 
        FROM bookings 
        WHERE booking_id = ? AND user_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $db->error);
    }
    
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $service_id = $row['service_id'];
        $stmt->close();
        
        if ($service_id) {
            echo json_encode([
                'success' => true,
                'service_id' => $service_id
            ]);
            exit();
        }
    }
    $stmt->close();
    
    // If no service_id found in bookings, try temp_bookings table
    $stmt = $db->prepare("
        SELECT service_id, user_id 
        FROM temp_bookings 
        WHERE temp_id = ? AND user_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $db->error);
    }
    
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $service_id = $row['service_id'];
        $stmt->close();
        
        if ($service_id) {
            echo json_encode([
                'success' => true,
                'service_id' => $service_id
            ]);
            exit();
        }
    }
    $stmt->close();
    
    // If still no service_id found, try to get it from flight_type_id
    // This assumes you have a way to link flight_type back to service
    $stmt = $db->prepare("
        SELECT b.flight_type_id, b.user_id,
               ft.service_id
        FROM bookings b
        LEFT JOIN flight_types ft ON b.flight_type_id = ft.id
        WHERE b.booking_id = ? AND b.user_id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $service_id = $row['service_id'];
            $stmt->close();
            
            if ($service_id) {
                echo json_encode([
                    'success' => true,
                    'service_id' => $service_id
                ]);
                exit();
            }
        }
        $stmt->close();
    }
    
    // If still no service found, try temp_bookings with flight_type
    $stmt = $db->prepare("
        SELECT tb.flight_type_id, tb.user_id,
               ft.service_id
        FROM temp_bookings tb
        LEFT JOIN flight_types ft ON tb.flight_type_id = ft.id
        WHERE tb.temp_id = ? AND tb.user_id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $service_id = $row['service_id'];
            $stmt->close();
            
            if ($service_id) {
                echo json_encode([
                    'success' => true,
                    'service_id' => $service_id
                ]);
                exit();
            }
        }
        $stmt->close();
    }
    
    // If no service found
    echo json_encode([
        'success' => false,
        'message' => 'Service not found for this booking'
    ]);
    
} catch (Exception $e) {
    error_log("Error in getServiceFromBooking.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}

$db->close();
?>