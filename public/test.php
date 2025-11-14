<?php
echo "PHP Test - API PHP Native";
echo "<br>Time: " . date('Y-m-d H:i:s');
echo "<br>Domain: " . $_SERVER['HTTP_HOST'];
echo "<br>Request: " . $_SERVER['REQUEST_URI'];
echo "<br>Document Root: " . $_SERVER['DOCUMENT_ROOT'];
echo "<br>Current Dir: " . __DIR__;

// Test config
$config = require __DIR__ . '/../config/env.php';
echo "<br>Config loaded: " . ($config ? 'YES' : 'NO');
echo "<br>Database host: " . $config['db']['dsn'];
?>