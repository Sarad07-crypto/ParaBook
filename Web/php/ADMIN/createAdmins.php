<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session first with error handling
if (session_status() == PHP_SESSION_NONE) {
    if (!session_start()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to start session']);
        exit;
    }
}

// Include database connection with error handling
// Fix the path - use the same pattern as logincheck.php
$connectionPath = dirname(__FILE__) . '/../connection.php';
if (!file_exists($connectionPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection file not found at: ' . $connectionPath]);
    exit;
}

require_once $connectionPath;

// Check if database connection exists
if (!isset($connect) || !$connect) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . (isset($connect) ? mysqli_connect_error() : 'Connection variable not set')]);
    exit;
}

// Define checkUserRole function early
function checkUserRole() {
    // Ensure session is started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
        return [
            'success' => false,
            'message' => 'User not logged in',
            'role' => null,
            'is_main_admin' => false
        ];
    }
    
    $role = $_SESSION['admin_role'];
    
    return [
        'success' => true,
        'role' => $role,
        'is_main_admin' => $role === 'main_admin',
        'admin_id' => $_SESSION['admin_id']
    ];
}

// Check if user is logged in at the top level
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userRole = $_SESSION['admin_role'];
$isMainAdmin = ($userRole === 'main_admin');

// Handle role check request - MUST be before any other output
if (isset($_GET['check_role'])) {
    header('Content-Type: application/json');
    try {
        echo json_encode(checkUserRole());
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error checking user role: ' . $e->getMessage()]);
    }
    exit;
}

// Handle other GET requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];
    
    try {
        switch ($action) {
            case 'getPendingAdmins':
                if (!$isMainAdmin) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Access denied. Only main admins can view pending admins.']);
                    exit;
                }
                echo json_encode(getPendingAdmins($connect));
                break;
                
            case 'getAdminDetails':
                if (!$isMainAdmin) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Access denied. Only main admins can view admin details.']);
                    exit;
                }
                if (isset($_GET['id'])) {
                    echo json_encode(getAdminDetails($connect, $_GET['id']));
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Admin ID required']);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle POST requests for updating admin status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'updateAdminStatus') {
            // Only allow main admins to update admin status
            if (!$isMainAdmin) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied. Only main admins can update admin status.']);
                exit;
            }
            
            $adminId = $_POST['adminId'] ?? null;
            $status = $_POST['status'] ?? null;
            
            if (!$adminId || !$status) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                exit;
            }
            
            // Get the current admin ID from session
            $approvedBy = $_SESSION['admin_id'];
            
            echo json_encode(updateAdminStatus($connect, $adminId, $status, $approvedBy));
            exit;
        }
        
        // Original registration handling
        $result = insertAdmin($connect, $_POST);
        
        if ($result['success']) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                
                echo json_encode([
                    'status' => 'success',
                    'message' => $result['message'],
                    'role' => $result['admin_role'],
                    'admin_id' => $result['admin_id'],
                    'redirect' => '/adminlogin'
                ]);
                
            } else {
                $_SESSION['admin_id'] = $result['admin_id'];
                $_SESSION['admin_role'] = $result['admin_role'];
                $_SESSION['success_message'] = $result['message'];
                
                header('Location: /adminlogin');
                exit();
            }
            
        } else {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                
                // Set appropriate HTTP status code for different error types
                if (strpos($result['message'], 'Email already exists') !== false) {
                    http_response_code(409); // Conflict
                } elseif (strpos($result['message'], 'required') !== false || 
                        strpos($result['message'], 'Invalid') !== false) {
                    http_response_code(400); // Bad Request
                } else {
                    http_response_code(500); // Internal Server Error
                }
                
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['message']
                ]);
            } else {
                $_SESSION['error_message'] = $result['message'];
                header('Location: /adminsignup');
                exit();
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
}

