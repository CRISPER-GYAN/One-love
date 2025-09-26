<?php
session_start();
require 'db.php';
$settings = $conn->query('SELECT * FROM settings LIMIT 1')->fetch_assoc();
$admission_managers = isset($settings['admission_managers']) ? json_decode($settings['admission_managers'], true) : [];
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff' || !in_array($_SESSION['user_name'], $admission_managers)) {
    header('Location: login.php');
    exit();
}
// List all admission applications
$applications = $conn->query('SELECT * FROM admission_applications');
// Handle application status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = $_POST['app_id'] ?? '';
    $status = $_POST['status'] ?? '';
    if ($app_id && $status) {
        $stmt = $conn->prepare('UPDATE admission_applications SET status=? WHERE id=?');
        $stmt->bind_param('si', $status, $app_id);
        $stmt->execute();
        $stmt->close();
        $success = 'Application status updated.';
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
    <?php if (!empty($success)) echo '<p style="color:green;">'.$success.'</p>'; ?>
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
                    <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
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
</body>
</html>
