<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Admin Dashboard</h1>
</header>
<div class="container">
    <nav>
        <ul>
            <li><a href="admin_settings.php">Settings</a></li>
            <li><a href="admin_attendance.php">Attendance Management</a></li>
            <!-- Add more links as needed -->
        </ul>
    </nav>
    <h2>Welcome, Admin!</h2>
    <!-- Dashboard content here -->
</div>
</body>
</html>
