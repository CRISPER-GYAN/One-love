<?php
// Admin attendance management for students and teachers
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
$date = date('Y-m-d');
// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_type'])) {
    $type = $_POST['attendance_type'];
    $ids = $_POST['ids'] ?? [];
    foreach ($ids as $id => $status) {
        if ($type === 'student') {
            $stmt = $conn->prepare('REPLACE INTO student_attendance (student_id, date, status) VALUES (?, ?, ?)');
            $stmt->bind_param('iss', $id, $date, $status);
        } else {
            $stmt = $conn->prepare('REPLACE INTO teacher_attendance (teacher_id, date, status) VALUES (?, ?, ?)');
            $stmt->bind_param('iss', $id, $date, $status);
        }
        $stmt->execute();
        $stmt->close();
    }
    $success = 'Attendance updated.';
}
// Fetch students and teachers
$students = $conn->query('SELECT id, name FROM students ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$teachers = $conn->query('SELECT id, name FROM teachers ORDER BY name')->fetch_all(MYSQLI_ASSOC);
// Fetch today's attendance
$student_attendance = [];
$res = $conn->query("SELECT student_id, status FROM student_attendance WHERE date='$date'");
while ($row = $res->fetch_assoc()) $student_attendance[$row['student_id']] = $row['status'];
$teacher_attendance = [];
$res = $conn->query("SELECT teacher_id, status FROM teacher_attendance WHERE date='$date'");
while ($row = $res->fetch_assoc()) $teacher_attendance[$row['teacher_id']] = $row['status'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Attendance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Attendance Management</h1></header>
<div class="container">
    <?php if (!empty($success)) echo '<div class="success">'.$success.'</div>'; ?>
    <h2>Mark Student Attendance (<?= htmlspecialchars($date) ?>)</h2>
    <form method="post">
        <input type="hidden" name="attendance_type" value="student">
        <table><tr><th>Name</th><th>Status</th></tr>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <td>
                    <select name="ids[<?= $student['id'] ?>]">
                        <?php foreach (["Present","Absent","Late","Excused"] as $status): ?>
                            <option value="<?= $status ?>" <?= ($student_attendance[$student['id']] ?? "Present") == $status ? "selected" : "" ?>><?= $status ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        <?php endforeach; ?>
        </table>
        <button type="submit">Save Student Attendance</button>
    </form>
    <h2>Mark Teacher Attendance (<?= htmlspecialchars($date) ?>)</h2>
    <form method="post">
        <input type="hidden" name="attendance_type" value="teacher">
        <table><tr><th>Name</th><th>Status</th></tr>
        <?php foreach ($teachers as $teacher): ?>
            <tr>
                <td><?= htmlspecialchars($teacher['name']) ?></td>
                <td>
                    <select name="ids[<?= $teacher['id'] ?>]">
                        <?php foreach (["Present","Absent","Late","Excused"] as $status): ?>
                            <option value="<?= $status ?>" <?= ($teacher_attendance[$teacher['id']] ?? "Present") == $status ? "selected" : "" ?>><?= $status ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        <?php endforeach; ?>
        </table>
        <button type="submit">Save Teacher Attendance</button>
    </form>
</div>
</body>
</html>
