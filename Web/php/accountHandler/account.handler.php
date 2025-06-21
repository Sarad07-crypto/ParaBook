<?php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type before any output
header('Content-Type: application/json');

// Debug logging
error_log("Account handler called. Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

try {
    // Include your controller - adjust path as needed
    $controller_path = __DIR__ . '/../../google/core/controller.class.php';
    
    // Check if file exists before including
    if (!file_exists($controller_path)) {
        // Try alternative paths
        $alt_paths = [
            'Web/php/google/core/controller.class.php',
            '../google/core/controller.class.php',
            '../../google/core/controller.class.php',
            dirname(__FILE__) . '/../../google/core/controller.class.php'
        ];
        
        $found = false;
        foreach ($alt_paths as $path) {
            if (file_exists($path)) {
                $controller_path = $path;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            throw new Exception("Controller file not found. Searched paths: " . implode(', ', array_merge([$controller_path], $alt_paths)));
        }
    }
    
    require_once $controller_path;
    error_log("Controller loaded successfully from: " . $controller_path);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            error_log("Action received: " . $_POST['action']);
            
            switch ($_POST['action']) {
                case 'set_account_type':
                    if (isset($_POST['acc_type']) && in_array($_POST['acc_type'], ['passenger', 'company'])) {
                        $_SESSION['selected_acc_type'] = $_POST['acc_type'];
                        
                        // Force session write
                        session_write_close();
                        session_start(); // Restart session for continued use
                        
                        error_log("Account type set to: " . $_POST['acc_type']);
                        error_log("Session after setting acc_type: " . print_r($_SESSION, true));
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Account type selected successfully'
                        ]);
                    } else {
                        error_log("Invalid account type: " . ($_POST['acc_type'] ?? 'not set'));
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid account type selected'
                        ]);
                    }
                    break;

                case 'complete_registration':
                    error_log("Attempting to complete registration");
                    
                    // Check if Controller class exists
                    if (!class_exists('Controller')) {
                        throw new Exception("Controller class not found");
                    }
                    
                    $controller = new Controller();
                    
                    // Check if method exists
                    if (!method_exists($controller, 'completeRegistrationWithAccountType')) {
                        throw new Exception("Method completeRegistrationWithAccountType not found in Controller class");
                    }
                    if (!isset($_SESSION['selected_acc_type'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Account type not set. Please complete account selection.'
                        ]);
                        exit;
                    }
                    $controller->completeRegistrationWithAccountType();
                    break;

                case 'cancel_selection':
                    error_log("Cancelling selection");
                    // Clear temporary user data and selected account type
                    unset($_SESSION['temp_user_data']);
                    unset($_SESSION['selected_acc_type']);
                    unset($_SESSION['show_account_selection']);
                    
                    // Force session write
                    session_write_close();
                    session_start();
                    
                    error_log("Session after cancellation: " . print_r($_SESSION, true));
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Selection cancelled',
                        'redirect_url' => '/login'
                    ]);
                    break;

                default:
                    error_log("Unknown action: " . $_POST['action']);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid action'
                    ]);
                    break;
            }
        } else {
            error_log("No action specified in POST request");
            echo json_encode([
                'success' => false,
                'message' => 'No action specified'
            ]);
        }
    } else {
        error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
    }

} catch (Exception $e) {
    error_log("Exception in account handler: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
    
    // Clean any output buffer to ensure clean JSON response
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} catch (Error $e) {
    error_log("Fatal error in account handler: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
    
    // Clean any output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}

?>