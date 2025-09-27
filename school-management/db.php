<?php
// Secure database connection using MySQLi and environment variables
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'sms_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    // Log error but do not display sensitive info to user
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit('A database error occurred.');
}
$conn->set_charset('utf8mb4');
?>
