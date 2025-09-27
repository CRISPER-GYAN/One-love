<?php
session_start();
require 'db.php';

// Only allow logged-in users (student, teacher, headmaster/headmistress, staff)
$allowed_roles = ['student', 'teacher', 'headmaster', 'headmistress', 'staff', 'admin'];
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowed_roles)) {
    header('Location: login.php');
    exit();
}

// Determine whose report card to show
$student_id = null;
if ($_SESSION['user_type'] === 'student') {
    $student_id = $_SESSION['user'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
}

// Fetch student list for staff/headmaster/teacher
$students = [];
if ($_SESSION['user_type'] !== 'student') {
    $res = $conn->query('SELECT id, name FROM students ORDER BY name');
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch student info and results
$student = null;
$results = [];
if ($student_id) {
    $stmt = $conn->prepare('SELECT * FROM students WHERE id=?');
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $stmt = $conn->prepare('SELECT subject, score FROM results WHERE student_id=?');
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; font-family: Segoe UI, Arial, sans-serif; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; padding: 2em; border-radius: 12px; box-shadow: 0 2px 8px #ccc; }
        h2 { color: #2a4d8f; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5em; }
        th, td { padding: 0.7em 1em; border-bottom: 1px solid #eaf0fa; text-align: left; }
        th { background: #eaf0fa; color: #2a4d8f; }
        .info { margin-bottom: 1.5em; }
        .print-btn { background: #2a4d8f; color: #fff; border: none; padding: 0.7em 2em; border-radius: 4px; margin-top: 1em; cursor: pointer; float: right; }
        @media print { .print-btn, form { display: none !important; } }
    </style>
</head>
<body>
<div class="container">
    <h2>Student Report Card</h2>
    <?php if ($_SESSION['user_type'] !== 'student'): ?>
        <form method="post" style="margin-bottom:2em;">
            <label for="student_id">Select Student:</label>
            <select name="student_id" id="student_id" required>
                <option value="">-- Select --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?= htmlspecialchars($s['id']) ?>" <?= ($student_id == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View</button>
        </form>
    <?php endif; ?>
    <?php if ($student): ?>
        <div class="info">
            <strong>Name:</strong> <?= htmlspecialchars($student['name']) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($student['email']) ?><br>
            <strong>Age:</strong> <?= htmlspecialchars($student['age']) ?><br>
            <strong>Gender:</strong> <?= htmlspecialchars($student['gender']) ?><br>
            <strong>Grade:</strong> <?= htmlspecialchars($student['grade']) ?><br>
        </div>
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
        <button class="print-btn" onclick="window.print()">Print</button>
    <?php elseif ($student_id): ?>
        <div style="color:#c00;">Student not found.</div>
    <?php endif; ?>
</div>
</body>
</html>
