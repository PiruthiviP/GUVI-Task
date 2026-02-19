<?php
// MySQL Connection
$host = '127.0.0.1';
$db_name = 'guvi_db';
// Default specific credentials for XAMPP/local setups, can be changed by user
$username = 'root'; 
$password = ''; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Return JSON error if connection fails
    die(json_encode(["status" => "error", "message" => "Database Connection Error: " . $e->getMessage()]));
}

// Redis Connection
$redis = new Redis();
try {
    // Default Redis port
    $redis->connect('127.0.0.1', 6379);
} catch (Exception $e) {
    die(json_encode(["status" => "error", "message" => "Redis Connection Error: " . $e->getMessage()]));
}
?>
