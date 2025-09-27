<?php
session_start();
require 'db.php';

// Only allow logged-in staff, headmaster, admin, or teacher
$allowed_roles = ['staff', 'headmaster', 'headmistress', 'admin', 'teacher'];
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowed_roles)) {
    header('Location: login.php');
    exit();
}

// Validate and get student ID
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$student_id) {
    echo '<div style="color:#c00;text-align:center;margin-top:2em;">Invalid student ID.</div>';
    exit();
}

// Fetch student info
$stmt = $conn->prepare('SELECT * FROM students WHERE id=?');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    echo '<div style="color:#c00;text-align:center;margin-top:2em;">Student not found.</div>';
    exit();
}

// Fetch results
$results = [];
$stmt = $conn->prepare('SELECT subject, score FROM results WHERE student_id=?');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $results[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile - <?= htmlspecialchars($student['name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; font-family: Segoe UI, Arial, sans-serif; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; padding: 2em; border-radius: 12px; box-shadow: 0 2px 8px #ccc; }
        h2 { color: #2a4d8f; text-align: center; }
        .profile-pic { max-width: 90px; border-radius: 50%; display: block; margin: 0 auto 1em auto; }
        .info { margin-bottom: 1.5em; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5em; }
        th, td { padding: 0.7em 1em; border-bottom: 1px solid #eaf0fa; text-align: left; }
        th { background: #eaf0fa; color: #2a4d8f; }
        .back-link { display: inline-block; margin-top: 1.5em; color: #2a4d8f; text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>Student Profile</h2>
    <?php if ($student['profile_picture']): ?>
        <img src="<?= htmlspecialchars($student['profile_picture']) ?>" alt="Photo" class="profile-pic">
    <?php endif; ?>
    <div class="info">
        <strong>Name:</strong> <?= htmlspecialchars($student['name']) ?><br>
        <strong>Email:</strong> <?= htmlspecialchars($student['email']) ?><br>
        <strong>Age:</strong> <?= htmlspecialchars($student['age']) ?><br>
        <strong>Gender:</strong> <?= htmlspecialchars($student['gender']) ?><br>
        <strong>Grade:</strong> <?= htmlspecialchars($student['grade']) ?><br>
    </div>
    <h3>Results</h3>
    <table>
        <tr><th>Subject</th><th>Score</th></tr>
        <?php if ($results): foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td><?= htmlspecialchars($row['score']) ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="2">No results found.</td></tr>
        <?php endif; ?>
    </table>
    <a href="students.php" class="back-link">&larr; Back to Students List</a>
</div>
</body>
</html>
