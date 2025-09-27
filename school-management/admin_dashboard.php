<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
$admin_name = $_SESSION['name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 32px;
            margin-top: 32px;
        }
        .dashboard-card {
            background: #eaf0fa;
            border-radius: 10px;
            box-shadow: 0 2px 8px #eaf0fa;
            padding: 32px 24px;
            text-align: center;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .dashboard-card:hover {
            box-shadow: 0 4px 24px #b6c8e6;
            transform: translateY(-4px) scale(1.03);
        }
        .dashboard-card a {
            color: #2a4d8f;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .dashboard-card .icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
            display: block;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-header .logout {
            color: #fff;
            background: #b91c1c;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1rem;
            margin-left: 16px;
        }
        .dashboard-header .logout:hover {
            background: #7f1d1d;
        }
    </style>
</head>
<body>
<header>
    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <div>
            <span style="font-size:1.1rem;">Welcome, <?= htmlspecialchars($admin_name) ?></span>
                <a href="user_settings.php" class="logout" style="background:#2a4d8f; margin-right:12px;">Settings</a>
                <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
</header>
<div class="container">
    <?php
    // Quick stats queries
    $student_count = $conn->query('SELECT COUNT(*) FROM students')->fetch_row()[0];
    $teacher_count = $conn->query('SELECT COUNT(*) FROM teachers')->fetch_row()[0];
    $staff_count = $conn->query('SELECT COUNT(*) FROM staff')->fetch_row()[0];
    $pending_admissions = 0;
    if ($conn->query("SHOW TABLES LIKE 'admission_applications' ")->num_rows) {
        $pending_admissions = $conn->query("SELECT COUNT(*) FROM admission_applications WHERE status IS NULL OR status = 'pending'")->fetch_row()[0];
    }
    ?>
    <div style="display:flex; gap:32px; justify-content:space-between; margin-bottom:32px; flex-wrap:wrap;">
        <div style="background:#eaf0fa; border-radius:10px; padding:24px 32px; min-width:180px; text-align:center;">
            <div style="font-size:2rem; font-weight:700; color:#2a4d8f;"> <?= $student_count ?> </div>
            <div style="color:#2a4d8f;">Students</div>
        </div>
        <div style="background:#eaf0fa; border-radius:10px; padding:24px 32px; min-width:180px; text-align:center;">
            <div style="font-size:2rem; font-weight:700; color:#2a4d8f;"> <?= $teacher_count ?> </div>
            <div style="color:#2a4d8f;">Teachers</div>
        </div>
        <div style="background:#eaf0fa; border-radius:10px; padding:24px 32px; min-width:180px; text-align:center;">
            <div style="font-size:2rem; font-weight:700; color:#2a4d8f;"> <?= $staff_count ?> </div>
            <div style="color:#2a4d8f;">Staff</div>
        </div>
        <div style="background:#eaf0fa; border-radius:10px; padding:24px 32px; min-width:180px; text-align:center;">
            <div style="font-size:2rem; font-weight:700; color:#2a4d8f;"> <?= $pending_admissions ?> </div>
            <div style="color:#2a4d8f;">Pending Admissions</div>
        </div>
    </div>
    <!-- Notifications/Alerts Section -->
    <div style="margin-bottom:32px;">
        <h3 style="color:#2a4d8f; margin-bottom:12px;">Notifications & Alerts</h3>
        <div style="background:#fff; border-radius:8px; box-shadow:0 1px 6px #eaf0fa; padding:18px 24px;">
        <?php
        $alerts = [];
        // Unpaid fees
        if ($conn->query("SHOW TABLES LIKE 'fees'")->num_rows) {
            $unpaid = $conn->query("SELECT COUNT(*) FROM fees WHERE status = 'unpaid' OR status IS NULL")->fetch_row()[0];
            if ($unpaid > 0) {
                $alerts[] = "$unpaid students have unpaid fees.";
            }
        }
        // Low attendance (example: attendance < 75%)
        if ($conn->query("SHOW TABLES LIKE 'students'")->num_rows && $conn->query("SHOW COLUMNS FROM students LIKE 'attendance'")->num_rows) {
            $low_attendance = $conn->query("SELECT COUNT(*) FROM students WHERE attendance IS NOT NULL AND attendance < 75")->fetch_row()[0];
            if ($low_attendance > 0) {
                $alerts[] = "$low_attendance students have attendance below 75%.";
            }
        }
        // Pending admissions
        if ($conn->query("SHOW TABLES LIKE 'admission_applications'")->num_rows) {
            $pending = $conn->query("SELECT COUNT(*) FROM admission_applications WHERE status IS NULL OR status = 'pending'")->fetch_row()[0];
            if ($pending > 0) {
                $alerts[] = "$pending admission applications are pending approval.";
            }
        }
        // Show alerts
        if ($alerts) {
            echo '<ul style="margin:0; padding-left:18px;">';
            foreach ($alerts as $alert) {
                echo '<li style="color:#b91c1c; font-weight:500; margin-bottom:8px;">' . htmlspecialchars($alert) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<span style="color:#1a7f37;">No urgent notifications at this time.</span>';
        }
        ?>
        </div>
    </div>
    <!-- Recent Activity Feed -->
    <div style="margin-bottom:32px;">
        <h3 style="color:#2a4d8f; margin-bottom:12px;">Recent Activity</h3>
        <div style="background:#fff; border-radius:8px; box-shadow:0 1px 6px #eaf0fa; padding:18px 24px;">
        <?php
        // Example: Show last 5 admin logins (if you have a logins table, otherwise show placeholder)
        $recent_logins = [];
        if ($conn->query("SHOW TABLES LIKE 'logins'")->num_rows) {
            $res = $conn->query("SELECT user_type, user_id, login_time FROM logins ORDER BY login_time DESC LIMIT 5");
            while ($row = $res->fetch_assoc()) {
                $recent_logins[] = $row;
            }
        }
        $recent_admissions = [];
        if ($conn->query("SHOW TABLES LIKE 'admission_applications'")->num_rows) {
            $res = $conn->query("SELECT name, email, created_at FROM admission_applications ORDER BY created_at DESC LIMIT 5");
            while ($row = $res->fetch_assoc()) {
                $recent_admissions[] = $row;
            }
        }
        if ($recent_logins) {
            echo '<strong>Recent Logins:</strong><ul style="margin:8px 0 16px 0;">';
            foreach ($recent_logins as $login) {
                echo '<li>' . htmlspecialchars($login['user_type']) . ' ID: ' . htmlspecialchars($login['user_id']) . ' at ' . htmlspecialchars($login['login_time']) . '</li>';
            }
            echo '</ul>';
        }
        if ($recent_admissions) {
            echo '<strong>Recent Admissions:</strong><ul style="margin:8px 0 0 0;">';
            foreach ($recent_admissions as $adm) {
                echo '<li>' . htmlspecialchars($adm['name']) . ' (' . htmlspecialchars($adm['email']) . ') - ' . htmlspecialchars($adm['created_at']) . '</li>';
            }
            echo '</ul>';
        }
        if (!$recent_logins && !$recent_admissions) {
            echo '<em>No recent activity found.</em>';
        }
        ?>
        </div>
    </div>
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <span class="icon">&#9881;</span>
            <a href="admin_settings.php">Settings</a>
            <div>Manage system settings</div>
        </div>
        <div class="dashboard-card">
            <span class="icon">&#128197;</span>
            <a href="admin_attendance.php">Attendance Management</a>
            <div>View & manage attendance</div>
        </div>
        <div class="dashboard-card">
            <span class="icon">&#128100;</span>
            <a href="admin_teachers.php">Teachers</a>
            <div>Manage teachers</div>
        </div>
        <div class="dashboard-card">
            <span class="icon">&#128214;</span>
            <a href="students.php">Students</a>
            <div>View & manage students</div>
        </div>
        <div class="dashboard-card">
            <span class="icon">&#128221;</span>
            <a href="admission_form.php">Admissions</a>
            <div>New student admissions</div>
        </div>
        <div class="dashboard-card">
            <span class="icon">&#128179;</span>
            <a href="finance_dashboard.php">Finance</a>
            <div>Fees & finance management</div>
        </div>
        <div class="dashboard-card">
            <span class="icon">&#128202;</span>
            <a href="report_card.php">Report Cards</a>
            <div>View student results</div>
        </div>
        <div class="dashboard-card">
            <span class="icon">&#128196;</span>
            <a href="admin_class_subjects.php">Class Subjects</a>
            <div>Assign subjects to classes</div>
        </div>
        <div class="dashboard-card">
            <span class="icon">&#128188;</span>
            <a href="staff_dashboard.php">Staff Dashboard</a>
            <div>View staff portal</div>
        </div>
        <div class="dashboard-card">
            <span class="icon">&#127979;</span>
            <a href="headmaster_dashboard.php">Headmaster Dashboard</a>
            <div>View headmaster portal</div>
        </div>
    </div>
</div>
<footer>
    &copy; <?= date('Y') ?> School Management System. All rights reserved.
</footer>
</body>
</html>
