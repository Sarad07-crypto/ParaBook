<?php
// File: Web/php/AJAX/cleanupTempBookings.php
session_start();

try {
    include "../connection.php";

    // Delete expired temporary bookings (older than 1 hour and not paid)
    $cleanupStmt = $connect->prepare("
        DELETE FROM temp_bookings 
        WHERE (expires_at < NOW() OR payment_status = 'expired') 
        AND payment_status != 'paid'
    ");
    
    if ($cleanupStmt) {
        $cleanupStmt->execute();
        $deletedRows = $cleanupStmt->affected_rows;
        $cleanupStmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => "Cleaned up $deletedRows expired bookings"
        ]);
    } else {
        throw new Exception('Database error: ' . $connect->error);
    }

} catch (Exception $e) {
    error_log("Cleanup error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Cleanup failed: ' . $e->getMessage()
    ]);
} finally {
    if (isset($connect) && $connect) {
        $connect->close();
    }
}
?>