function getPendingAdmins($connect) {
    $conn = $connect;
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed'
        ];
    }
    
    try {
        $query = "SELECT id, first_name, last_name, email, contact, gender, date_of_birth, created_at 
                  FROM admins 
                  WHERE status = 'pending' AND role = 'sub_admin' 
                  ORDER BY created_at DESC";
        
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception('Query failed: ' . mysqli_error($conn));
        }
        
        $admins = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $admins[] = $row;
        }
        
        return [
            'success' => true,
            'data' => $admins
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getAdminDetails($connect, $adminId) {
    $conn = $connect;
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed'
        ];
    }
    
    try {
        // Validate admin ID
        if (!is_numeric($adminId)) {
            throw new Exception('Invalid admin ID');
        }
        
        $query = "SELECT id, first_name, last_name, email, contact, gender, date_of_birth, created_at, status 
                  FROM admins 
                  WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $adminId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            throw new Exception('Query failed: ' . mysqli_error($conn));
        }
        
        $admin = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$admin) {
            throw new Exception('Admin not found');
        }
        
        return [
            'success' => true,
            'data' => $admin
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function updateAdminStatus($connect, $adminId, $status, $approvedBy) {
    $conn = $connect;
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed'
        ];
    }
    
    try {
        // Validate inputs
        if (!is_numeric($adminId) || !is_numeric($approvedBy)) {
            throw new Exception('Invalid admin ID or approver ID');
        }
        
        if (!in_array($status, ['approve', 'reject'])) {
            throw new Exception('Invalid status');
        }
        
        mysqli_begin_transaction($conn);
        
        $newStatus = ($status === 'approve') ? 'approved' : 'rejected';
        $approvedAt = ($status === 'approve') ? date('Y-m-d H:i:s') : null;
        
        $query = "UPDATE admins 
                  SET status = ?, approved_by = ?, approved_at = ? 
                  WHERE id = ? AND status = 'pending'";
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "sisi", $newStatus, $approvedBy, $approvedAt, $adminId);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to update admin status: ' . mysqli_error($conn));
        }
        
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affectedRows === 0) {
            throw new Exception('Admin not found or already processed');
        }
        
        mysqli_commit($conn);
        
        $message = ($status === 'approve') ? 'Admin approved successfully!' : 'Admin rejected successfully!';
        
        return [
            'success' => true,
            'message' => $message
        ];
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function insertAdmin($connect, $formData) {
    $conn = $connect;
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed'
        ];
    }
    
    mysqli_set_charset($conn, "utf8");
    
    try {
        mysqli_begin_transaction($conn);
        
        // Extract and validate form data
        $firstName = isset($formData['firstName']) ? mysqli_real_escape_string($conn, trim($formData['firstName'])) : '';
        $lastName = isset($formData['lastName']) ? mysqli_real_escape_string($conn, trim($formData['lastName'])) : '';
        $email = isset($formData['email']) ? mysqli_real_escape_string($conn, trim($formData['email'])) : '';
        $contact = isset($formData['contact']) ? mysqli_real_escape_string($conn, trim($formData['contact'])) : '';
        $password = isset($formData['password']) ? $formData['password'] : '';
        $dateOfBirth = isset($formData['DOB']) ? mysqli_real_escape_string($conn, $formData['DOB']) : '';
        $gender = isset($formData['gender']) ? mysqli_real_escape_string($conn, $formData['gender']) : '';
        
        // Validation
        if (empty($firstName) || empty($lastName) || empty($email) || 
            empty($contact) || empty($password) || empty($dateOfBirth) || empty($gender)) {
            throw new Exception('All fields are required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        if (!preg_match('/^9\d{9}$/', $contact)) {
            throw new Exception('Invalid contact number format');
        }
        
        if (!preg_match('/^(?!.*\s)(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$&!.])[A-Za-z\d@$&!.]{8,}$/', $password)) {
            throw new Exception('Password does not meet requirements');
        }
        
        if (!in_array($gender, ['Male', 'Female'])) {
            throw new Exception('Invalid gender selection');
        }
        
        // Check if email already exists
        $emailCheckQuery = "SELECT id FROM admins WHERE email = ?";
        $emailCheckStmt = mysqli_prepare($conn, $emailCheckQuery);
        if (!$emailCheckStmt) {
            throw new Exception('Prepare failed: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($emailCheckStmt, "s", $email);
        mysqli_stmt_execute($emailCheckStmt);
        $emailResult = mysqli_stmt_get_result($emailCheckStmt);
        
        if (mysqli_num_rows($emailResult) > 0) {
            mysqli_stmt_close($emailCheckStmt);
            throw new Exception('Email already exists');
        }
        mysqli_stmt_close($emailCheckStmt);
        
        // Check if main admin exists
        $mainAdminQuery = "SELECT COUNT(*) as count FROM admins WHERE role = 'main_admin'";
        $mainAdminResult = mysqli_query($conn, $mainAdminQuery);
        if (!$mainAdminResult) {
            throw new Exception('Query failed: ' . mysqli_error($conn));
        }
        
        $mainAdminRow = mysqli_fetch_assoc($mainAdminResult);
        $hasMainAdmin = $mainAdminRow['count'] > 0;
        
        $role = $hasMainAdmin ? 'sub_admin' : 'main_admin';
        $status = $hasMainAdmin ? 'pending' : 'approved';
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $insertQuery = "INSERT INTO admins (first_name, last_name, email, contact, password, date_of_birth, gender, role, status, approved_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        
        if (!$insertStmt) {
            throw new Exception('Prepare failed: ' . mysqli_error($conn));
        }
        
        $approvedAt = $hasMainAdmin ? null : date('Y-m-d H:i:s');
        
        mysqli_stmt_bind_param($insertStmt, "ssssssssss", 
            $firstName, $lastName, $email, $contact, $hashedPassword, 
            $dateOfBirth, $gender, $role, $status, $approvedAt);
        
        if (!mysqli_stmt_execute($insertStmt)) {
            throw new Exception('Failed to insert admin: ' . mysqli_error($conn));
        }
        
        $adminId = mysqli_insert_id($conn);
        mysqli_stmt_close($insertStmt);
        
        mysqli_commit($conn);
        
        $message = $hasMainAdmin ? 
            'Registration successful! Your account is pending approval by the main admin.' :
            'Registration successful! You have been assigned as the main admin.';
        
        return [
            'success' => true,
            'message' => $message,
            'admin_role' => $role,
            'admin_id' => $adminId
        ];
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Get admin statistics for dashboard
function getAdminStats($connect) {
    $conn = $connect;
    
    if (!$conn) {
        return [
            'pending_admins' => 0,
            'approved_admins' => 0,
            'rejected_admins' => 0,
            'total_admins' => 0
        ];
    }
    
    try {
        $query = "SELECT 
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_admins,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_admins,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_admins,
                    COUNT(*) as total_admins
                  FROM admins";
        
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            return mysqli_fetch_assoc($result);
        }
        
    } catch (Exception $e) {
        // Log error if needed
        error_log("getAdminStats error: " . $e->getMessage());
    }
    
    return [
        'pending_admins' => 0,
        'approved_admins' => 0,
        'rejected_admins' => 0,
        'total_admins' => 0
    ];
}

?>