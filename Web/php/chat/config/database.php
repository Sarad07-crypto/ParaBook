<?php
/**
 * Database Configuration - Improved version with better error handling
 */

// Database credentials
define('DB_HOST', 'localhost:3307');
define('DB_NAME', 'parabook');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection with improved error handling
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
                PDO::ATTR_TIMEOUT => 30, // Add connection timeout
                PDO::ATTR_PERSISTENT => false // Disable persistent connections for debugging
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Test the connection immediately
            $pdo->query("SELECT 1");
            
            error_log("Database connection successful");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            error_log("Connection details: Host=" . DB_HOST . ", DB=" . DB_NAME . ", User=" . DB_USER);
            
            // Don't expose sensitive details in the exception message
            throw new Exception("Database connection failed. Check server logs for details.");
        }
    }
    
    return $pdo;
}

/**
 * Database health check with detailed diagnostics
 */
function checkDatabaseHealth() {
    try {
        error_log("Starting database health check...");
        
        $db = getDBConnection();
        
        // Test basic query
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        if ($result['test'] == 1) {
            error_log("Database health check: Basic query successful");
        }
        
        // Test required tables exist
        $tables = ['users', 'chat_conversations', 'chat_messages', 'company_services'];
        foreach ($tables as $table) {
            $stmt = $db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() == 0) {
                error_log("Database health check: Missing table '$table'");
                return false;
            }
        }
        
        error_log("Database health check: All required tables exist");
        return true;
        
    } catch (Exception $e) {
        error_log("Database health check failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Test database connection and log diagnostics
 */
function testDatabaseConnection() {
    error_log("=== Database Connection Test ===");
    error_log("Host: " . DB_HOST);
    error_log("Database: " . DB_NAME);
    error_log("User: " . DB_USER);
    error_log("Charset: " . DB_CHARSET);
    
    try {
        $db = getDBConnection();
        error_log("Connection test: SUCCESS");
        
        $healthCheck = checkDatabaseHealth();
        error_log("Health check: " . ($healthCheck ? "PASSED" : "FAILED"));
        
        return $db;
    } catch (Exception $e) {
        error_log("Connection test: FAILED - " . $e->getMessage());
        return null;
    }
}

// Create the $db variable that your application expects
try {
    $db = getDBConnection();
    error_log("Global database connection initialized successfully");
} catch (Exception $e) {
    error_log("Failed to initialize global database connection: " . $e->getMessage());
    $db = null;
}

// Run diagnostics if this file is accessed directly
if (basename($_SERVER['PHP_SELF']) === 'database.php') {
    header('Content-Type: application/json');
    
    $testResult = testDatabaseConnection();
    $response = [
        'success' => $testResult !== null,
        'message' => $testResult !== null ? 'Database connection successful' : 'Database connection failed',
        'timestamp' => date('Y-m-d H:i:s'),
        'config' => [
            'host' => DB_HOST,
            'database' => DB_NAME,
            'user' => DB_USER,
            'charset' => DB_CHARSET
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
}
?>