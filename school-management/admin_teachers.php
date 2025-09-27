<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
// Create teacher (using Teacher ID instead of email)
if (isset($_POST['create_teacher'])) {
    $name = $_POST['name'] ?? '';
    $teacher_id = $_POST['teacher_id'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($name && $teacher_id && $subject && $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO teachers (name, email, subject, profile_picture) VALUES (?, ?, ?, "")');
        $stmt->bind_param('sss', $name, $teacher_id, $subject);
        $stmt->execute();
        $stmt->close();
        $success = 'Teacher created.';
    }
}
// Assign teacher to class
if (isset($_POST['assign_class'])) {
    $class_id = $_POST['class_id'] ?? '';
    $teacher_id = $_POST['teacher_id'] ?? '';
    if ($class_id && $teacher_id) {
        $stmt = $conn->prepare('UPDATE classes SET teacher_id=? WHERE id=?');
        $stmt->bind_param('ii', $teacher_id, $class_id);
        $stmt->execute();
        $stmt->close();
        $success = 'Teacher assigned to class.';
    }
}
// Assign teacher to subject per class
if (isset($_POST['assign_subject_teacher'])) {
    $class_id = $_POST['class_id_sub'] ?? '';
    $subject_id = $_POST['subject_id_sub'] ?? '';
    $teacher_id = $_POST['teacher_id_sub'] ?? '';
    if ($class_id && $subject_id && $teacher_id) {
        $stmt = $conn->prepare('UPDATE class_subjects SET teacher_id=? WHERE class_id=? AND subject_id=?');
        $stmt->bind_param('iii', $teacher_id, $class_id, $subject_id);
        $stmt->execute();
        $stmt->close();
        $success = 'Teacher assigned to subject for class.';
    }
}
$teachers = $conn->query('SELECT * FROM teachers');
$classes = $conn->query('SELECT * FROM classes');
$subjects = $conn->query('SELECT * FROM subjects');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Teachers</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; }
        .container { max-width: 1000px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #eaf0fa; padding: 2.5em 2.5em 2em 2.5em; }
        h1, h2 { color: #2a4d8f; }
        .section { background: #eaf0fa; border-radius: 10px; padding: 2em 2em 1.5em 2em; margin-bottom: 2em; box-shadow: 0 1px 6px #eaf0fa; }
        label { display: block; margin: 1.2em 0 0.5em; font-weight: 500; }
        input, select { width: 100%; padding: 0.7em; border-radius: 6px; border: 1px solid #d1d5db; background: #f9fafb; font-size: 1rem; }
        button { background: #2a4d8f; color: #fff; border: none; border-radius: 6px; padding: 10px 28px; font-size: 1rem; cursor: pointer; margin-top: 1.5em; }
        button:hover { background: #18305c; }
        .success { color: #1a7f37; background: #e6f4ea; padding: 10px; border-radius: 6px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin: 24px 0; background: #fff; }
        th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; }
        th { background: #eaf0fa; color: #2a4d8f; }
        .back-link { color: #2a4d8f; text-decoration: underline; font-size: 1.1em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Teachers</h1>
    <?php if (!empty($success)) echo '<div class="success">'.$success.'</div>'; ?>
    <div class="section">
        <h2>Create Teacher</h2>
        <form method="post">
            <label>Name:
                <input type="text" name="name" required>
            </label>
            <label>Teacher ID:
                <input type="text" name="teacher_id" required>
            </label>
            <label>Main Subject:
                <input type="text" name="subject" required>
            </label>
            <label>Password:
                <input type="password" name="password" required>
            </label>
            <button type="submit" name="create_teacher">Create Teacher</button>
        </form>
    </div>
    <div class="section">
        <h2>Assign Teacher to Class</h2>
        <form method="post">
            <label>Class:
                <select name="class_id" required>
                    <option value="">Select Class</option>
                    <?php $classes->data_seek(0); while ($row = $classes->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
            <label>Teacher:
                <select name="teacher_id" required>
                    <option value="">Select Teacher</option>
                    <?php $teachers->data_seek(0); foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>">ID: <?= htmlspecialchars($t['email']) ?> - <?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" name="assign_class">Assign</button>
        </form>
    </div>
    <div class="section">
        <h2>Assign Teacher to Subject for Class</h2>
        <form method="post">
            <label>Class:
                <select name="class_id_sub" required>
                    <option value="">Select Class</option>
                    <?php $classes->data_seek(0); while ($row = $classes->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
            <label>Subject:
                <select name="subject_id_sub" required>
                    <option value="">Select Subject</option>
                    <?php $subjects->data_seek(0); while ($sub = $subjects->fetch_assoc()): ?>
                    <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
            <label>Teacher:
                <select name="teacher_id_sub" required>
                    <option value="">Select Teacher</option>
                    <?php $teachers->data_seek(0); foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>">ID: <?= htmlspecialchars($t['email']) ?> - <?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" name="assign_subject_teacher">Assign</button>
        </form>
    </div>
    <div class="section">
        <h2>All Teachers</h2>
        <table>
            <thead>
                <tr><th>Name</th><th>Teacher ID</th><th>Main Subject</th></tr>
            </thead>
            <tbody>
            <?php $teachers->data_seek(0); foreach ($teachers as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td><?= htmlspecialchars($t['email']) ?></td>
                    <td><?= htmlspecialchars($t['subject']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</div>
</body>
</html>
