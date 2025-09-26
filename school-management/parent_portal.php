<?php
session_start();
require 'db.php';
$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parent Portal</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: <?= $settings['background_color'] ?>; color: #222; font-family: <?= $settings['font_family'] ?>; }
        header { background: <?= $settings['primary_color'] ?>; }
        .container { background: #fff; }
        h2 { color: <?= $settings['primary_color'] ?>; }
        .btn, button { background: <?= $settings['button_color'] ?>; color: <?= $settings['button_text_color'] ?>; }
        table th { background: <?= $settings['secondary_color'] ?>; color: <?= $settings['primary_color'] ?>; }
        footer { background: <?= $settings['secondary_color'] ?>; color: <?= $settings['primary_color'] ?>; }
    </style>
</head>
<body>
<header>
    <h1>Parent Portal</h1>
</header>
<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? '') ?>!</h2>
    <ul>
        <li><a href="view_assignments.php" class="btn">View Assignments</a></li>
    </ul>
</div>
<footer>
    &copy; <?= date('Y') ?> School Management System
</footer>
</body>
</html>