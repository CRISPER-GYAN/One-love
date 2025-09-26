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
// Handle assignment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignment'])) {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $stmt = $conn->prepare('INSERT INTO assignments (class_id, subject_id, teacher_id, title, description, due_date) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iiisss', $class_id, $subject_id, $teacher_id, $title, $description, $due_date);
    $stmt->execute();
    $stmt->close();
    $success = 'Assignment created.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Give Assignment</title>
</head>
<body>
    <h2>Give Assignment to Students</h2>
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
        echo '<form method="post">';
        echo '<input type="hidden" name="class_id" value="'.$class_id.'">';
        echo '<input type="hidden" name="subject_id" value="'.$subject_id.'">';
        echo '<label>Title: <input type="text" name="title" required></label><br>';
        echo '<label>Description:<br><textarea name="description" required></textarea></label><br>';
        echo '<label>Due Date: <input type="date" name="due_date" required></label><br>';
        echo '<button type="submit">Create Assignment</button>';
        echo '<input type="hidden" name="assignment" value="1">';
        echo '</form>';
    }
    ?>
    <p><a href="teacher_dashboard.php" class="btn">Back to Dashboard</a> | <a href="teacher_upload_results.php" class="btn">Upload Results</a></p>
</body>
</html>
