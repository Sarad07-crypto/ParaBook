<?php

class Connect extends PDO
{
    public function __construct()
    {
        try {
            parent::__construct(
                "mysql:host=localhost:3307;dbname=parabook",
                'root',
                '',
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}

class Controller
{
    function insertData($data)
    {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Debug: Log what we're receiving
        error_log("InsertData called with: " . print_r($data, true));
        
        try {
            $db = new Connect();
            $session = bin2hex(random_bytes(16));

            $checkMainUser = $db->prepare("SELECT * FROM users WHERE email = :email");
            $checkMainUser->execute([':email' => $data['email']]);
            $mainUser = $checkMainUser->fetch(PDO::FETCH_ASSOC);

            $checkUser = $db->prepare("SELECT id FROM users WHERE google_id = :g_id");
            $checkUser->execute([":g_id" => $data["g_id"]]);
            $existingUser = $checkUser->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                $userId = $existingUser['id'];

                // Fetch account type and user info including avatar
                $userInfoQuery = $db->prepare("SELECT acc_type, firstName, lastName, avatar FROM users_info WHERE user_id = :user_id");
                $userInfoQuery->execute([":user_id" => $userId]);
                $userInfo = $userInfoQuery->fetch(PDO::FETCH_ASSOC);
                $accType = $userInfo['acc_type'] ?? 'passenger';
                
                // Set session variables including avatar
                $_SESSION['acc_type'] = $accType;
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $data['email'];
                $_SESSION['avatar'] = $userInfo['avatar'] ?? 'default-avatar.png';
                $_SESSION['givenName'] = $userInfo['firstName'] ?? $data['givenName'];
                $_SESSION['familyName'] = $userInfo['lastName'] ?? $data['familyName'];
                
                // Update session
                $updateSession = $db->prepare("
                    UPDATE users_sessions
                    SET session_token = :session, created_at = NOW()
                    WHERE user_id = :user_id
                ");
                $updateSession->execute([
                    ":user_id" => $userId,
                    ":session" => $session
                ]);

                // Set cookies (fix deprecated NULL domain parameter)
                setcookie("id", $userId, time() + 60 * 60 * 24 * 30, "/");
                setcookie("session", $session, time() + 60 * 60 * 24 * 30, "/");

                // Redirect based on acc_type
                $redirectUrl = ($accType === 'company') ? '/home' : '/home';
                header("Location: $redirectUrl");
                exit();
            }

            if ($mainUser) {
                session_destroy();
                echo "<script>
                alert('User already registered.');
                window.location.href = '/login';
                </script>";
                exit();
            }

            // Debug: Log before setting session
            error_log("Setting session data - temp_user_data and show_account_selection");
            
            // Store user data temporarily in session and show account selection
            $_SESSION['temp_user_data'] = $data;
            $_SESSION['show_account_selection'] = true;
            
            // Debug: Verify session data was set
            error_log("Session data set: " . print_r($_SESSION, true));
            
            // Force session write
            session_write_close();
            
            // Debug: Log before redirect
            error_log("Redirecting to /accountSelection");
            
            // Redirect to account selection page
            header("Location: /accountSelection");
            exit();

        } catch (PDOException $e) {
            error_log("Database error in insertData: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("General error in insertData: " . $e->getMessage());
            throw $e;
        }
    }

    function completeRegistrationWithAccountType() {
        // Clean any output buffer first
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Debug logging
        error_log("CompleteRegistration called. Session data: " . print_r($_SESSION, true));
        error_log("Current session ID: " . session_id());
        error_log("Selected account type from session: " . ($_SESSION['selected_acc_type'] ?? 'NOT SET'));
        
        try {
            if (!isset($_SESSION['temp_user_data']) || !isset($_SESSION['selected_acc_type'])) {
                error_log("Missing session data - temp_user_data or selected_acc_type");
                error_log("temp_user_data exists: " . (isset($_SESSION['temp_user_data']) ? 'YES' : 'NO'));
                error_log("selected_acc_type exists: " . (isset($_SESSION['selected_acc_type']) ? 'YES' : 'NO'));
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required data. Please start the registration process again.',
                    'debug' => [
                        'temp_user_data' => isset($_SESSION['temp_user_data']),
                        'selected_acc_type' => isset($_SESSION['selected_acc_type']),
                        'session_id' => session_id(),
                        'all_session_keys' => array_keys($_SESSION)
                    ]
                ]);
                return;
            }

            $data = $_SESSION['temp_user_data'];
            
            $db = new Connect();
            $session = bin2hex(random_bytes(16));
            $acc_type = $_SESSION['selected_acc_type'];
            
            error_log("About to register user with account type: " . $acc_type);
            
            // Validate account type
            if (!in_array($acc_type, ['passenger', 'company'])) {
                $acc_type = 'passenger';
            }

            // Begin transaction
            $db->beginTransaction();

            try {
                // Insert new user
                $insertUser = $db->prepare("
                    INSERT INTO users (email, password, google_id, facebook_id, sign_with)
                    VALUES (:email, NULL, :g_id, NULL, :sign_with)
                ");
                $insertUser->execute([
                    ":email" => $data["email"],
                    ":g_id" => $data["g_id"] ?? null, 
                    ":sign_with" => $data["sign_with"]
                ]);
                $userId = $db->lastInsertId();

                if (!$userId) {
                    throw new Exception("Failed to create user record");
                }

                // Insert into users_info with the selected account type
                $insertInfo = $db->prepare("
                    INSERT INTO users_info (
                        user_id, acc_type, firstName, lastName, gender, contact, dob, country, avatar
                    ) VALUES (
                        :user_id, :acc_type, :f_Name, :l_Name, '', '', NULL, '', :avatar
                    )
                ");
                $insertInfo->execute([
                    ":user_id" => $userId,
                    ":acc_type" => $acc_type,
                    ":f_Name" => $data["givenName"],
                    ":l_Name" => $data["familyName"],
                    ":avatar" => $data["avatar"]
                ]);

                // Insert verify
                $insertverify = $db->prepare("
                    INSERT INTO users_verify (user_id, verify_token, is_verified, otp, otp_expiry)
                    VALUES (:user_id, NULL, 1, NULL, NULL)
                ");
                $insertverify->execute([":user_id" => $userId]);

                // Insert session
                $insertSession = $db->prepare("
                    INSERT INTO users_sessions (user_id, session_token, created_at)
                    VALUES (:user_id, :session, NOW())
                ");
                $insertSession->execute([
                    ":user_id" => $userId,
                    ":session" => $session
                ]);

                // Commit transaction
                $db->commit();

                $_SESSION['acc_type'] = $acc_type;
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $data['email'];
                $_SESSION['avatar'] = $data['avatar'] ?? 'default-avatar.png';
                $_SESSION['givenName'] = $data['givenName'];
                $_SESSION['familyName'] = $data['familyName'];

                // Set cookies
                setcookie("id", $userId, time() + 60 * 60 * 24 * 30, "/");
                setcookie("session", $session, time() + 60 * 60 * 24 * 30, "/");

                // Clear temporary session data
                unset($_SESSION['selected_acc_type']);
                unset($_SESSION['temp_user_data']);
                unset($_SESSION['show_account_selection']); 

                // Force session write
                session_write_close();

                // Return success response
                $redirectUrl = ($acc_type === 'company') ? '/home' : '/home';
                
                echo json_encode([
                    'success' => true,
                    'message' => 'User successfully registered as ' . ucfirst($acc_type),
                    'redirect_url' => $redirectUrl,
                    'user_id' => $userId
                ]);

            } catch (Exception $e) {
                // Rollback transaction
                $db->rollback();
                throw $e;
            }

        } catch (PDOException $e) {
            error_log("Database error in completeRegistration: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error occurred during registration',
                'debug' => [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]
            ]);
        } catch (Exception $e) {
            error_log("General error in completeRegistration: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
                'debug' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        }
    }
}

?>