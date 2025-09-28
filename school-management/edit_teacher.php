<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$tid = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$tid) {
    header('Location: admin_teachers.php');
    exit();
}

$success = '';
$error = '';
$teacher = null;

// Save teacher edit
if (isset($_POST['save_teacher_edit'])) {
    $name = isset($_POST['edit_name']) ? trim($_POST['edit_name']) : '';
    $email = isset($_POST['edit_email']) ? trim($_POST['edit_email']) : '';
    $teacher_id = isset($_POST['edit_teacher_id']) ? trim($_POST['edit_teacher_id']) : '';
    $phone = isset($_POST['edit_phone']) ? trim($_POST['edit_phone']) : '';
    $qualification = isset($_POST['edit_qualification']) ? trim($_POST['edit_qualification']) : '';
    $subject = isset($_POST['edit_subject']) ? trim($_POST['edit_subject']) : '';
    $profile_picture = $teacher['profile_picture'] ?? '';
    // Handle profile picture upload
    if (isset($_FILES['edit_profile_picture']) && $_FILES['edit_profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['edit_profile_picture']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $uploadDir = 'uploads/teacher_profiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = uniqid('teacher_', true) . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['edit_profile_picture']['tmp_name'], $dest)) {
                $profile_picture = $dest;
            } else {
                $error = 'Failed to upload profile picture.';
            }
        } else {
            $error = 'Invalid profile picture format.';
        }
    }
    if ($name && $email && $teacher_id && $phone && $qualification && $subject && !$error) {
        $stmt = $conn->prepare('UPDATE teachers SET name=?, email=?, teacher_id=?, phone=?, qualification=?, subject=?, profile_picture=? WHERE id=?');
        if ($stmt) {
            $stmt->bind_param('sssssssi', $name, $email, $teacher_id, $phone, $qualification, $subject, $profile_picture, $tid);
            if ($stmt->execute()) {
                $success = 'Teacher information updated.';
            } else {
                $error = 'Failed to update teacher information.';
            }
            $stmt->close();
        } else {
            $error = 'Database error: ' . $conn->error;
        }
    } else if (!$error) {
        $error = 'All fields are required.';
    }
}

// Always fetch teacher info after update or on page load
$stmt = $conn->prepare('SELECT * FROM teachers WHERE id=?');
if ($stmt) {
    $stmt->bind_param('i', $tid);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();
    $stmt->close();
}
if (!$teacher) {
    header('Location: admin_teachers.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Teacher</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #eaf0fa; padding: 2.5em 2.5em 2em 2.5em; }
        h1 { color: #2a4d8f; }
        label { display: block; margin: 1.2em 0 0.5em; font-weight: 500; }
        input { width: 100%; padding: 0.7em; border-radius: 6px; border: 1px solid #d1d5db; background: #f9fafb; font-size: 1rem; }
        button { background: #2a4d8f; color: #fff; border: none; border-radius: 6px; padding: 10px 28px; font-size: 1rem; cursor: pointer; margin-top: 1.5em; }
        button:hover { background: #18305c; }
        .success { color: #1a7f37; background: #e6f4ea; padding: 10px; border-radius: 6px; margin-bottom: 16px; }
        .error { color: #b91c1c; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 16px; }
        .back-link { color: #2a4d8f; text-decoration: underline; font-size: 1.1em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Teacher</h1>
    <?php if ($success) echo '<div class="success">'.$success.'</div>'; ?>
    <?php if ($error) echo '<div class="error">'.$error.'</div>'; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Name:
            <input type="text" name="edit_name" value="<?= isset($teacher['name']) ? htmlspecialchars($teacher['name']) : '' ?>" required>
        </label>
        <label>Email:
            <input type="email" name="edit_email" value="<?= isset($teacher['email']) ? htmlspecialchars($teacher['email']) : '' ?>" required>
        </label>
        <label>Teacher ID:
            <input type="text" name="edit_teacher_id" value="<?= isset($teacher['teacher_id']) ? htmlspecialchars($teacher['teacher_id']) : '' ?>" required>
        </label>
        <label>Phone Number:
            <input type="text" name="edit_phone" value="<?= isset($teacher['phone']) ? htmlspecialchars($teacher['phone']) : '' ?>" required>
        </label>
        <label>Qualification:
            <input type="text" name="edit_qualification" value="<?= isset($teacher['qualification']) ? htmlspecialchars($teacher['qualification']) : '' ?>" required>
        </label>
        <label>Main Subject:
            <input type="text" name="edit_subject" value="<?= isset($teacher['subject']) ? htmlspecialchars($teacher['subject']) : '' ?>" required>
        </label>
        <label>Profile Picture:
            <input type="file" name="edit_profile_picture" accept="image/*">
            <?php if (!empty($teacher['profile_picture'])): ?>
                <br><img src="<?= htmlspecialchars($teacher['profile_picture']) ?>" alt="Profile Picture" style="width:80px;height:80px;border-radius:50%;margin-top:0.5em;">
            <?php endif; ?>
        </label>
        <button type="submit" name="save_teacher_edit">Save Changes</button>
    </form>
    <a href="admin_teachers.php" class="back-link">&larr; Back to Teachers</a>
</div>
</body>
</html>
