<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'headmaster' && $_SESSION['user_type'] !== 'headmistress')) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user'];
// Fetch school info
$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();
// Fetch permissions
$features = [
    'report_card' => 'View/Approve Report Cards',
    'promote_students' => 'Promote/Repeat Students',
    'admin_settings' => 'School Settings',
    'staff_dashboard' => 'Staff Dashboard',
    'finance_dashboard' => 'Finance Dashboard',
    'admission_profile' => 'Admission Manager'
];
$permissions = [];
$res = $conn->query("SELECT feature, allowed FROM headmaster_permissions WHERE headmaster_id = $user_id");
while ($row = $res && $res->fetch_assoc()) {
    $permissions[$row['feature']] = $row['allowed'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= ($settings['headmaster_title'] ?? 'Headmaster/Headmistress') ?> Dashboard</title>
</head>
<body>
    <h2><?= ($settings['headmaster_title'] ?? 'Headmaster/Headmistress') ?> Dashboard</h2>
    <p>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? '') ?>!</p>
    <ul>
        <?php if (!isset($permissions['report_card']) || $permissions['report_card']): ?>
        <li><a href="report_card.php">View/Approve Report Cards</a></li>
        <?php endif; ?>
        <?php if (!isset($permissions['promote_students']) || $permissions['promote_students']): ?>
        <li><a href="promote_students.php">Promote/Repeat Students</a></li>
        <?php endif; ?>
        <?php if (!isset($permissions['admin_settings']) || $permissions['admin_settings']): ?>
        <li><a href="admin_settings.php">School Settings</a></li>
        <?php endif; ?>
        <?php if (!isset($permissions['staff_dashboard']) || $permissions['staff_dashboard']): ?>
        <li><a href="staff_dashboard.php">Staff Dashboard</a></li>
        <?php endif; ?>
        <?php if (!isset($permissions['finance_dashboard']) || $permissions['finance_dashboard']): ?>
        <li><a href="finance_dashboard.php">Finance Dashboard</a></li>
        <?php endif; ?>
        <?php if (!isset($permissions['admission_profile']) || $permissions['admission_profile']): ?>
        <li><a href="admission_profile.php">Admission Manager</a></li>
        <?php endif; ?>
    </ul>
    <p><a href="logout.php" class="btn">Logout</a></p>
</body>
</html>
