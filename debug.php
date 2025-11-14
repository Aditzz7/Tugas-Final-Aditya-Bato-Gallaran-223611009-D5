<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>API Debug Information</h1>";
echo "<pre>";

// Basic PHP info
echo "PHP Version: " . phpversion() . "\n";
echo "Current Directory: " . __DIR__ . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n\n";

// Test config loading
echo "=== Testing Config Loading ===\n";
try {
    $config = require __DIR__ . '/config/env.php';
    echo "✅ Config loaded successfully\n";
    echo "Database DSN: " . $config['db']['dsn'] . "\n";
    echo "Database User: " . $config['db']['user'] . "\n";
} catch (Exception $e) {
    echo "❌ Config loading failed: " . $e->getMessage() . "\n";
}

// Test database connection
echo "\n=== Testing Database Connection ===\n";
try {
    $pdo = new PDO($config['db']['dsn'], $config['db']['user'], $config['db']['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n";

    // Test if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;
    echo "Users table exists: " . ($tableExists ? "✅ YES" : "❌ NO") . "\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test autoloader
echo "\n=== Testing Autoloader ===\n";
try {
    // Test if classes can be loaded
    if (file_exists(__DIR__ . '/src/Helpers/Response.php')) {
        echo "✅ Response.php exists\n";
        require_once __DIR__ . '/src/Helpers/Response.php';
        echo "✅ Response class loaded\n";
    } else {
        echo "❌ Response.php not found\n";
    }
} catch (Exception $e) {
    echo "❌ Autoloader error: " . $e->getMessage() . "\n";
}

// Test basic API response
echo "\n=== Testing Basic API Response ===\n";
try {
    $testData = ['status' => 'debug_ok', 'time' => date('c')];
    echo "✅ Test data created\n";
    echo "Test JSON: " . json_encode($testData) . "\n";
} catch (Exception $e) {
    echo "❌ JSON encoding failed: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===";
echo "</pre>";
?>