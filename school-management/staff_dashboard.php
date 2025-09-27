<?php
// Start session before any output
session_start();
require 'db.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$settings = $conn->query('SELECT * FROM settings LIMIT 1')->fetch_assoc();
$admission_managers = isset($settings['admission_managers']) ? json_decode($settings['admission_managers'], true) : [];
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff' || !in_array($_SESSION['user_name'], $admission_managers)) {
    header('Location: login.php');
    exit();
}
// List all admission applications
$applications = $conn->query('SELECT * FROM admission_applications');
// Handle application status update with CSRF and validation
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $success = '<span style="color:red;">Invalid session token.</span>';
    } else {
        $app_id = intval($_POST['app_id'] ?? 0);
        $status = htmlspecialchars($_POST['status'] ?? '');
        if ($app_id && in_array($status, ['Pending','Accepted','Rejected'])) {
            $stmt = $conn->prepare('UPDATE admission_applications SET status=? WHERE id=?');
            $stmt->bind_param('si', $status, $app_id);
            $stmt->execute();
            $stmt->close();
            $success = 'Application status updated.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Manager Dashboard</title>
</head>
<body>
    <h2>Admission Manager Dashboard</h2>
    <p><a href="admission_profile.php">Profile & Password</a></p>
    <?php if (!empty($success)) echo '<p style="color:green;">'.htmlspecialchars($success).'</p>'; ?>
    <table border="1">
        <tr><th>Name</th><th>Email</th><th>Age</th><th>Gender</th><th>Status</th><th>Action</th></tr>
        <?php while ($row = $applications->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['age'] ?></td>
            <td><?= $row['gender'] ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="app_id" value="<?= htmlspecialchars($row['id']) ?>">
                    <select name="status">
                        <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Accepted" <?= $row['status'] == 'Accepted' ? 'selected' : '' ?>>Accept</option>
                        <option value="Rejected" <?= $row['status'] == 'Rejected' ? 'selected' : '' ?>>Reject</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <ul>
        <li><a href="teacher_upload_results.php">Upload Student Results</a></li>
        <li><a href="teacher_assignments.php">Give Assignment</a></li>
    </ul>
    <p><a href="logout.php">Logout</a></p>
        <p><a href="user_settings.php">Settings</a> <a href="logout.php">Logout</a></p>
</body>
</html>
