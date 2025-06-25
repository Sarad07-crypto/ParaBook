<?php
// api/update_profile.php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost:3307;dbname=parabook", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $userId = $_SESSION['user_id'];
    
    // Debug: Log received data
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    
    // Get form data with proper sanitization
    $firstName = !empty($_POST['firstName']) ? trim($_POST['firstName']) : null;
    $lastName = !empty($_POST['lastName']) ? trim($_POST['lastName']) : null;
    $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
    $contact = !empty($_POST['contact']) ? trim($_POST['contact']) : null;
    $password = !empty($_POST['password']) ? trim($_POST['password']) : null;
    $dob = !empty($_POST['DOB']) ? $_POST['DOB'] : null;
    $country = !empty($_POST['country']) ? trim($_POST['country']) : null;
    $userType = !empty($_POST['userType']) ? $_POST['userType'] : null;
    $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;
    
    // Validate required fields (at least one field should be provided)
    if (empty($firstName) && empty($lastName) && empty($email) && empty($contact) && 
        empty($password) && empty($dob) && empty($country) && empty($userType) && 
        empty($gender) && (!isset($_FILES['profileImage']) || $_FILES['profileImage']['error'] !== UPLOAD_ERR_OK)) {
        echo json_encode(['success' => false, 'message' => 'No data provided for update']);
        exit;
    }
    
    // Validate email format if provided
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Validate date format if provided
    if ($dob && !DateTime::createFromFormat('Y-m-d', $dob)) {
        echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD format.']);
        exit;
    }
    
    // Handle profile image upload
    $avatarPath = null;
    $oldAvatarPath = null;
    
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        // Get current avatar to delete old file later
        $stmt = $pdo->prepare("SELECT avatar FROM users_info WHERE user_id = ?");
        $stmt->execute([$userId]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($currentUser && !empty($currentUser['avatar'])) {
            $oldAvatarPath = $currentUser['avatar'];
        }
        
        // Define upload directory - adjust path as needed
        $uploadDir = '../../../Assets/uploads/avatars/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
                exit;
            }
        }
        
        // Validate file
        $fileInfo = $_FILES['profileImage'];
        $fileExtension = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Check file extension
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.']);
            exit;
        }
        
        // Check file size (limit to 5MB)
        if ($fileInfo['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.']);
            exit;
        }
        
        // Check if it's actually an image
        $imageInfo = getimagesize($fileInfo['tmp_name']);
        if ($imageInfo === false) {
            echo json_encode(['success' => false, 'message' => 'Invalid image file.']);
            exit;
        }
        
        // Generate clean filename without extra separators
        $fileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        // Move uploaded file
        if (move_uploaded_file($fileInfo['tmp_name'], $targetPath)) {
            $avatarPath = $fileName;
            error_log("Avatar uploaded successfully: " . $targetPath);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image. Please check directory permissions.']);
            exit;
        }
    } else if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File size exceeds server limit',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        $errorMessage = isset($uploadErrors[$_FILES['profileImage']['error']]) 
            ? $uploadErrors[$_FILES['profileImage']['error']] 
            : 'Unknown upload error';
            
        echo json_encode(['success' => false, 'message' => 'Upload error: ' . $errorMessage]);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update users table if email or password is provided
    $updateUserFields = [];
    $userParams = [];
    
    if ($email) {
        // Check if email already exists for other users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        
        $updateUserFields[] = "email = ?";
        $userParams[] = $email;
    }
    
    if ($password && !empty(trim($password))) {
        // Validate password strength (optional)
        if (strlen($password) < 6) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
            exit;
        }
        
        $updateUserFields[] = "password = ?";
        $userParams[] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Update users table if needed
    if (!empty($updateUserFields)) {
        $userParams[] = $userId;
        $userSql = "UPDATE users SET " . implode(', ', $updateUserFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($userSql);
        $stmt->execute($userParams);
        error_log("Updated users table for user ID: " . $userId);
    }
    
    // Check if user_info record exists
    $stmt = $pdo->prepare("SELECT user_id FROM users_info WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userInfoExists = $stmt->fetch();
    
    // Prepare user_info data (only include non-null values)
    $userInfoData = [];
    
    if ($userType !== null) $userInfoData['acc_type'] = $userType;
    if ($firstName !== null) $userInfoData['firstName'] = $firstName;
    if ($lastName !== null) $userInfoData['lastName'] = $lastName;
    if ($gender !== null) $userInfoData['gender'] = $gender;
    if ($contact !== null) $userInfoData['contact'] = $contact;
    if ($dob !== null) $userInfoData['dob'] = $dob;
    if ($country !== null) $userInfoData['country'] = $country;
    
    // Always update avatar if a new one was uploaded
    if ($avatarPath !== null) {
        $userInfoData['avatar'] = $avatarPath;
    }
    
    // Update or insert user_info data
    if (!empty($userInfoData)) {
        if ($userInfoExists) {
            // Update existing record
            $updateInfoFields = [];
            $infoParams = [];
            
            foreach ($userInfoData as $field => $value) {
                $updateInfoFields[] = "`$field` = ?";
                $infoParams[] = $value;
            }
            
            $infoParams[] = $userId;
            $infoSql = "UPDATE users_info SET " . implode(', ', $updateInfoFields) . " WHERE user_id = ?";
            $stmt = $pdo->prepare($infoSql);
            $stmt->execute($infoParams);
            error_log("Updated users_info table for user ID: " . $userId);
        } else {
            // Insert new record
            $userInfoData['user_id'] = $userId;
            
            $fields = array_keys($userInfoData);
            $placeholders = array_fill(0, count($fields), '?');
            
            // Add backticks around field names
            $quotedFields = array_map(function($field) { return "`$field`"; }, $fields);
            
            $infoSql = "INSERT INTO users_info (" . implode(', ', $quotedFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $pdo->prepare($infoSql);
            $stmt->execute(array_values($userInfoData));
            error_log("Inserted new users_info record for user ID: " . $userId);
        }
        
        // Delete old avatar file if a new one was uploaded
        if ($avatarPath && $oldAvatarPath && $oldAvatarPath !== $avatarPath) {
            $oldFilePath = $uploadDir . $oldAvatarPath;
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
                error_log("Deleted old avatar: " . $oldFilePath);
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // UPDATE SESSION VARIABLES IMMEDIATELY
    // This is the key addition to fix the immediate update issue
    
    // Update session variables based on what was changed
    if ($email) {
        $_SESSION['Email'] = $email;
    }
    
    if ($firstName !== null) {
        $_SESSION['firstName'] = $firstName;
    }
    
    if ($lastName !== null) {
        $_SESSION['lastName'] = $lastName;
    }
    
    // Update avatar session variable immediately
    if ($avatarPath !== null) {
        $_SESSION['avatar'] = $avatarPath;
        error_log("Updated session avatar to: " . $avatarPath);
    }
    
    // Fetch updated user data to return and verify session sync
    $stmt = $pdo->prepare("
        SELECT u.email, ui.firstName, ui.lastName, ui.contact, ui.dob, ui.country, 
               ui.acc_type, ui.gender, ui.avatar
        FROM users u 
        LEFT JOIN users_info ui ON u.id = ui.user_id 
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ensure session is in sync with database
    if ($updatedUser) {
        if (!empty($updatedUser['firstName'])) {
            $_SESSION['firstName'] = $updatedUser['firstName'];
        }
        if (!empty($updatedUser['lastName'])) {
            $_SESSION['lastName'] = $updatedUser['lastName'];
        }
        if (!empty($updatedUser['avatar'])) {
            $_SESSION['avatar'] = $updatedUser['avatar'];
        }
        if (!empty($updatedUser['email'])) {
            $_SESSION['Email'] = $updatedUser['email'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'data' => [
            'avatar_path' => $avatarPath,
            'avatar_url' => $avatarPath ? '../../../Assets/uploads/avatars/' . $avatarPath : null,
            'user_data' => $updatedUser,
            'session_updated' => true
        ]
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("General error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>