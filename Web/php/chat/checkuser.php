<?php
// Create a file called check_user.php and run it to verify user exists

require_once 'config/database.php';

try {
    echo "Connecting to database...\n";
    $db = getDBConnection();
    echo "Connected successfully!\n";
    
    // Check if user 43 exists
    echo "\nChecking for user ID 43...\n";
    $stmt = $db->prepare("SELECT id, email FROM users WHERE id = ?");
    $stmt->execute([43]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User 43 EXISTS:\n";
        echo "   ID: " . $user['id'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
    } else {
        echo "❌ User 43 NOT FOUND\n";
    }
    
    // Show all users for reference
    echo "\n📋 All users in database:\n";
    $allUsers = $db->query("SELECT id, email FROM users ORDER BY id")->fetchAll();
    
    if (empty($allUsers)) {
        echo "   No users found in database!\n";
    } else {
        foreach ($allUsers as $u) {
            echo "   ID: {$u['id']}, Email: {$u['email']}\n";
        }
    }
    
    // Test database connection with user table
    $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "\n📊 Total users in database: $userCount\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>