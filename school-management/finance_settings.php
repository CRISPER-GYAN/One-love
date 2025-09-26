<?php
session_start();
require 'db.php';
$settings = $conn->query('SELECT * FROM settings LIMIT 1')->fetch_assoc();
$finance_managers = isset($settings['finance_managers']) ? json_decode($settings['finance_managers'], true) : [];
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'finance' || !in_array($_SESSION['user_name'], $finance_managers)) {
    header('Location: login.php');
    exit();
}
// Handle school fees update per class
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['school_fees'] as $class_id => $fee) {
        $stmt = $conn->prepare('UPDATE classes SET school_fees=? WHERE id=?');
        $stmt->bind_param('di', $fee, $class_id);
        $stmt->execute();
        $stmt->close();
    }
    $success = 'School fees updated for all classes.';
}
$classes = $conn->query('SELECT * FROM classes');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Settings - School Fees Per Class</title>
</head>
<body>
    <h2>Set School Fees Per Class</h2>
    <?php if (!empty($success)) echo '<p style="color:green;">'.$success.'</p>'; ?>
    <form method="post">
        <table border="1">
            <tr><th>Class Name</th><th>School Fees</th></tr>
            <?php while ($row = $classes->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['class_name']) ?></td>
                <td><input type="number" name="school_fees[<?= $row['id'] ?>]" value="<?= $row['school_fees'] ?? 0 ?>" step="0.01" required></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <button type="submit">Save Fees</button>
    </form>
    <p><a href="finance_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
