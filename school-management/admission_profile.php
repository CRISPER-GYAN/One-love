<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: login.php');
    exit();
}
$email = $_SESSION['user_name'];
// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    if ($new_password) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE staff SET password=? WHERE email=? AND role="admission"');
        $stmt->bind_param('ss', $hashed, $email);
        $stmt->execute();
        $stmt->close();
        $success = 'Password updated.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Manager Profile</title>
</head>
<body>
    <h2>Admission Manager Profile</h2>
    <?php if (!empty($success)) echo '<p style="color:green;">'.$success.'</p>'; ?>
    <form method="post">
        <label>Change Password: <input type="password" name="new_password" required></label><br>
        <button type="submit">Update Password</button>
    </form>
    <p><a href="staff_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
