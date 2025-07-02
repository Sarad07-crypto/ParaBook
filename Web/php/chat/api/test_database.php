<?php
// test_database.php - Place this in the same directory as get_messages.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

try {
    echo "Testing database connection...\n";
    
    // Use the same database config as your main application
    $pdo = null;
    
    // Try different ways to get the database connection
    echo "Loading database config...\n";
    
    // Method 1: Check if database.php returns a connection
    $dbConnection = require_once '../config/database.php';
    if ($dbConnection instanceof PDO) {
        $pdo = $dbConnection;
        echo "✓ Database connection obtained via return\n";
    }
    
    // Method 2: Check if $pdo global variable was set
    if (!$pdo && isset($GLOBALS['pdo'])) {
        $pdo = $GLOBALS['pdo'];
        echo "✓ Database connection obtained via global pdo\n";
    }
    
    // Method 3: Check if $pdo variable was set in current scope
    if (!$pdo && isset($pdo)) {
        echo "✓ Database connection obtained via pdo variable\n";
    }
    
    // Method 4: Check for other common variable names
    if (!$pdo && isset($db)) {
        $pdo = $db;
        echo "✓ Database connection obtained via db variable\n";
    }
    
    if (!$pdo && isset($connection)) {
        $pdo = $connection;
        echo "✓ Database connection obtained via connection variable\n";
    }
    
    if (!$pdo && isset($database)) {
        $pdo = $database;
        echo "✓ Database connection obtained via database variable\n";
    }
    
    // Method 5: Try to create connection using common constants
    if (!$pdo) {
        echo "Checking for database constants...\n";
        
        // List common constant names
        $constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PASSWORD',
                     'DATABASE_HOST', 'DATABASE_NAME', 'DATABASE_USER', 'DATABASE_PASSWORD'];
        
        foreach ($constants as $const) {
            if (defined($const)) {
                echo "✓ Found constant: $const = " . constant($const) . "\n";
            } else {
                echo "✗ Constant not found: $const\n";
            }
        }
        
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "✓ Database connection created using DB_* constants\n";
        } elseif (defined('DATABASE_HOST') && defined('DATABASE_NAME') && defined('DATABASE_USER') && defined('DATABASE_PASSWORD')) {
            $pdo = new PDO(
                "mysql:host=" . DATABASE_HOST . ";dbname=" . DATABASE_NAME . ";charset=utf8mb4",
                DATABASE_USER,
                DATABASE_PASSWORD
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "✓ Database connection created using DATABASE_* constants\n";
        }
    }
    
    if (!$pdo) {
        echo "\n✗ Could not establish database connection.\n";
        echo "Please check your database.php file and ensure it either:\n";
        echo "1. Returns a PDO connection object\n";
        echo "2. Sets a global variable like \$pdo, \$db, \$connection, or \$database\n";
        echo "3. Defines constants like DB_HOST, DB_NAME, DB_USER, DB_PASS\n";
        echo "\nYour database.php file should look something like:\n";
        echo "<?php\n";
        echo "// Option 1: Return connection\n";
        echo "return new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');\n\n";
        echo "// Option 2: Set global variable\n";
        echo "\$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');\n\n";
        echo "// Option 3: Define constants\n";
        echo "define('DB_HOST', 'localhost');\n";
        echo "define('DB_NAME', 'mydb');\n";
        echo "define('DB_USER', 'user');\n";
        echo "define('DB_PASS', 'pass');\n";
        
        throw new Exception("No database connection found");
    }
    
    echo "✓ Database connection successful!\n\n";
    
    // Test if tables exist
    $tables = ['chat_conversations', 'chat_messages', 'users', 'company_services'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "✓ Table '$table' exists with columns: " . implode(', ', $columns) . "\n";
        } catch (PDOException $e) {
            echo "✗ Table '$table' not found or error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    
    // Test specific conversation
    $conversation_id = 2; // The ID from your URL
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM chat_conversations WHERE id = ?");
        $stmt->execute([$conversation_id]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($conversation) {
            echo "✓ Conversation ID $conversation_id found:\n";
            echo json_encode($conversation, JSON_PRETTY_PRINT) . "\n\n";
        } else {
            echo "✗ Conversation ID $conversation_id not found\n\n";
        }
    } catch (PDOException $e) {
        echo "✗ Error checking conversation: " . $e->getMessage() . "\n\n";
    }
    
    // Test messages for this conversation
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as message_count FROM chat_messages WHERE conversation_id = ?");
        $stmt->execute([$conversation_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ Found {$result['message_count']} messages for conversation ID $conversation_id\n";
    } catch (PDOException $e) {
        echo "✗ Error checking messages: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>