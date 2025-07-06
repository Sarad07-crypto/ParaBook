<?php
// logincheck.php - Handle admin login
require dirname(__FILE__) . '/../connection.php';

// Start session
session_start();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check connection
    if (!$connect) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed: ' . mysqli_connect_error()
        ]);
        exit();
    }
    
    // Set charset
    mysqli_set_charset($connect, "utf8");
    
    try {
        // Extract and sanitize form data
        $email = mysqli_real_escape_string($connect, trim($_POST['email']));
        $password = $_POST['password'];
        
        // Validate required fields
        if (empty($email) || empty($password)) {
            throw new Exception('Email and password are required');
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Query to get admin details
        $query = "SELECT id, first_name, last_name, email, password, role, status, approved_at, created_at 
                  FROM admins 
                  WHERE email = ?";
        
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 0) {
            throw new Exception('Invalid email or password');
        }
        
        $admin = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        // Verify password
        if (!password_verify($password, $admin['password'])) {
            throw new Exception('Invalid email or password');
        }
        
        // Check account status
        if ($admin['status'] === 'pending') {
            // Account is pending approval
            echo json_encode([
                'status' => 'pending',
                'message' => 'Your account is pending approval by the main administrator. Please wait for approval before accessing the dashboard.',
                'admin_name' => $admin['first_name'] . ' ' . $admin['last_name'],
                'created_at' => $admin['created_at']
            ]);
            exit();
        }
        
        if ($admin['status'] === 'rejected') {
            throw new Exception('Your account has been rejected. Please contact the main administrator.');
        }
        
        if ($admin['status'] !== 'approved') {
            throw new Exception('Your account status is invalid. Please contact the administrator.');
        }
        
        // Login successful - set session variables
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_status'] = $admin['status'];
        
        
        // Check if this is an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            
            // AJAX request - return JSON response
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful! Redirecting to dashboard...',
                'admin_name' => $admin['first_name'] . ' ' . $admin['last_name'],
                'role' => $admin['role'],
                'redirect' => '/adminhome'
            ]);
            
        } else {
            // Regular form submission - redirect directly
            header('Location: /adminhome');
            exit();
        }
        
    } catch (Exception $e) {
        // Check if this is an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } else {
            // For regular form submission, redirect back with error
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: /adminlogin');
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
?>