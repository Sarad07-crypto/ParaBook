<?php
/**
 * Authentication Manager
 * Handles user authentication and session management
 */

class AuthManager {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Login user with email and password
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, email, password, user_type, company_name, profile_image, phone, status
                FROM users 
                WHERE email = ? AND status = 'active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $this->updateLastLogin($user['id']);
                
                // Remove password from response
                unset($user['password']);
                
                // Start session and store user data
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_data'] = $user;
                
                return $user;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Register new user
     */
    public function register($userData) {
        try {
            $this->db->beginTransaction();
            
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$userData['email']]);
            if ($stmt->fetch()) {
                $this->db->rollBack();
                return false;
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
            
            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, user_type, company_name, phone, profile_image, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
            ");
            
            $stmt->execute([
                $userData['name'],
                $userData['email'],
                $hashedPassword,
                $userData['user_type'] ?? 'passenger',
                $userData['company_name'] ?? null,
                $userData['phone'] ?? null,
                $userData['profile_image'] ?? null
            ]);
            
            $user_id = $this->db->lastInsertId();
            
            // Get the created user
            $stmt = $this->db->prepare("
                SELECT id, name, email, user_type, company_name, profile_image, phone, status
                FROM users WHERE id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->db->commit();
            
            // Start session and store user data
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_data'] = $user;
            
            return $user;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Authenticate request using session or API key
     */
    public function authenticateRequest() {
        try {
            // Check for API key in headers first
            $headers = getallheaders();
            $apiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? '';
            
            if ($apiKey) {
                return $this->authenticateByApiKey($apiKey);
            }
            
            // Check session authentication
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            if (isset($_SESSION['user_id'])) {
                // Verify user still exists and is active
                $stmt = $this->db->prepare("
                    SELECT id, name, email, user_type, company_name, profile_image, phone, status
                    FROM users WHERE id = ? AND status = 'active'
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $_SESSION['user_data'] = $user; // Update session data
                    return $user;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Authenticate using API key
     */
    private function authenticateByApiKey($apiKey) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.email, u.user_type, u.company_name, u.profile_image, u.phone, u.status
                FROM users u
                JOIN user_api_keys ak ON u.id = ak.user_id
                WHERE ak.api_key = ? AND ak.is_active = TRUE AND u.status = 'active'
            ");
            $stmt->execute([$apiKey]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Update last used timestamp for API key
                $stmt = $this->db->prepare("
                    UPDATE user_api_keys 
                    SET last_used_at = NOW() 
                    WHERE api_key = ?
                ");
                $stmt->execute([$apiKey]);
            }
            
            return $user ?: false;
            
        } catch (Exception $e) {
            error_log("API key authentication error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        try {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            if (isset($_SESSION['user_id'])) {
                $this->updateLastLogout($_SESSION['user_id']);
            }
            
            // Clear session
            session_unset();
            session_destroy();
            
            return true;
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($user_id, $currentPassword, $newPassword) {
        try {
            // Verify current password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return false;
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$hashedPassword, $user_id]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($user_id, $profileData) {
        try {
            $allowedFields = ['name', 'phone', 'company_name', 'profile_image'];
            $updateFields = [];
            $params = [];
            
            foreach ($allowedFields as $field) {
                if (isset($profileData[$field])) {
                    $updateFields[] = "{$field} = ?";
                    $params[] = $profileData[$field];
                }
            }
            
            if (empty($updateFields)) {
                return false;
            }
            
            $updateFields[] = "updated_at = NOW()";
            $params[] = $user_id;
            
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // Get updated user data
            $stmt = $this->db->prepare("
                SELECT id, name, email, user_type, company_name, profile_image, phone, status
                FROM users WHERE id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Update session data if exists
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
                $_SESSION['user_data'] = $user;
            }
            
            return $user;
            
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, email, user_type, company_name, profile_image, phone, status, created_at
                FROM users WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate API key for user
     */
    public function generateApiKey($user_id, $name = 'Default') {
        try {
            $apiKey = 'ak_' . bin2hex(random_bytes(32));
            
            $stmt = $this->db->prepare("
                INSERT INTO user_api_keys (user_id, api_key, name, is_active, created_at)
                VALUES (?, ?, ?, TRUE, NOW())
            ");
            $stmt->execute([$user_id, $apiKey, $name]);
            
            return $apiKey;
            
        } catch (Exception $e) {
            error_log("API key generation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's API keys
     */
    public function getUserApiKeys($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, api_key, is_active, created_at, last_used_at
                FROM user_api_keys 
                WHERE user_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get API keys error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Revoke API key
     */
    public function revokeApiKey($user_id, $api_key) {
        try {
            $stmt = $this->db->prepare("
                UPDATE user_api_keys 
                SET is_active = FALSE, updated_at = NOW()
                WHERE user_id = ? AND api_key = ?
            ");
            $stmt->execute([$user_id, $api_key]);
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log("API key revocation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has permission for action
     */
    public function hasPermission($user, $action) {
        // Simple role-based permissions
        $permissions = [
            'passenger' => ['send_message', 'create_conversation', 'upload_file'],
            'company' => ['send_message', 'upload_file', 'view_analytics'],
            'admin' => ['*'] // All permissions
        ];
        
        $userType = $user['user_type'] ?? 'passenger';
        $userPermissions = $permissions[$userType] ?? [];
        
        return in_array('*', $userPermissions) || in_array($action, $userPermissions);
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin($user_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET last_login_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$user_id]);
        } catch (Exception $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
    
    /**
     * Update last logout timestamp
     */
    private function updateLastLogout($user_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET last_logout_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$user_id]);
        } catch (Exception $e) {
            error_log("Update last logout error: " . $e->getMessage());
        }
    }
}