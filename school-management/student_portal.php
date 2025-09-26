<?php
session_start();
require 'db.php';
$student_id = $_SESSION['user'];
$settings = $conn->query('SELECT * FROM settings LIMIT 1')->fetch_assoc();
// Get student's class subjects
$student_class = $conn->query("SELECT class_id FROM students WHERE id = $student_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal</title>
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
    <h1>Student Portal</h1>
</header>
<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? '') ?>!</h2>
    <?php
    if ($student_class && $student_class['class_id']) {
        $subjects = $conn->query("SELECT s.name FROM class_subjects cs JOIN subjects s ON cs.subject_id = s.id WHERE cs.class_id = {$student_class['class_id']}");
        echo '<h2>Subjects for Your Class</h2><ul>';
        while ($sub = $subjects->fetch_assoc()) {
            echo '<li>' . htmlspecialchars($sub['name']) . '</li>';
        }
        echo '</ul>';
    }
    ?>
    <ul>
        <li><a href="view_assignments.php" class="btn">View Assignments</a></li>
        <li><a href="dashboard.php" class="btn">Back to Dashboard</a></li>
    </ul>
</div>
<footer>
    &copy; <?= date('Y') ?> School Management System
</footer>
</body>
</html>
