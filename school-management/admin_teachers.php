<?php
session_start();
require 'db.php';
require_once 'auth.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
// Create teacher (using Teacher ID, phone number, and qualification)
if (isset($_POST['create_teacher'])) {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $teacher_id = isset($_POST['teacher_id']) ? $_POST['teacher_id'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $qualification = isset($_POST['qualification']) ? $_POST['qualification'] : '';
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $profile_picture = '';
    $error = '';
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $uploadDir = 'uploads/teacher_profiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = uniqid('teacher_', true) . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest)) {
                $profile_picture = $dest;
            } else {
                $error = 'Failed to upload profile picture.';
            }
        } else {
            $error = 'Invalid profile picture format.';
        }
    }
    if ($name && $email && $teacher_id && $phone && $qualification && $subject && $password && !$error) {
        $hashed = create_hashed_password($password);
        // Check if all columns exist before insert
        $columnsRes = $conn->query("SHOW COLUMNS FROM teachers");
        $columns = array();
        while ($col = $columnsRes->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
        $sql = '';
        $bindTypes = '';
        $params = [];
        if (in_array('phone', $columns) && in_array('qualification', $columns) && in_array('profile_picture', $columns) && in_array('teacher_id', $columns) && in_array('password', $columns)) {
            $sql = 'INSERT INTO teachers (name, email, teacher_id, phone, qualification, subject, password, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
            $bindTypes = 'ssssssss';
            $params = [$name, $email, $teacher_id, $phone, $qualification, $subject, $hashed, $profile_picture];
        } elseif (in_array('phone', $columns) && in_array('qualification', $columns) && in_array('teacher_id', $columns) && in_array('password', $columns)) {
            $sql = 'INSERT INTO teachers (name, email, teacher_id, phone, qualification, subject, password) VALUES (?, ?, ?, ?, ?, ?, ?)';
            $bindTypes = 'sssssss';
            $params = [$name, $email, $teacher_id, $phone, $qualification, $subject, $hashed];
        } elseif (in_array('phone', $columns) && in_array('teacher_id', $columns) && in_array('password', $columns)) {
            $sql = 'INSERT INTO teachers (name, email, teacher_id, phone, subject, password) VALUES (?, ?, ?, ?, ?, ?)';
            $bindTypes = 'ssssss';
            $params = [$name, $email, $teacher_id, $phone, $subject, $hashed];
        } elseif (in_array('teacher_id', $columns) && in_array('password', $columns)) {
            $sql = 'INSERT INTO teachers (name, email, teacher_id, subject, password) VALUES (?, ?, ?, ?, ?)';
            $bindTypes = 'sssss';
            $params = [$name, $email, $teacher_id, $subject, $hashed];
        } else {
            $error = 'Teachers table missing required columns.';
        }
        if ($sql) {
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($bindTypes, ...$params);
                if ($stmt->execute()) {
                    $success = 'Teacher created.';
                } else {
                    $error = 'Error: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $error = 'Database error: ' . htmlspecialchars($conn->error);
            }
        }
    } else if (!$error) {
        $error = 'All fields are required.';
    }
}
// Assign teacher to class
if (isset($_POST['assign_class'])) {
    $class_id = isset($_POST['class_id']) ? $_POST['class_id'] : '';
    $teacher_id = isset($_POST['teacher_id']) ? $_POST['teacher_id'] : '';
    if ($class_id && $teacher_id) {
        $stmt = $conn->prepare('UPDATE classes SET teacher_id=? WHERE id=?');
        $stmt->bind_param('ii', $teacher_id, $class_id);
        $stmt->execute();
        $stmt->close();
        $success = 'Teacher assigned to class.';
    }
}
// Assign teacher to subject per class
if (isset($_POST['assign_subject_teacher'])) {
    $class_id = isset($_POST['class_id_sub']) ? $_POST['class_id_sub'] : '';
    $subject_id = isset($_POST['subject_id_sub']) ? $_POST['subject_id_sub'] : '';
    $teacher_id = isset($_POST['teacher_id_sub']) ? $_POST['teacher_id_sub'] : '';
    if ($class_id && $subject_id && $teacher_id) {
        $stmt = $conn->prepare('UPDATE class_subjects SET teacher_id=? WHERE class_id=? AND subject_id=?');
        $stmt->bind_param('iii', $teacher_id, $class_id, $subject_id);
        $stmt->execute();
        $stmt->close();
        $success = 'Teacher assigned to subject for class.';
    }
}
$teachers = $conn->query('SELECT * FROM teachers');
$classes = $conn->query('SELECT * FROM classes');
$subjects = $conn->query('SELECT * FROM subjects');

