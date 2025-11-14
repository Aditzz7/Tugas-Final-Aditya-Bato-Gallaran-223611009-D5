<?php
// Simple database connection test
$config = require __DIR__ . '/config/env.php';

try {
    $pdo = new PDO($config['db']['dsn'], $config['db']['user'], $config['db']['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connection successful!<br>";
    echo "Host: " . $config['db']['dsn'] . "<br>";
    echo "User: " . $config['db']['user'] . "<br>";

    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists<br>";

        // Count users
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "Total users: " . $count . "<br>";
    } else {
        echo "❌ Users table does not exist<br>";
        echo "<a href='database_setup.sql'>Download SQL to create table</a><br>";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    echo "Please check your database credentials in config/env.php<br>";
}
?>