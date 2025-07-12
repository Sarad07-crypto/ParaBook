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

// Function to check if any admin exists in the system
function hasAnyAdmin($connect) {
    $conn = $connect;
    
    if (!$conn) {
        error_log("DEBUG: hasAnyAdmin - No database connection");
        return false;
    }
    
    try {
        $query = "SELECT COUNT(*) as count FROM admins WHERE status = 'approved'";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            error_log("DEBUG: hasAnyAdmin - Found " . $row['count'] . " approved admins");
            return $row['count'] > 0;
        }
        
    } catch (Exception $e) {
        error_log("hasAnyAdmin error: " . $e->getMessage());
    }
    
    error_log("DEBUG: hasAnyAdmin - Returning false (no admins found)");
    return false;
}

// Check if user needs to be logged in based on whether any admin exists
$hasExistingAdmin = hasAnyAdmin($connect);
$userRole = null;
$isMainAdmin = false;

if ($hasExistingAdmin) {
    // If admins exist, require login for certain operations
    if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role'])) {
        $userRole = $_SESSION['admin_role'];
        $isMainAdmin = ($userRole === 'main_admin');
    }
} else {
    // If no admins exist, allow registration without login
    $userRole = null;
    $isMainAdmin = false;
}

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
    
    // These actions require login and main admin privileges
    if (!$hasExistingAdmin || !isset($_SESSION['admin_id']) || !$isMainAdmin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied. Only main admins can perform this action.']);
        exit;
    }
    
    try {
        switch ($action) {
            case 'getPendingAdmins':
                echo json_encode(getPendingAdmins($connect));
                break;
                
            case 'getAdminDetails':
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

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'updateAdminStatus') {
            // Only allow main admins to update admin status
            if (!$hasExistingAdmin || !isset($_SESSION['admin_id']) || !$isMainAdmin) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied. Only main admins can update admin status.']);
                exit;
            }
            
            $adminId = $_POST['adminId'] ?? null;
            $status = $_POST['status'] ?? null;
            $newRole = $_POST['role'] ?? null;
            
            if (!$adminId || !$status) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                exit;
            }
            
            $approvedBy = $_SESSION['admin_id'];
            echo json_encode(updateAdminStatus($connect, $adminId, $status, $approvedBy, $newRole));
            exit;
        }
        
        // Handle admin registration - THIS IS THE MAIN FIX
        // Check if this is a registration request (no 'action' parameter or different action)
        if (!isset($_POST['action']) || $_POST['action'] !== 'updateAdminStatus') {
            $result = insertAdmin($connect, $_POST);
            echo json_encode($result); // Return the result directly as insertAdmin already returns proper format
            exit;
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
        if (!is_numeric($adminId)) {
            throw new Exception('Invalid admin ID');
        }
        
        $query = "SELECT id, first_name, last_name, email, contact, gender, date_of_birth, created_at, status, role 
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

function updateAdminStatus($connect, $adminId, $status, $approvedBy, $newRole = null) {
    $conn = $connect;
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed'
        ];
    }
    
    try {
        if (!is_numeric($adminId) || !is_numeric($approvedBy)) {
            throw new Exception('Invalid admin ID or approver ID');
        }
        
        if (!in_array($status, ['approve', 'reject'])) {
            throw new Exception('Invalid status');
        }
        
        if ($newRole !== null && !in_array($newRole, ['main_admin', 'sub_admin'])) {
            throw new Exception('Invalid role: ' . $newRole);
        }
        
        mysqli_begin_transaction($conn);
        
        $newStatus = ($status === 'approve') ? 'approved' : 'rejected';
        $approvedAt = ($status === 'approve') ? date('Y-m-d H:i:s') : null;
        
        if ($newRole !== null && $status === 'approve') {
            $query = "UPDATE admins 
                      SET status = ?, approved_by = ?, approved_at = ?, role = ? 
                      WHERE id = ? AND status = 'pending'";
            
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "sissi", $newStatus, $approvedBy, $approvedAt, $newRole, $adminId);
        } else {
            $query = "UPDATE admins 
                      SET status = ?, approved_by = ?, approved_at = ? 
                      WHERE id = ? AND status = 'pending'";
            
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "sisi", $newStatus, $approvedBy, $approvedAt, $adminId);
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to update admin status: ' . mysqli_error($conn));
        }
        
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affectedRows === 0) {
            throw new Exception('Admin not found or already processed');
        }
        
        mysqli_commit($conn);
        
        if ($status === 'approve') {
            $message = 'Admin approved successfully!';
            if ($newRole !== null) {
                $roleDisplayName = str_replace('_', ' ', strtoupper($newRole));
                $message .= ' Role set to ' . $roleDisplayName . '.';
            }
        } else {
            $message = 'Admin rejected successfully!';
        }
        
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
    
    try {
        // Extract form data
        $firstName = trim($formData['firstName']);
        $lastName = trim($formData['lastName']);
        $email = trim($formData['email']);
        $contact = trim($formData['contact']);
        $password = $formData['password'];
        $dateOfBirth = $formData['DOB'];
        $gender = $formData['gender'];
        
        // Debug logging
        error_log("DEBUG: Attempting to register admin with email: " . $email);
        
        // Basic validation
        if (empty($firstName) || empty($lastName) || empty($email) || 
            empty($contact) || empty($password) || empty($dateOfBirth) || empty($gender)) {
            throw new Exception('All fields are required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Start transaction to ensure atomicity
        mysqli_begin_transaction($conn);
        
        // Check if email already exists - BEFORE any insert
        $checkEmailQuery = "SELECT email FROM admins WHERE email = ?";
        $checkStmt = mysqli_prepare($conn, $checkEmailQuery);
        if (!$checkStmt) {
            mysqli_rollback($conn);
            throw new Exception('Failed to prepare email check query: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $result = mysqli_stmt_get_result($checkStmt);
        
        // Check if any rows were returned
        if (mysqli_num_rows($result) > 0) {
            mysqli_stmt_close($checkStmt);
            mysqli_rollback($conn);
            
            // Get the existing email for debugging
            $existingEmail = mysqli_fetch_assoc($result)['email'];
            error_log("DEBUG: Email already exists in database: " . $existingEmail);
            
            throw new Exception('Email already exists. Please use a different email address.');
        }
        
        mysqli_stmt_close($checkStmt);
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Determine role and status based on whether any admin exists
        $hasExistingAdmin = hasAnyAdmin($connect);
        $role = $hasExistingAdmin ? 'sub_admin' : 'main_admin';
        $status = $hasExistingAdmin ? 'pending' : 'approved';
        $approvedAt = null;
        
        if (!$hasExistingAdmin) {
            // First admin - auto-approve
            $approvedAt = date('Y-m-d H:i:s');
        }
        
        // Insert admin with role and status
        if ($approvedAt !== null) {
            $insertQuery = "INSERT INTO admins (first_name, last_name, email, contact, password, date_of_birth, gender, role, status, approved_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            
            if (!$insertStmt) {
                mysqli_rollback($conn);
                throw new Exception('Failed to prepare insert statement: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($insertStmt, "ssssssssss", 
                $firstName, $lastName, $email, $contact, $hashedPassword, $dateOfBirth, $gender, $role, $status, $approvedAt);
        } else {
            // For pending admins, don't set approved_at
            $insertQuery = "INSERT INTO admins (first_name, last_name, email, contact, password, date_of_birth, gender, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            
            if (!$insertStmt) {
                mysqli_rollback($conn);
                throw new Exception('Failed to prepare insert statement: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($insertStmt, "sssssssss", 
                $firstName, $lastName, $email, $contact, $hashedPassword, $dateOfBirth, $gender, $role, $status);
        }
        
        if (!mysqli_stmt_execute($insertStmt)) {
            mysqli_rollback($conn);
            throw new Exception('Failed to insert admin: ' . mysqli_error($conn));
        }
        
        $adminId = mysqli_insert_id($conn);
        mysqli_stmt_close($insertStmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        error_log("DEBUG: Admin successfully registered with ID: " . $adminId);
        
        $message = $hasExistingAdmin ? 
            'Admin registration submitted successfully. Awaiting approval from main admin.' : 
            'Admin registered successfully and approved automatically.';
        
        return [
            'success' => true,
            'status' => 'success',
            'message' => $message,
            'admin_id' => $adminId,
            'redirect' => '/adminlogin'
        ];
        
    } catch (Exception $e) {
        // Rollback transaction on any error
        if (isset($conn)) {
            mysqli_rollback($conn);
        }
        
        error_log("DEBUG: Error in insertAdmin: " . $e->getMessage());
        
        return [
            'success' => false,
            'status' => 'error',
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