<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$tid = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = '';
$error = '';
$teacher = null;

if ($tid) {
    $stmt = $conn->prepare('SELECT * FROM teachers WHERE id=?');
    if ($stmt) {
        $stmt->bind_param('i', $tid);
        $stmt->execute();
        $result = $stmt->get_result();
        $teacher = $result->fetch_assoc();
        $stmt->close();
    }
}
if (!$teacher) {
    header('Location: admin_teachers.php');
    exit();
}

if (isset($_POST['confirm_remove']) && isset($_POST['teacher_id_remove'])) {
    $remove_id = intval($_POST['teacher_id_remove']);
    // Remove teacher from classes (set teacher_id to NULL)
    $stmt1 = $conn->prepare('UPDATE classes SET teacher_id=NULL WHERE teacher_id=?');
    if ($stmt1) {
        $stmt1->bind_param('i', $remove_id);
        $stmt1->execute();
        $stmt1->close();
    }
    // Remove teacher from class_subjects (set teacher_id to NULL)
    $stmt2 = $conn->prepare('UPDATE class_subjects SET teacher_id=NULL WHERE teacher_id=?');
    if ($stmt2) {
        $stmt2->bind_param('i', $remove_id);
        $stmt2->execute();
        $stmt2->close();
    }
    // Delete teacher from teachers table
    $stmt = $conn->prepare('DELETE FROM teachers WHERE id=?');
    if ($stmt) {
        $stmt->bind_param('i', $remove_id);
        if ($stmt->execute()) {
            $success = 'Teacher removed successfully.';
            $stmt->close();
            header('Location: admin_teachers.php?removed=1');
            exit();
        } else {
            $error = 'Failed to remove teacher.';
            $stmt->close();
        }
    } else {
        $error = 'Database error: ' . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Remove Teacher</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; }
        .container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #eaf0fa; padding: 2.5em 2.5em 2em 2.5em; }
        h1 { color: #b91c1c; }
        .danger { color: #b91c1c; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 16px; }
        .success { color: #1a7f37; background: #e6f4ea; padding: 10px; border-radius: 6px; margin-bottom: 16px; }
        .error { color: #b91c1c; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 16px; }
        .back-link { color: #2a4d8f; text-decoration: underline; font-size: 1.1em; }
        button { background: #b91c1c; color: #fff; border: none; border-radius: 6px; padding: 10px 28px; font-size: 1rem; cursor: pointer; margin-top: 1.5em; }
        button:hover { background: #7f1d1d; }
    </style>
</head>
<body>
<div class="container">
    <h1>Remove Teacher</h1>
    <?php if ($error) echo '<div class="error">'.$error.'</div>'; ?>
    <div class="danger">
        Are you sure you want to remove this teacher?<br><br>
        <strong>Name:</strong> <?= isset($teacher['name']) ? htmlspecialchars($teacher['name']) : '' ?><br>
        <strong>Teacher ID:</strong> <?= isset($teacher['email']) ? htmlspecialchars($teacher['email']) : '' ?><br>
        <strong>Phone:</strong> <?= isset($teacher['phone']) ? htmlspecialchars($teacher['phone']) : '' ?><br>
        <strong>Qualification:</strong> <?= isset($teacher['qualification']) ? htmlspecialchars($teacher['qualification']) : '' ?><br>
        <strong>Main Subject:</strong> <?= isset($teacher['subject']) ? htmlspecialchars($teacher['subject']) : '' ?><br>
    </div>
    <form method="post">
    <input type="hidden" name="teacher_id_remove" value="<?= isset($teacher['id']) ? $teacher['id'] : '' ?>">
        <button type="submit" name="confirm_remove">Yes, Remove</button>
        <a href="admin_teachers.php" class="back-link" style="margin-left:20px;">Cancel</a>
    </form>
</div>
</body>
</html>
