<?php
// Headmistress dashboard placeholder
session_start();
if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'headmistress' && $_SESSION['user_type'] !== 'headmaster')) {
    header('Location: login.php');
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Headmistress Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Welcome to the Headmistress Dashboard</h2>
    <p>Headmistress dashboard and features coming soon.</p>
        <p><a href="user_settings.php">Settings</a></p>
</body>
</html>
