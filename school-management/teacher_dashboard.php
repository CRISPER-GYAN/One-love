<?php
// Start session before any output
session_start();
require 'db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

// Fetch settings securely
$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: <?= htmlspecialchars($settings['background_color'] ?? '#f4f6fb') ?>; color: #222; font-family: <?= htmlspecialchars($settings['font_family'] ?? 'Segoe UI, Arial, sans-serif') ?>; }
        header { background: <?= htmlspecialchars($settings['primary_color'] ?? '#2a4d8f') ?>; }
        .container { background: #fff; }
        h2 { color: <?= htmlspecialchars($settings['primary_color'] ?? '#2a4d8f') ?>; }
        .btn, button { background: <?= htmlspecialchars($settings['button_color'] ?? '#2a4d8f') ?>; color: <?= htmlspecialchars($settings['button_text_color'] ?? '#fff') ?>; }
        table th { background: <?= htmlspecialchars($settings['secondary_color'] ?? '#eaf0fa') ?>; color: <?= htmlspecialchars($settings['primary_color'] ?? '#2a4d8f') ?>; }
        footer { background: <?= htmlspecialchars($settings['secondary_color'] ?? '#eaf0fa') ?>; color: <?= htmlspecialchars($settings['primary_color'] ?? '#2a4d8f') ?>; }
    </style>
</head>
<body>
<header>
    <h1>Teacher Dashboard</h1>
</header>
<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? '') ?>!</h2>
    <ul>
        <li><a href="teacher_upload_results.php" class="btn">Upload Student Results</a></li>
        <li><a href="teacher_assignments.php" class="btn">Give Assignment</a></li>
        <li><a href="admin_attendance.php" class="btn">Attendance Management</a></li>
        <li><a href="teacher_settings.php" class="btn">Teacher Settings / Reset Password</a></li>
    </ul>
</div>
<footer>
    &copy; <?= date('Y') ?> School Management System
</footer>
</body>
</html>