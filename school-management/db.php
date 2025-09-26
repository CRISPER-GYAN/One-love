<?php
// Secure database connection using MySQLi
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'school_management';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Database connection error.');
}
// Set charset for security
$conn->set_charset('utf8mb4');
?>
