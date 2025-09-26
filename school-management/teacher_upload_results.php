<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: login.php');
    exit();
}
$teacher_id = $_SESSION['user'];
// Get classes and subjects assigned to this teacher
$assignments = $conn->query("SELECT cs.class_id, c.class_name, cs.subject_id, s.name as subject_name FROM class_subjects cs JOIN classes c ON cs.class_id = c.id JOIN subjects s ON cs.subject_id = s.id WHERE cs.teacher_id = $teacher_id");
// Handle result upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'] ?? '';
    $subject_id = $_POST['subject_id'] ?? '';
    foreach ($_POST['score'] as $student_id => $score) {
        $stmt = $conn->prepare('INSERT INTO results (student_id, subject, score) VALUES (?, ?, ?)');
        $stmt->bind_param('isd', $student_id, $_POST['subject_name'], $score);
        $stmt->execute();
        $stmt->close();
    }
    $success = 'Results uploaded.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Student Results</title>
</head>
<body>
    <h2>Upload Results for Assigned Classes & Subjects</h2>
    <?php if (!empty($success)) echo '<p style="color:green;">'.$success.'</p>'; ?>
    <form method="post">
        <label>Class & Subject:
            <select name="assignment" required onchange="this.form.submit()">
                <option value="">Select</option>
                <?php while ($row = $assignments->fetch_assoc()): ?>
                <option value="<?= $row['class_id'] ?>-<?= $row['subject_id'] ?>" <?= (isset($_POST['assignment']) && $_POST['assignment'] == $row['class_id'].'-'.$row['subject_id']) ? 'selected' : '' ?>><?= htmlspecialchars($row['class_name']) ?> - <?= htmlspecialchars($row['subject_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </label>
    </form>
    <?php
    if (isset($_POST['assignment'])) {
        list($class_id, $subject_id) = explode('-', $_POST['assignment']);
        $students = $conn->query("SELECT id, name FROM students WHERE class_id = $class_id");
        $subject_name = $conn->query("SELECT name FROM subjects WHERE id = $subject_id")->fetch_assoc()['name'];
        echo '<form method="post">';
        echo '<input type="hidden" name="class_id" value="'.$class_id.'">';
        echo '<input type="hidden" name="subject_id" value="'.$subject_id.'">';
        echo '<input type="hidden" name="subject_name" value="'.$subject_name.'">';
        echo '<table border="1"><tr><th>Student</th><th>Score</th></tr>';
        while ($stu = $students->fetch_assoc()) {
            echo '<tr><td>'.htmlspecialchars($stu['name']).'</td><td><input type="number" name="score['.$stu['id'].']" min="0" max="100" required></td></tr>';
        }
        echo '</table><button type="submit">Upload Results</button></form>';
    }
    ?>
    <p><a href="teacher_dashboard.php" class="btn">Back to Dashboard</a> | <a href="teacher_assignments.php" class="btn">Give Assignment</a></p>
</body>
</html>
