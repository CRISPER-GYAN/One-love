<?php
session_start();
require 'db.php';
$user_type = $_SESSION['user_type'] ?? '';
$user_id = $_SESSION['user'] ?? '';
if ($user_type === 'student') {
    // Get student's class and subjects
    $student = $conn->query("SELECT class_id FROM students WHERE id = $user_id")->fetch_assoc();
    $class_id = $student['class_id'];
    $assignments = $conn->query("SELECT a.*, s.name as subject_name, t.name as teacher_name FROM assignments a JOIN subjects s ON a.subject_id = s.id JOIN staff t ON a.teacher_id = t.id WHERE a.class_id = $class_id ORDER BY a.due_date DESC");
} elseif ($user_type === 'parent') {
    // Get child(ren) class and subjects
    $children = $conn->query("SELECT id, name, class_id FROM students WHERE parent_id = $user_id");
    $assignments = [];
    while ($child = $children->fetch_assoc()) {
        $child_assignments = $conn->query("SELECT a.*, s.name as subject_name, t.name as teacher_name FROM assignments a JOIN subjects s ON a.subject_id = s.id JOIN staff t ON a.teacher_id = t.id WHERE a.class_id = {$child['class_id']} ORDER BY a.due_date DESC");
        while ($row = $child_assignments->fetch_assoc()) {
            $row['student_name'] = $child['name'];
            $assignments[] = $row;
        }
    }
} else {
    echo "<p>Unauthorized access.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Assignments</title>
</head>
<body>
    <h2>Assignments</h2>
    <?php
    if ($user_type === 'student') {
        if ($assignments->num_rows > 0) {
            echo '<table border="1"><tr><th>Subject</th><th>Title</th><th>Description</th><th>Due Date</th><th>Teacher</th></tr>';
            while ($row = $assignments->fetch_assoc()) {
                echo '<tr><td>'.htmlspecialchars($row['subject_name']).'</td><td>'.htmlspecialchars($row['title']).'</td><td>'.htmlspecialchars($row['description']).'</td><td>'.htmlspecialchars($row['due_date']).'</td><td>'.htmlspecialchars($row['teacher_name']).'</td></tr>';
            }
            echo '</table>';
        } else {
            echo '<p>No assignments found.</p>';
        }
    } elseif ($user_type === 'parent') {
        if (count($assignments) > 0) {
            echo '<table border="1"><tr><th>Student</th><th>Subject</th><th>Title</th><th>Description</th><th>Due Date</th><th>Teacher</th></tr>';
            foreach ($assignments as $row) {
                echo '<tr><td>'.htmlspecialchars($row['student_name']).'</td><td>'.htmlspecialchars($row['subject_name']).'</td><td>'.htmlspecialchars($row['title']).'</td><td>'.htmlspecialchars($row['description']).'</td><td>'.htmlspecialchars($row['due_date']).'</td><td>'.htmlspecialchars($row['teacher_name']).'</td></tr>';
            }
            echo '</table>';
        } else {
            echo '<p>No assignments found for your child(ren).</p>';
        }
    }
    ?>
    <p><a href="student_portal.php" class="btn">Back to Portal</a></p>
</body>
</html>
