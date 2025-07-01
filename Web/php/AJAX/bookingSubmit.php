<?php

ini_set("log_errors", 1);
ini_set("error_log", "C:/xampp/php/logs/my_php_errors.log");
error_log("=== BOOKING SUBMIT DEBUG START ==="); 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require '../connection.php';

if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}
    
try {
    // Enhanced session debugging
    error_log("=== SESSION DEBUG ===");
    error_log("Session ID: " . session_id());
    error_log("Session status: " . session_status());
    error_log("Session data: " . print_r($_SESSION, true));
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        error_log("AUTHENTICATION FAILED - User not logged in");
        $_SESSION['booking_error'] = 'Authentication required. Please log in first.';
        header('Location: /login');
        exit;
    }

    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("INVALID REQUEST METHOD: " . $_SERVER['REQUEST_METHOD']);
        $_SESSION['booking_error'] = 'Invalid request method. Only POST requests are allowed.';
        header('Location: /booking');
        exit;
    }

    // Enhanced database connection test
    if (!$connect) {
        error_log("DATABASE CONNECTION IS NULL");
        $_SESSION['booking_error'] = 'Database connection failed - connection is null';
        header('Location: /booking');
        exit;
    }
    
    if ($connect->connect_error) {
        error_log("DATABASE CONNECTION ERROR: " . $connect->connect_error);
        $_SESSION['booking_error'] = 'Database connection failed: ' . $connect->connect_error;
        header('Location: /booking');
        exit;
    }
    
    // Test database connection with a simple query
    $testQuery = $connect->query("SELECT 1");
    if (!$testQuery) {
        error_log("DATABASE TEST QUERY FAILED: " . $connect->error);
        $_SESSION['booking_error'] = 'Database connection test failed';
        header('Location: /booking');
        exit;
    }
    error_log("Database connection test successful");

    // Ensure userid is properly typed and valid
    $userid = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
    if ($userid === false || $userid <= 0) {
        error_log("INVALID USER ID: " . $_SESSION['user_id']);
        $_SESSION['booking_error'] = 'Invalid user session. Please log in again.';
        header('Location: /login');
        exit;
    }
    error_log("Valid User ID: " . $userid);

    // Enhanced form data validation and sanitization
    function sanitizeInput($data) {
        if ($data === null) return '';
        return trim(htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8'));
    }

    // Get and sanitize form data
    $formData = [
        'mainName' => sanitizeInput($_POST['mainName'] ?? ''),
        'mainEmail' => sanitizeInput($_POST['mainEmail'] ?? ''),
        'mainPhone' => sanitizeInput($_POST['mainPhone'] ?? ''),
        'mainNationality' => sanitizeInput($_POST['mainNationality'] ?? ''),
        'mainDate' => sanitizeInput($_POST['mainDate'] ?? ''),
        'mainPickup' => sanitizeInput($_POST['mainPickup'] ?? ''),
        'mainFlightType' => sanitizeInput($_POST['mainFlightType'] ?? ''),
        'mainWeight' => sanitizeInput($_POST['mainWeight'] ?? ''),
        'mainAge' => sanitizeInput($_POST['mainAge'] ?? ''),
        'gender' => sanitizeInput($_POST['gender'] ?? ''),
        'mainNotes' => sanitizeInput($_POST['mainNotes'] ?? '')
    ];

    // Log ALL received POST data
    error_log("=== RAW POST DATA ===");
    error_log(print_r($_POST, true));
    
    // Log processed data
    error_log("=== SANITIZED FORM DATA ===");
    foreach ($formData as $key => $value) {
        error_log("$key: '$value'");
    }

    // Enhanced field validation
    $validationErrors = [];
    
    // Required fields validation
    $requiredFields = [
        'mainName' => 'Full Name',
        'mainEmail' => 'Email Address',
        'mainPhone' => 'Phone Number',
        'mainNationality' => 'Nationality',
        'mainDate' => 'Flight Date',
        'mainPickup' => 'Pickup Location',
        'mainFlightType' => 'Flight Type',
        'mainAge' => 'Age',
        'gender' => 'Gender'
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($formData[$field])) {
            $validationErrors[$field] = "$label is required";
        }
    }
    
    // Email validation
    if (!empty($formData['mainEmail']) && !filter_var($formData['mainEmail'], FILTER_VALIDATE_EMAIL)) {
        $validationErrors['mainEmail'] = 'Please enter a valid email address';
    }
    
    // Phone validation (Nepal format)
    if (!empty($formData['mainPhone'])) {
        $phone = preg_replace('/[^0-9+\-]/', '', $formData['mainPhone']);
        if (!preg_match('/^(\+977-?)?9\d{9}$/', $phone)) {
            $validationErrors['mainPhone'] = 'Please enter a valid Nepali phone number';
        }
    }
    
    // Date validation
    if (!empty($formData['mainDate'])) {
        $enteredDate = DateTime::createFromFormat('Y-m-d', $formData['mainDate']);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if (!$enteredDate || $enteredDate < $today) {
            $validationErrors['mainDate'] = 'Please select a valid future date';
        }
    }
    
    // Age validation
    if (!empty($formData['mainAge'])) {
        $age = filter_var($formData['mainAge'], FILTER_VALIDATE_INT);
        if ($age === false || $age < 10 || $age > 80) {
            $validationErrors['mainAge'] = 'Age must be between 10 and 80 years';
        }
    }
    
    // Weight validation (optional)
    if (!empty($formData['mainWeight'])) {
        $weight = filter_var($formData['mainWeight'], FILTER_VALIDATE_FLOAT);
        if ($weight === false || $weight < 30 || $weight > 120) {
            $validationErrors['mainWeight'] = 'Weight must be between 30 and 120 kg';
        }
    }
    
    if (!empty($validationErrors)) {
        error_log("VALIDATION ERRORS: " . print_r($validationErrors, true));
        $_SESSION['booking_error'] = 'Please correct the following errors: ' . implode(', ', $validationErrors);
        $_SESSION['form_data'] = $formData; // Preserve form data
        $_SESSION['validation_errors'] = $validationErrors;
        header('Location: /booking');
        exit;
    }

    // Convert and validate flight type ID
    $flightTypeId = filter_var($formData['mainFlightType'], FILTER_VALIDATE_INT);
    if ($flightTypeId === false || $flightTypeId <= 0) {
        error_log("INVALID FLIGHT TYPE ID: " . $formData['mainFlightType']);
        $_SESSION['booking_error'] = 'Invalid flight type selected';
        header('Location: /booking');
        exit;
    }

    // Convert other numeric values
    $ageValue = (int)$formData['mainAge'];
    $weightValue = !empty($formData['mainWeight']) ? (float)$formData['mainWeight'] : null;

    error_log("=== CONVERTED VALUES ===");
    error_log("Flight Type ID: $flightTypeId");
    error_log("Age: $ageValue");
    error_log("Weight: " . ($weightValue ?? 'NULL'));

    // Enhanced flight type validation with better error handling
    $flightTypeStmt = $connect->prepare("SELECT flight_type_name, price FROM service_flight_types WHERE id = ?");
    if (!$flightTypeStmt) {
        error_log("FLIGHT TYPE QUERY PREPARE FAILED: " . $connect->error);
        $_SESSION['booking_error'] = 'Database error while validating flight type';
        header('Location: /booking');
        exit;
    }

    $flightTypeStmt->bind_param("i", $flightTypeId);
    
    if (!$flightTypeStmt->execute()) {
        error_log("FLIGHT TYPE QUERY EXECUTE FAILED: " . $flightTypeStmt->error);
        $_SESSION['booking_error'] = 'Error validating flight type';
        header('Location: /booking');
        exit;
    }
    
    $flightTypeResult = $flightTypeStmt->get_result();

    if ($flightTypeResult->num_rows === 0) {
        error_log("FLIGHT TYPE NOT FOUND OR INACTIVE: ID = $flightTypeId");
        $_SESSION['booking_error'] = 'Selected flight type is not available';
        header('Location: /booking');
        exit;
    }

    $flightTypeData = $flightTypeResult->fetch_assoc();
    $flightTypeName = $flightTypeData['flight_type_name'];
    $totalAmount = (float)$flightTypeData['price'];
    $flightTypeStmt->close();
    
    error_log("FLIGHT TYPE VALIDATED - Name: $flightTypeName, Price: $totalAmount");

    // Enhanced booking number generation
    $maxAttempts = 20;
    $attempts = 0;
    $bookingNo = null;
    
    do {
        $attempts++;
        $bookingNo = 'BK-' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
        
        // Check uniqueness in both tables
        $checkStmt = $connect->prepare("
            SELECT 1 FROM bookings WHERE booking_no = ? 
            UNION ALL
            SELECT 1 FROM temp_bookings WHERE booking_no = ?
        ");
        
        if (!$checkStmt) {
            throw new Exception('Failed to prepare booking number check query: ' . $connect->error);
        }
        
        $checkStmt->bind_param("ss", $bookingNo, $bookingNo);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->num_rows > 0;
        $checkStmt->close();
        
        if ($attempts >= $maxAttempts) {
            throw new Exception('Unable to generate unique booking number after ' . $maxAttempts . ' attempts');
        }
        
    } while ($exists);
    
    error_log("UNIQUE BOOKING NUMBER GENERATED: $bookingNo (attempts: $attempts)");

    // Verify temp_bookings table structure
    $tableStructure = $connect->query("SHOW COLUMNS FROM temp_bookings");
    if (!$tableStructure) {
        error_log("FAILED TO GET TABLE STRUCTURE: " . $connect->error);
        $_SESSION['booking_error'] = 'Database table structure error';
        header('Location: /booking');
        exit;
    }
    
    $columns = [];
    while ($row = $tableStructure->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    error_log("TEMP_BOOKINGS COLUMNS: " . implode(', ', $columns));

    // Enhanced temporary booking insertion
    $sql = "INSERT INTO temp_bookings (
        booking_no, user_id, full_name, email, phone, nationality, 
        date, pickup, flight_type_id, flight_type_name, weight, age, 
        gender, notes, total_amount, payment_status, created_at, expires_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))";

    error_log("INSERT SQL: " . $sql);

    $tempBookingStmt = $connect->prepare($sql);
    if (!$tempBookingStmt) {
        $error = 'Failed to prepare temporary booking statement: ' . $connect->error;
        error_log($error);
        throw new Exception($error);
    }

    // Enhanced parameter binding with detailed logging
    $bindParams = [
        $bookingNo,           // s - booking_no
        $userid,              // i - user_id  
        $formData['mainName'], // s - full_name
        $formData['mainEmail'], // s - email
        $formData['mainPhone'], // s - phone
        $formData['mainNationality'], // s - nationality
        $formData['mainDate'], // s - date
        $formData['mainPickup'], // s - pickup
        $flightTypeId,        // i - flight_type_id
        $flightTypeName,      // s - flight_type_name
        $weightValue,         // d - weight (can be null)
        $ageValue,            // i - age
        $formData['gender'],  // s - gender
        $formData['mainNotes'], // s - notes
        $totalAmount,          // d - total_amount
    ];

    error_log("=== BINDING PARAMETERS ===");
    for ($i = 0; $i < count($bindParams); $i++) {
        $value = $bindParams[$i] ?? 'NULL';
        error_log("Param " . ($i + 1) . ": " . (is_null($value) ? 'NULL' : "'$value'"));
    }

    // Bind parameters
    $bindResult = $tempBookingStmt->bind_param(
        "sissssssisdissd",
        $bookingNo, $userid, $formData['mainName'], $formData['mainEmail'], 
        $formData['mainPhone'], $formData['mainNationality'], $formData['mainDate'], 
        $formData['mainPickup'], $flightTypeId, $flightTypeName, $weightValue, 
        $ageValue, $formData['gender'], $formData['mainNotes'], $totalAmount
    );

    if (!$bindResult) {
        $error = 'Parameter binding failed: ' . $tempBookingStmt->error;
        error_log($error);
        throw new Exception($error);
    }

    // Execute with enhanced error handling
    if (!$tempBookingStmt->execute()) {
        $error = 'Temporary booking insertion failed: ' . $tempBookingStmt->error . ' (Error Code: ' . $tempBookingStmt->errno . ')';
        error_log($error);
        throw new Exception($error);
    }

    $affectedRows = $tempBookingStmt->affected_rows;
    $tempBookingId = $connect->insert_id;
    
    error_log("TEMPORARY BOOKING INSERTED SUCCESSFULLY");
    error_log("Affected rows: $affectedRows");
    error_log("Temporary booking ID: $tempBookingId");

    // Enhanced verification
    $verifyStmt = $connect->prepare("SELECT temp_id, booking_no, full_name, total_amount FROM temp_bookings WHERE temp_id = ?");
    if ($verifyStmt) {
        $verifyStmt->bind_param("i", $tempBookingId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        
        if ($verifyResult->num_rows > 0) {
            $verifyData = $verifyResult->fetch_assoc();
            error_log("VERIFICATION SUCCESSFUL: " . print_r($verifyData, true));
        } else {
            error_log("VERIFICATION FAILED: No record found with temp_id: $tempBookingId");
            throw new Exception('Booking was not properly saved');
        }
        $verifyStmt->close();
    }

    // Store booking details in session for eSewa redirect
    $_SESSION['booking_success'] = [
        'booking_no' => $bookingNo,
        'temp_booking_id' => $tempBookingId,
        'flight_type' => $flightTypeName,
        'date' => $formData['mainDate'],
        'pickup' => $formData['mainPickup'],
        'total_amount' => $totalAmount
    ];

    // Enhanced eSewa configuration
    $merchantId = "EPAYTEST"; // Use your actual merchant ID for production
    
    // Ensure URLs are absolute and accessible
    $baseUrl = "http://localhost:8080"; // Update this to your actual domain
    $successUrl = $baseUrl . "/Web/php/eSewa/success.php";
    $failureUrl = $baseUrl . "/Web/php/eSewa/failure.php";
    
    error_log("=== ESEWA CONFIGURATION ===");
    error_log("Merchant ID: $merchantId");
    error_log("Success URL: $successUrl");
    error_log("Failure URL: $failureUrl");
    error_log("Total Amount: $totalAmount");
    error_log("Transaction UUID: $bookingNo");
    
    // Store eSewa data in session
    $_SESSION['esewa_data'] = [
        'total_amount' => number_format($totalAmount, 2, '.', ''),
        'amount' => number_format($totalAmount, 2, '.', ''),
        'tax_amount' => '0.00',
        'product_service_charge' => '0.00',
        'product_delivery_charge' => '0.00',
        'merchant_id' => $merchantId,
        'transaction_uuid' => $bookingNo,
        'success_url' => $successUrl,
        'failure_url' => $failureUrl
    ];
    
    // Store form data in session for repopulating form on errors
    $_SESSION['form_data'] = $formData;
    
    // Clear any previous error/success messages when starting fresh
    unset($_SESSION['booking_error']);
    unset($_SESSION['validation_errors']);
    
    error_log("REDIRECTING TO ESEWA PAYMENT PAGE");
    
    // Redirect to eSewa payment page
    header('Location: /eSewaPayment');
    exit;

} catch (Exception $e) {
    error_log("=== EXCEPTION CAUGHT ===");
    error_log("Exception message: " . $e->getMessage());
    error_log("File: " . $e->getFile());
    error_log("Line: " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $_SESSION['booking_error'] = 'A server error occurred. Please try again later.';
    header('Location: /booking');
    exit;
    
} finally {
    // Clean up resources
    if (isset($tempBookingStmt) && $tempBookingStmt) {
        $tempBookingStmt->close();
    }
    if (isset($flightTypeStmt) && $flightTypeStmt) {
        $flightTypeStmt->close();
    }
    if (isset($connect) && $connect) {
        $connect->close();
        error_log("Database connection closed");
    }
    error_log("=== BOOKING SUBMIT DEBUG END ===");
}
?>