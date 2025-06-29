<?php
// Create this file as check_logs.php and run it in your browser

echo "<h2>PHP Error Log Configuration</h2>";

// Show phpinfo to find log settings
phpinfo();

echo "<hr><h2>Current Error Log Settings</h2>";

// Display current error log settings
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";

echo "<tr><td>log_errors</td><td>" . (ini_get('log_errors') ? 'On' : 'Off') . "</td></tr>";
echo "<tr><td>error_log</td><td>" . (ini_get('error_log') ?: 'Default system log') . "</td></tr>";
echo "<tr><td>display_errors</td><td>" . (ini_get('display_errors') ? 'On' : 'Off') . "</td></tr>";
echo "<tr><td>error_reporting</td><td>" . error_reporting() . "</td></tr>";

echo "</table>";

echo "<hr><h2>Possible Error Log Locations</h2>";
echo "<ul>";

// Common error log locations
$possibleLogs = [
    '/var/log/apache2/error.log',           // Ubuntu/Debian Apache
    '/var/log/httpd/error_log',             // CentOS/RHEL Apache  
    '/var/log/nginx/error.log',             // Nginx
    '/var/log/php_errors.log',              // Common PHP log
    '/tmp/php_errors.log',                  // Temporary PHP log
    '/Applications/XAMPP/logs/php_error_log', // XAMPP on macOS
    'C:\\xampp\\php\\logs\\php_error_log',  // XAMPP on Windows
    'C:\\wamp\\logs\\php_error.log',        // WAMP on Windows
    getcwd() . '/error.log',                // Current directory
    $_SERVER['DOCUMENT_ROOT'] . '/error.log' // Web root
];

foreach ($possibleLogs as $logPath) {
    if (file_exists($logPath)) {
        echo "<li><strong>Found:</strong> $logPath</li>";
    } else {
        echo "<li>Not found: $logPath</li>";
    }
}

echo "</ul>";

// Try to write a test log entry
error_log("TEST LOG ENTRY - " . date('Y-m-d H:i:s'));

echo "<hr><h2>Test Log Entry</h2>";
echo "<p>A test log entry has been written. Check the error log location shown above.</p>";

// Show recent error log entries if we can find the log file
$errorLogPath = ini_get('error_log');
if ($errorLogPath && file_exists($errorLogPath)) {
    echo "<hr><h2>Recent Error Log Entries (Last 20 lines)</h2>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 400px;'>";
    
    // Read last 20 lines of error log
    $lines = file($errorLogPath);
    if ($lines) {
        $recentLines = array_slice($lines, -20);
        echo htmlspecialchars(implode('', $recentLines));
    } else {
        echo "Could not read error log file.";
    }
    
    echo "</pre>";
} else {
    echo "<hr><h2>Error Log Not Found or Not Configured</h2>";
    echo "<p>The error log path is not set or the file doesn't exist yet.</p>";
}

echo "<hr><h2>How to Enable Error Logging</h2>";
echo "<p>If error logging is not enabled, you can enable it by:</p>";
echo "<ol>";
echo "<li>Adding these lines to your .htaccess file:</li>";
echo "<pre>php_flag log_errors On\nphp_value error_log /path/to/your/error.log</pre>";
echo "<li>Or adding these lines to your PHP script:</li>";
echo "<pre>ini_set('log_errors', 1);\nini_set('error_log', '/path/to/your/error.log');</pre>";
echo "<li>Or modifying your php.ini file:</li>";
echo "<pre>log_errors = On\nerror_log = /path/to/your/error.log</pre>";
echo "</ol>";
?>