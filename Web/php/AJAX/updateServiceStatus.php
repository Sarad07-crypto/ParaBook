<?php
// Enable error logging and display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');

// Set content type to JSON
header('Content-Type: application/json');

// Start output buffering
ob_start();

// Log incoming request for debugging
error_log('=== updateServiceStatus.php Debug ===');
error_log('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
error_log('POST data: ' . print_r($_POST, true));

try {
    // Clean any output
    ob_clean();
    
    // Include database connection
    require '../connection.php';
    
    if (!isset($connect)) {
        throw new Exception('Database connection variable $conn not defined');
    }
    
    if (!$connect) {
        throw new Exception('Database connection failed: ' . (isset($mysqli_connect_error) ? mysqli_connect_error() : 'Unknown error'));
    }
    
    // Test database connection
    if (mysqli_connect_errno()) {
        throw new Exception('Database connection error: ' . mysqli_connect_error());
    }
    
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
    }
    
    // Check action
    if (!isset($_POST['action']) || $_POST['action'] !== 'updateServiceStatus') {
        throw new Exception('Invalid action: ' . (isset($_POST['action']) ? $_POST['action'] : 'not set'));
    }
    
    // Debug: Log what we received
    error_log('serviceId from POST: ' . (isset($_POST['serviceId']) ? $_POST['serviceId'] : 'NOT SET'));
    error_log('status from POST: ' . (isset($_POST['status']) ? $_POST['status'] : 'NOT SET'));
    error_log('reason from POST: ' . (isset($_POST['reason']) ? $_POST['reason'] : 'NOT SET'));
    
    // Validate required fields
    if (!isset($_POST['serviceId']) || !isset($_POST['status'])) {
        throw new Exception('Missing required fields - serviceId: ' . 
            (isset($_POST['serviceId']) ? 'present' : 'missing') . 
            ', status: ' . (isset($_POST['status']) ? 'present' : 'missing'));
    }
    
    $serviceId = intval($_POST['serviceId']);
    $status = trim($_POST['status']);
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    
    error_log('Processed values - serviceId: ' . $serviceId . ', status: ' . $status . ', reason: ' . $reason);
    
    // Validate service ID
    if ($serviceId <= 0) {
        throw new Exception('Invalid service ID: ' . $serviceId . ' (original: ' . $_POST['serviceId'] . ')');
    }
    
    // Validate status
    $allowedStatuses = ['pending', 'approved', 'rejected'];
    if (!in_array($status, $allowedStatuses)) {
        throw new Exception('Invalid status value: ' . $status);
    }
    
    // If status is rejected, reason should be provided
    if ($status === 'rejected' && empty($reason)) {
        throw new Exception('Rejection reason is required');
    }
    
    // Check if company_services table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'company_services'";
    $tableCheckResult = mysqli_query($connect, $tableCheckQuery);
    if (!$tableCheckResult || mysqli_num_rows($tableCheckResult) == 0) {
        throw new Exception('Table company_services does not exist');
    }
    
    // Check if service exists
    $checkQuery = "SELECT id, status, company_name, service_title FROM company_services WHERE id = ?";
    $checkStmt = mysqli_prepare($connect, $checkQuery);
    
    if (!$checkStmt) {
        throw new Exception('Database prepare failed: ' . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($checkStmt, 'i', $serviceId);
    $checkResult = mysqli_stmt_execute($checkStmt);
    
    if (!$checkResult) {
        throw new Exception('Database query failed: ' . mysqli_stmt_error($checkStmt));
    }
    
    $result = mysqli_stmt_get_result($checkStmt);
    $service = mysqli_fetch_assoc($result);
    
    error_log('Service found: ' . ($service ? 'YES' : 'NO'));
    if ($service) {
        error_log('Service details: ' . print_r($service, true));
    }
    
    if (!$service) {
        mysqli_stmt_close($checkStmt);
        throw new Exception('Service not found with ID: ' . $serviceId);
    }
    
    // Check if service is already in the target status
    if ($service['status'] === $status) {
        mysqli_stmt_close($checkStmt);
        throw new Exception('Service is already ' . $status);
    }
    
    mysqli_stmt_close($checkStmt);
    
    // Update service status
    $updateQuery = "UPDATE company_services SET status = ?, updated_at = NOW() WHERE id = ?";
    $updateStmt = mysqli_prepare($connect, $updateQuery);
    
    if (!$updateStmt) {
        throw new Exception('Database prepare failed: ' . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($updateStmt, 'si', $status, $serviceId);
    $updateResult = mysqli_stmt_execute($updateStmt);
    
    if ($updateResult) {
        error_log('Service status updated successfully');
        
        // Log the status change if reason provided (optional table)
        if (!empty($reason)) {
            $logQuery = "INSERT INTO service_status_logs (service_id, old_status, new_status, reason, changed_at) 
                         VALUES (?, ?, ?, ?, NOW())";
            
            $logStmt = mysqli_prepare($connect, $logQuery);
            if ($logStmt) {
                mysqli_stmt_bind_param($logStmt, 'isss', $serviceId, $service['status'], $status, $reason);
                if (!mysqli_stmt_execute($logStmt)) {
                    error_log('Failed to log status change: ' . mysqli_stmt_error($logStmt));
                }
                mysqli_stmt_close($logStmt);
            } else {
                error_log('Failed to prepare log statement (service_status_logs table may not exist): ' . mysqli_error($connect));
            }
        }
        
        // Prepare success message
        $statusText = ucfirst($status);
        $companyName = $service['company_name'];
        $serviceTitle = $service['service_title'];
        
        $message = "Service '{$serviceTitle}' by {$companyName} has been {$statusText} successfully.";
        
        if ($status === 'rejected' && !empty($reason)) {
            $message .= " Reason: {$reason}";
        }
        
        mysqli_stmt_close($updateStmt);
        
        // Clean output buffer and return success response
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => [
                'serviceId' => $serviceId,
                'oldStatus' => $service['status'],
                'newStatus' => $status,
                'reason' => $reason
            ]
        ]);
        
        error_log('Success response sent');
        
    } else {
        mysqli_stmt_close($updateStmt);
        throw new Exception('Failed to update service status: ' . mysqli_stmt_error($updateStmt));
    }
    
} catch (Exception $e) {
    // Log the error
    error_log('Error in updateServiceStatus: ' . $e->getMessage());
    
    // Clean output buffer and return error response
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => __LINE__,
            'error' => $e->getMessage()
        ]
    ]);
} catch (Error $e) {
    // Catch fatal errors
    error_log('Fatal error in updateServiceStatus: ' . $e->getMessage());
    
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} finally {
    // Close database connection
    if (isset($connect) && $connect) {
        mysqli_close($connect);
    }
}

// End output buffering
ob_end_flush();
?>