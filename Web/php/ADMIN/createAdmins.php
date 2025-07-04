<?php

function insertAdmin($connect, $formData) {
    // Use the existing connection
    $conn = $connect;
    
    // Check connection
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed: ' . mysqli_connect_error()
        ];
    }
    
    // Set charset
    mysqli_set_charset($conn, "utf8");
    
    try {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        // Extract and sanitize form data
        $firstName = mysqli_real_escape_string($conn, trim($formData['firstName']));
        $lastName = mysqli_real_escape_string($conn, trim($formData['lastName']));
        $email = mysqli_real_escape_string($conn, trim($formData['email']));
        $contact = mysqli_real_escape_string($conn, trim($formData['contact']));
        $password = $formData['password']; // Will be hashed
        $dateOfBirth = mysqli_real_escape_string($conn, $formData['DOB']);
        $gender = mysqli_real_escape_string($conn, $formData['gender']);
        
        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($email) || 
            empty($contact) || empty($password) || empty($dateOfBirth) || empty($gender)) {
            throw new Exception('All fields are required');
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Validate contact number (Nepal format: 9XXXXXXXXX)
        if (!preg_match('/^9\d{9}$/', $contact)) {
            throw new Exception('Invalid contact number format');
        }
        
        // Validate password strength
        if (!preg_match('/^(?!.*\s)(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$&!.])[A-Za-z\d@$&!.]{8,}$/', $password)) {
            throw new Exception('Password does not meet requirements');
        }
        
        // Validate gender
        if (!in_array($gender, ['Male', 'Female'])) {
            throw new Exception('Invalid gender selection');
        }
        
        // Check if email already exists
        $emailCheckQuery = "SELECT id FROM admins WHERE email = ?";
        $emailCheckStmt = mysqli_prepare($conn, $emailCheckQuery);
        mysqli_stmt_bind_param($emailCheckStmt, "s", $email);
        mysqli_stmt_execute($emailCheckStmt);
        $emailResult = mysqli_stmt_get_result($emailCheckStmt);
        
        if (mysqli_num_rows($emailResult) > 0) {
            throw new Exception('Email already exists');
        }
        
        // Check if there's already a main admin
        $mainAdminQuery = "SELECT COUNT(*) as count FROM admins WHERE role = 'main_admin'";
        $mainAdminResult = mysqli_query($conn, $mainAdminQuery);
        $mainAdminRow = mysqli_fetch_assoc($mainAdminResult);
        $hasMainAdmin = $mainAdminRow['count'] > 0;
        
        // Determine role and status
        $role = $hasMainAdmin ? 'sub_admin' : 'main_admin';
        $status = $hasMainAdmin ? 'pending' : 'approved';
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare insert query
        $insertQuery = "INSERT INTO admins (first_name, last_name, email, contact, password, date_of_birth, gender, role, status, approved_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        
        // Set approved_at for main admin
        $approvedAt = $hasMainAdmin ? NULL : date('Y-m-d H:i:s');
        
        mysqli_stmt_bind_param($insertStmt, "ssssssssss", 
            $firstName, $lastName, $email, $contact, $hashedPassword, 
            $dateOfBirth, $gender, $role, $status, $approvedAt
        );
        
        // Execute insert
        if (!mysqli_stmt_execute($insertStmt)) {
            throw new Exception('Failed to insert admin: ' . mysqli_error($conn));
        }
        
        $insertedId = mysqli_insert_id($conn);
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Close statements
        mysqli_stmt_close($emailCheckStmt);
        mysqli_stmt_close($insertStmt);
        
        // Don't close the connection as it's managed externally
        
        return [
            'success' => true,
            'message' => $hasMainAdmin ? 
                'Registration successful! Your account is pending approval by the main admin.' :
                'Registration successful! You are now the main admin.',
            'admin_id' => $insertedId,
            'role' => $role,
            'status' => $status
        ];
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// signcheck.php - Handle form submission
require 'Web/php/connection.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data
    $result = insertAdmin($connect, $_POST);
    
    if ($result['success']) {
        // Check if this is an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            
            // AJAX request - return JSON response
            echo json_encode([
                'status' => 'success',
                'message' => $result['message'],
                'role' => $result['role'],
                'admin_id' => $result['admin_id'],
                'redirect' => '/adminhome'
            ]);
            
        } else {
            // Regular form submission - redirect directly
            session_start();
            $_SESSION['admin_id'] = $result['admin_id'];
            $_SESSION['role'] = $result['role'];
            $_SESSION['success_message'] = $result['message'];
            
            // Redirect to admin home
            header('Location: /adminhome');
            exit();
        }
        
    } else {
        // Error response
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            
            echo json_encode([
                'status' => 'error',
                'message' => $result['message']
            ]);
        } else {
            // For regular form submission, you might want to redirect back with error
            session_start();
            $_SESSION['error_message'] = $result['message'];
            header('Location: /register'); // or wherever your registration form is
            exit();
        }
    }
} else {
    // Invalid request method
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

// Additional helper functions using your connection
function getPendingAdmins($connect) {
    $conn = $connect;
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed: ' . mysqli_connect_error()
        ];
    }
    
    $query = "SELECT id, first_name, last_name, email, contact, date_of_birth, gender, created_at 
              FROM admins 
              WHERE role = 'sub_admin' AND status = 'pending' 
              ORDER BY created_at DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [
            'success' => false,
            'message' => 'Failed to fetch pending admins: ' . mysqli_error($conn)
        ];
    }
    
    $pendingAdmins = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pendingAdmins[] = $row;
    }
    
    return [
        'success' => true,
        'data' => $pendingAdmins
    ];
}

// Function to approve/reject admin
function updateAdminStatus($connect, $adminId, $action, $approvedBy) {
    $conn = $connect;
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed: ' . mysqli_connect_error()
        ];
    }
    
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    $approvedAt = date('Y-m-d H:i:s');
    
    $query = "UPDATE admins SET status = ?, approved_by = ?, approved_at = ? WHERE id = ? AND role = 'sub_admin'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "siis", $status, $approvedBy, $approvedAt, $adminId);
    
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return [
            'success' => false,
            'message' => 'Failed to update admin status: ' . mysqli_error($conn)
        ];
    }
    
    $affectedRows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    if ($affectedRows === 0) {
        return [
            'success' => false,
            'message' => 'Admin not found or already processed'
        ];
    }
    
    return [
        'success' => true,
        'message' => "Admin successfully {$status}"
    ];
}

?>