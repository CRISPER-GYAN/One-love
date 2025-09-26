<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
// Create teacher
if (isset($_POST['create_teacher'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($name && $email && $subject && $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO teachers (name, email, subject, profile_picture) VALUES (?, ?, ?, "")');
        $stmt->bind_param('sss', $name, $email, $subject);
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
    <title>Manage Teachers</title>
</head>
<body>
    <h2>Manage Teachers</h2>
    <?php if (!empty($success)) echo '<p style="color:green;">'.$success.'</p>'; ?>
    <h3>Create Teacher</h3>
    <form method="post">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="subject" placeholder="Main Subject" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="create_teacher">Create Teacher</button>
    </form>
    <h3>Assign Teacher to Class</h3>
    <form method="post">
        <select name="class_id" required>
            <option value="">Select Class</option>
            <?php while ($row = $classes->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
            <?php endwhile; ?>
        </select>
        <select name="teacher_id" required>
            <option value="">Select Teacher</option>
            <?php foreach ($teachers as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="assign_class">Assign</button>
    </form>
    <h3>Assign Teacher to Subject for Class</h3>
    <form method="post">
        <select name="class_id_sub" required>
            <option value="">Select Class</option>
            <?php $classes->data_seek(0); while ($row = $classes->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
            <?php endwhile; ?>
        </select>
        <select name="subject_id_sub" required>
            <option value="">Select Subject</option>
            <?php while ($sub = $subjects->fetch_assoc()): ?>
            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <select name="teacher_id_sub" required>
            <option value="">Select Teacher</option>
            <?php $teachers->data_seek(0); foreach ($teachers as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="assign_subject_teacher">Assign</button>
    </form>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
