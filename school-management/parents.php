<?php
// Parent dashboard placeholder
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'parent') {
    header('Location: login.php');
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parent Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Welcome to the Parent Portal</h2>
    <p>Parent dashboard and features coming soon.</p>
</body>
</html>
