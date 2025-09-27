<?php
// Start session before any output
session_start();
require 'db.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Only allow staff (admission manager) to access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: login.php');
    exit();
}
$email = $_SESSION['user_name'];
$success = '';
$error = '';
// Handle password change securely
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid session token.';
    } else {
        $new_password = $_POST['new_password'] ?? '';
        if (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            require_once 'auth.php';
            $hashed = create_hashed_password($new_password);
            $stmt = $conn->prepare('UPDATE staff SET password=? WHERE email=? AND role="admission"');
            $stmt->bind_param('ss', $hashed, $email);
            if ($stmt->execute()) {
                $success = 'Password updated.';
            } else {
                $error = 'Database error.';
            }
            $stmt->close();
        }
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
    <?php if (!empty($success)) echo '<p style="color:green;">'.htmlspecialchars($success).'</p>'; ?>
    <?php if (!empty($error)) echo '<p style="color:red;">'.htmlspecialchars($error).'</p>'; ?>
    <form method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label>Change Password: <input type="password" name="new_password" required minlength="6"></label><br>
        <button type="submit">Update Password</button>
    </form>
    <p><a href="staff_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
