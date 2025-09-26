<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
// Handle class creation
if (isset($_POST['create_class'])) {
    $class_name = $_POST['class_name'] ?? '';
    if ($class_name) {
        $stmt = $conn->prepare('INSERT INTO classes (class_name) VALUES (?)');
        $stmt->bind_param('s', $class_name);
        $stmt->execute();
        $stmt->close();
        $success = 'Class created.';
    }
}
// Handle subject creation
if (isset($_POST['create_subject'])) {
    $subject_name = $_POST['subject_name'] ?? '';
    if ($subject_name) {
        $stmt = $conn->prepare('INSERT INTO subjects (name) VALUES (?)');
        $stmt->bind_param('s', $subject_name);
        $stmt->execute();
        $stmt->close();
        $success = 'Subject created.';
    }
}
// Handle assigning subjects to classes
if (isset($_POST['assign_subject'])) {
    $class_id = $_POST['class_id'] ?? '';
    $subject_id = $_POST['subject_id'] ?? '';
    if ($class_id && $subject_id) {
        $stmt = $conn->prepare('INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $class_id, $subject_id);
        $stmt->execute();
        $stmt->close();
        $success = 'Subject assigned to class.';
    }
}
$classes = $conn->query('SELECT * FROM classes');
$subjects = $conn->query('SELECT * FROM subjects');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Classes & Subjects</title>
</head>
<body>
    <h2>Manage Classes & Subjects</h2>
    <?php if (!empty($success)) echo '<p style="color:green;">'.$success.'</p>'; ?>
    <h3>Create Class</h3>
    <form method="post">
        <input type="text" name="class_name" placeholder="Class Name" required>
        <button type="submit" name="create_class">Create Class</button>
    </form>
    <h3>Create Subject</h3>
    <form method="post">
        <input type="text" name="subject_name" placeholder="Subject Name" required>
        <button type="submit" name="create_subject">Create Subject</button>
    </form>
    <h3>Assign Subject to Class</h3>
    <form method="post">
        <select name="class_id" required>
            <option value="">Select Class</option>
            <?php while ($row = $classes->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
            <?php endwhile; ?>
        </select>
        <select name="subject_id" required>
            <option value="">Select Subject</option>
            <?php while ($sub = $subjects->fetch_assoc()): ?>
            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="assign_subject">Assign</button>
    </form>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