// Remove teacher
if (isset($_POST['remove_teacher']) && isset($_POST['teacher_id_remove'])) {
    $tid = intval($_POST['teacher_id_remove']);
    $conn->query("DELETE FROM teachers WHERE id=$tid");
    $success = 'Teacher removed.';
    $teachers = $conn->query('SELECT * FROM teachers');
}
// Unassign teacher from class
if (isset($_POST['unassign_class']) && isset($_POST['class_id_unassign'])) {
    $cid = intval($_POST['class_id_unassign']);
    $conn->query("UPDATE classes SET teacher_id=NULL WHERE id=$cid");
    $success = 'Teacher unassigned from class.';
}
// Unassign teacher from subject/class
if (isset($_POST['unassign_subject_teacher']) && isset($_POST['class_id_unassign_sub']) && isset($_POST['subject_id_unassign_sub'])) {
    $cid = intval($_POST['class_id_unassign_sub']);
    $sid = intval($_POST['subject_id_unassign_sub']);
    $conn->query("UPDATE class_subjects SET teacher_id=NULL WHERE class_id=$cid AND subject_id=$sid");
    $success = 'Teacher unassigned from subject/class.';
}
// Redirect to edit_teacher.php when Edit is clicked
if (isset($_POST['edit_teacher']) && isset($_POST['teacher_id_edit'])) {
    $tid = intval($_POST['teacher_id_edit']);
    header('Location: edit_teacher.php?id=' . $tid);
    exit();
}
// Save teacher edit
if (isset($_POST['save_teacher_edit']) && isset($_POST['teacher_id_edit'])) {
    $tid = intval($_POST['teacher_id_edit']);
    $name = isset($_POST['edit_name']) ? $_POST['edit_name'] : '';
    $teacher_id = isset($_POST['edit_teacher_id']) ? $_POST['edit_teacher_id'] : '';
    $phone = isset($_POST['edit_phone']) ? $_POST['edit_phone'] : '';
    $qualification = isset($_POST['edit_qualification']) ? $_POST['edit_qualification'] : '';
    $subject = isset($_POST['edit_subject']) ? $_POST['edit_subject'] : '';
    if ($name && $teacher_id && $phone && $qualification && $subject) {
        $stmt = $conn->prepare('UPDATE teachers SET name=?, email=?, phone=?, qualification=?, subject=? WHERE id=?');
        $stmt->bind_param('sssssi', $name, $teacher_id, $phone, $qualification, $subject, $tid);
        $stmt->execute();
        $stmt->close();
        $success = 'Teacher information updated.';
        $teachers = $conn->query('SELECT * FROM teachers');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Teachers</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; }
        .container { max-width: 1000px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #eaf0fa; padding: 2.5em 2.5em 2em 2.5em; }
        h1, h2 { color: #2a4d8f; }
        .section { background: #eaf0fa; border-radius: 10px; padding: 2em 2em 1.5em 2em; margin-bottom: 2em; box-shadow: 0 1px 6px #eaf0fa; }
        label { display: block; margin: 1.2em 0 0.5em; font-weight: 500; }
        input, select { width: 100%; padding: 0.7em; border-radius: 6px; border: 1px solid #d1d5db; background: #f9fafb; font-size: 1rem; }
        button { background: #2a4d8f; color: #fff; border: none; border-radius: 6px; padding: 10px 28px; font-size: 1rem; cursor: pointer; margin-top: 1.5em; }
        button:hover { background: #18305c; }
        .success { color: #1a7f37; background: #e6f4ea; padding: 10px; border-radius: 6px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin: 24px 0; background: #fff; }
        th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; }
        th { background: #eaf0fa; color: #2a4d8f; }
        .back-link { color: #2a4d8f; text-decoration: underline; font-size: 1.1em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Teachers</h1>
    <?php if (!empty($success)) echo '<div class="success">'.$success.'</div>'; ?>
    <?php if (!empty($error)) echo '<div class="error">'.$error.'</div>'; ?>
    <div class="section">
        <h2>Create Teacher</h2>
    <form method="post" enctype="multipart/form-data">
            <label>Name:
                <input type="text" name="name" required>
            </label>
            <label>Email:
                <input type="email" name="email" required>
            </label>
            <label>Teacher ID:
                <input type="text" name="teacher_id" required>
            </label>
            <label>Phone Number:
                <input type="text" name="phone" required pattern="[0-9+\- ]{7,15}" placeholder="e.g. +233XXXXXXXXX">
            </label>
            <label>Qualification:
                <input type="text" name="qualification" required placeholder="e.g. B.Ed, M.Ed, PhD">
            </label>
            <label>Main Subject:
                <input type="text" name="subject" required>
            </label>
            <label>Password:
                <input type="password" name="password" required>
            </label>
            <label>Profile Picture:
                <input type="file" name="profile_picture" accept="image/*">
            </label>
            <button type="submit" name="create_teacher">Create Teacher</button>
        </form>
    </div>
    <div class="section">
        <h2>Assign Teacher to Class</h2>
        <form method="post">
            <label>Class:
                <select name="class_id" required>
                    <option value="">Select Class</option>
                    <?php $classes->data_seek(0); while ($row = $classes->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
            <label>Teacher:
                <select name="teacher_id" required>
                    <option value="">Select Teacher</option>
                    <?php $teachers->data_seek(0); foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>">ID: <?= htmlspecialchars($t['email']) ?> - <?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" name="assign_class">Assign</button>
        </form>
    </div>
    <div class="section">
        <h2>Assign Teacher to Subject for Class</h2>
        <form method="post">
            <label>Class:
                <select name="class_id_sub" required>
                    <option value="">Select Class</option>
                    <?php $classes->data_seek(0); while ($row = $classes->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
            <label>Subject:
                <select name="subject_id_sub" required>
                    <option value="">Select Subject</option>
                    <?php $subjects->data_seek(0); while ($sub = $subjects->fetch_assoc()): ?>
                    <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
            <label>Teacher:
                <select name="teacher_id_sub" required>
                    <option value="">Select Teacher</option>
                    <?php $teachers->data_seek(0); foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>">ID: <?= htmlspecialchars($t['email']) ?> - <?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" name="assign_subject_teacher">Assign</button>
        </form>
    </div>
    <div class="section">
        <h2>All Teachers</h2>
        <table>
            <thead>
                <tr><th>Name</th><th>Email</th><th>Teacher ID</th><th>Phone</th><th>Qualification</th><th>Main Subject</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php $teachers->data_seek(0); foreach ($teachers as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td><?= htmlspecialchars($t['email']) ?></td>
                    <td><?= htmlspecialchars($t['teacher_id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($t['phone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($t['qualification'] ?? '') ?></td>
                    <td><?= htmlspecialchars($t['subject']) ?></td>
                    <td>
                        <a href="view_teacher.php?id=<?= $t['id'] ?>" style="display:inline-block;"><button type="button">View</button></a>
                        <a href="edit_teacher.php?id=<?= $t['id'] ?>" style="display:inline-block;"><button type="button">Edit</button></a>
                        <a href="remove_teacher.php?id=<?= $t['id'] ?>" style="display:inline-block;"><button type="button">Remove</button></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Edit teacher form moved to edit_teacher.php -->
    </div>
    <div class="section">
        <h2>Unassign Teacher</h2>
        <form method="post">
            <label>Unassign from Class:
                <select name="class_id_unassign">
                    <option value="">Select Class</option>
                    <?php $classes->data_seek(0); while ($row = $classes->fetch_assoc()): ?>
                        <?php if ($row['teacher_id']): ?>
                        <option value="<?= $row['id'] ?>">Class: <?= htmlspecialchars($row['class_name']) ?> (Teacher ID: <?= htmlspecialchars($row['teacher_id']) ?>)</option>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </select>
            </label>
            <button type="submit" name="unassign_class">Unassign</button>
        </form>
        <form method="post">
            <label>Unassign from Subject/Class:
                <select name="class_id_unassign_sub">
                    <option value="">Select Class</option>
                    <?php $class_subjects = $conn->query('SELECT * FROM class_subjects');
                    $subjects->data_seek(0);
                    $classes->data_seek(0);
                    while ($cs = $class_subjects->fetch_assoc()): ?>
                        <?php if ($cs['teacher_id']): ?>
                        <option value="<?= $cs['class_id'] ?>|<?= $cs['subject_id'] ?>">Class: <?= htmlspecialchars($cs['class_id']) ?>, Subject: <?= htmlspecialchars($cs['subject_id']) ?> (Teacher ID: <?= htmlspecialchars($cs['teacher_id']) ?>)</option>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </select>
            </label>
            <input type="hidden" name="subject_id_unassign_sub" id="subject_id_unassign_sub">
            <button type="submit" name="unassign_subject_teacher" onclick="
                var sel = this.form.class_id_unassign_sub;
                if(sel && sel.value) {
                    var parts = sel.value.split('|');
                    this.form.class_id_unassign_sub.value = parts[0];
                    document.getElementById('subject_id_unassign_sub').value = parts[1];
                }
            ">Unassign</button>
        </form>
    </div>
    <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</div>
</body>
</html>
