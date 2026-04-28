<?php
// TEMPORARY debug file — DELETE after fixing connection
// Visit: https://sigmasms.up.railway.app/dbcheck.php

$vars = [
    'MYSQLHOST'     => getenv('MYSQLHOST'),
    'MYSQLDATABASE' => getenv('MYSQLDATABASE'),
    'MYSQLUSER'     => getenv('MYSQLUSER'),
    'MYSQLPASSWORD' => getenv('MYSQLPASSWORD') ? '***SET***' : '(empty)',
    'MYSQLPORT'     => getenv('MYSQLPORT'),
    'APP_URL'       => getenv('APP_URL'),
    'MYSQL_URL'     => getenv('MYSQL_URL') ? '***SET***' : '(not set)',
    'DATABASE_URL'  => getenv('DATABASE_URL') ? '***SET***' : '(not set)',
];

echo '<pre style="font-family:monospace;font-size:14px;padding:20px;">';
echo "=== Railway ENV Variables ===\n\n";
foreach ($vars as $k => $v) {
    echo str_pad($k, 20) . ' = ' . ($v ?: '(empty/not set)') . "\n";
}

// Try connection
$host = getenv('MYSQLHOST')     ?: '127.0.0.1';
$port = getenv('MYSQLPORT')     ?: '3306';
$name = getenv('MYSQLDATABASE') ?: '';
$user = getenv('MYSQLUSER')     ?: '';
$pass = getenv('MYSQLPASSWORD') ?: '';

echo "\n=== Connection Test ===\n";
echo "DSN: mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4\n\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "SUCCESS: Connected to database!\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
echo '</pre>';
