<?php
session_start();
require 'db.php';
require_once 'auth.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: login.php');
    exit();
}
$teacher_id = $_SESSION['user'];
$error = '';
$success = '';

// Fetch teacher info
$stmt = $conn->prepare('SELECT * FROM teachers WHERE id=?');
$stmt->bind_param('i', $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle password reset
if (isset($_POST['reset_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$current || !$new || !$confirm) {
        $error = 'All fields are required.';
    } elseif (!verify_password($current, $teacher['password'])) {
        $error = 'Current password is incorrect.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } else {
    $hash = create_hashed_password($new);
        $stmt = $conn->prepare('UPDATE teachers SET password=? WHERE id=?');
        $stmt->bind_param('si', $hash, $teacher_id);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: teacher_dashboard.php?password_updated=1');
            exit();
        } else {
            $error = 'Failed to update password.';
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Settings</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; }
        .container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #eaf0fa; padding: 2.5em; }
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
    <h1>Teacher Settings</h1>
    <?php if ($success) echo '<div class="success">'.$success.'</div>'; ?>
    <?php if ($error) echo '<div class="error">'.$error.'</div>'; ?>
    <form method="post">
        <h3>Reset Password</h3>
        <label>Current Password:
            <input type="password" name="current_password" required>
        </label>
        <label>New Password:
            <input type="password" name="new_password" required>
        </label>
        <label>Confirm New Password:
            <input type="password" name="confirm_password" required>
        </label>
        <button type="submit" name="reset_password">Update Password</button>
    </form>
    <a href="teacher_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</div>
</body>
</html>
