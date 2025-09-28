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
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; font-family: 'Segoe UI', Arial, sans-serif; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #eaf0fa; padding: 2.5em 2.5em 2em 2.5em; }
        h1, h2, h3 { color: #2a4d8f; margin-bottom: 18px; }
        .card-section { background: #eaf0fa; border-radius: 10px; padding: 2em 2em 1.5em 2em; margin-bottom: 2em; box-shadow: 0 1px 6px #eaf0fa; }
        label { display: block; margin: 1.2em 0 0.5em; font-weight: 500; }
        input[type="text"], select { width: 100%; padding: 0.7em; border-radius: 6px; border: 1px solid #d1d5db; background: #f9fafb; font-size: 1rem; margin-bottom: 1em; }
        button { background: #2a4d8f; color: #fff; border: none; border-radius: 6px; padding: 10px 28px; font-size: 1rem; cursor: pointer; margin-top: 1em; }
        button:hover { background: #18305c; }
        .success { color: #1a7f37; background: #e6f4ea; padding: 10px; border-radius: 6px; margin-bottom: 16px; }
        .dashboard-link { display: inline-block; margin-top: 1.5em; color: #2a4d8f; text-decoration: none; font-weight: 500; }
        .dashboard-link:hover { text-decoration: underline; }
        @media (max-width: 600px) {
            .container { padding: 1em; }
            .card-section { padding: 1em; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align:center;">Manage Classes & Subjects</h1>
        <?php if (!empty($success)) echo '<div class="success">'.htmlspecialchars($success).'</div>'; ?>
        <div class="card-section">
            <h2>Create New Class</h2>
            <form method="post" autocomplete="off">
                <label for="class_name">Class Name</label>
                <input type="text" id="class_name" name="class_name" placeholder="e.g. JSS1, Grade 5" required>
                <button type="submit" name="create_class">Create Class</button>
            </form>
        </div>
        <div class="card-section">
            <h2>Create New Subject</h2>
            <form method="post" autocomplete="off">
                <label for="subject_name">Subject Name</label>
                <input type="text" id="subject_name" name="subject_name" placeholder="e.g. Mathematics" required>
                <button type="submit" name="create_subject">Create Subject</button>
            </form>
        </div>
        <div class="card-section">
            <h2>Assign Subject to Class</h2>
            <form method="post">
                <label for="class_id">Select Class</label>
                <select name="class_id" id="class_id" required>
                    <option value="">Select Class</option>
                    <?php 
                    $classes2 = $conn->query('SELECT * FROM classes');
                    while ($row = $classes2->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="subject_id">Select Subject</label>
                <select name="subject_id" id="subject_id" required>
                    <option value="">Select Subject</option>
                    <?php 
                    $subjects2 = $conn->query('SELECT * FROM subjects');
                    while ($sub = $subjects2->fetch_assoc()): ?>
                    <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="assign_subject">Assign Subject</button>
            </form>
        </div>
        <a href="admin_dashboard.php" class="dashboard-link">&larr; Back to Dashboard</a>
    </div>
</body>
</html>
