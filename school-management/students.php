<?php
session_start();
require 'db.php';

// Only allow logged-in staff, headmaster, admin, or teacher
$allowed_roles = ['staff', 'headmaster', 'headmistress', 'admin', 'teacher'];
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowed_roles)) {
    header('Location: login.php');
    exit();
}

// Search/filter logic
$search = trim($_GET['search'] ?? '');
$students = [];
if ($search) {
    $like = "%$search%";
    $stmt = $conn->prepare('SELECT id, name, email, age, gender, grade, profile_picture FROM students WHERE name LIKE ? OR email LIKE ? ORDER BY name');
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
} else {
    $res = $conn->query('SELECT id, name, email, age, gender, grade, profile_picture FROM students ORDER BY name');
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Students</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; font-family: Segoe UI, Arial, sans-serif; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; padding: 2em; border-radius: 12px; box-shadow: 0 2px 8px #ccc; }
        h2 { color: #2a4d8f; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5em; }
        th, td { padding: 0.7em 1em; border-bottom: 1px solid #eaf0fa; text-align: left; }
        th { background: #eaf0fa; color: #2a4d8f; }
        img { max-width: 40px; border-radius: 50%; }
        @media (max-width: 700px) {
            .container { padding: 0.5em; }
            table, th, td { font-size: 0.95em; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>All Students</h2>
    <form method="get" style="margin-bottom:1.5em;text-align:right;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email" style="padding:0.5em 1em;border-radius:6px;border:1px solid #bfcbe2;">
        <button type="submit" style="padding:0.5em 1.5em;border-radius:6px;background:#2a4d8f;color:#fff;border:none;">Search</button>
    </form>
    <table>
        <tr>
            <th>Photo</th>
            <th>Name</th>
            <th>Email</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Grade</th>
        </tr>
        <?php if ($students): foreach ($students as $student): ?>
            <tr>
                <td><?php if ($student['profile_picture']): ?><img src="<?= htmlspecialchars($student['profile_picture']) ?>" alt="Photo"><?php endif; ?></td>
                <td><a href="student_profile.php?id=<?= htmlspecialchars($student['id']) ?>" style="color:#2a4d8f;text-decoration:underline;"><?= htmlspecialchars($student['name']) ?></a></td>
                <td><?= htmlspecialchars($student['email']) ?></td>
                <td><?= htmlspecialchars($student['age']) ?></td>
                <td><?= htmlspecialchars($student['gender']) ?></td>
                <td><?= htmlspecialchars($student['grade']) ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="6">No students found.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
