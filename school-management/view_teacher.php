<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
$teacher = null;
if (isset($_GET['id'])) {
    $tid = intval($_GET['id']);
    $stmt = $conn->prepare('SELECT * FROM teachers WHERE id=?');
    $stmt->bind_param('i', $tid);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();
    $stmt->close();
}
if (!$teacher) {
    echo '<div style="color:red;">Teacher not found.</div>';
    echo '<a href="admin_teachers.php">&larr; Back to Teachers</a>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Information</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #eaf0fa; padding: 2.5em; }
        h1 { color: #2a4d8f; }
        .info-table { width: 100%; border-collapse: collapse; margin: 24px 0; background: #fff; }
        th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; }
        th { background: #eaf0fa; color: #2a4d8f; width: 40%; }
        .back-link { color: #2a4d8f; text-decoration: underline; font-size: 1.1em; }
        .profile-pic { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 2px solid #2a4d8f; margin-bottom: 1em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Teacher Information</h1>
    <?php if (!empty($teacher['profile_picture'])): ?>
        <img src="<?= htmlspecialchars($teacher['profile_picture']) ?>" alt="Profile Picture" class="profile-pic">
    <?php endif; ?>
    <table class="info-table">
        <tr><th>Name</th><td><?= htmlspecialchars($teacher['name']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($teacher['email']) ?></td></tr>
        <tr><th>Teacher ID</th><td><?= htmlspecialchars($teacher['teacher_id']) ?></td></tr>
        <tr><th>Phone</th><td><?= htmlspecialchars($teacher['phone']) ?></td></tr>
        <tr><th>Qualification</th><td><?= htmlspecialchars($teacher['qualification']) ?></td></tr>
        <tr><th>Main Subject</th><td><?= htmlspecialchars($teacher['subject']) ?></td></tr>
    </table>
    <a href="admin_teachers.php" class="back-link">&larr; Back to Teachers</a>
</div>
</body>
</html>
