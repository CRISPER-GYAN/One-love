<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
$msg = '';
$success = '';
// Handle admin registration toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_admin_registration'])) {
    $visible = isset($_POST['admin_registration_visible']) ? 1 : 0;
    $conn->query("UPDATE settings SET admin_registration_visible = $visible LIMIT 1");
    $msg = 'Setting updated.';
}
$result = $conn->query("SELECT admin_registration_visible FROM settings LIMIT 1");
$visible = 1;
if ($row = $result->fetch_assoc()) {
    $visible = (int)$row['admin_registration_visible'];
}
// Fetch current settings
$settings_result = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settings_result ? $settings_result->fetch_assoc() : null;
// Handle school settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_school_settings'])) {
    $school_name = trim($_POST['school_name']);
    $grade_point = intval($_POST['promotion_grade_point']);
    $headmaster_name = trim($_POST['headmaster_name']);
    $logo_path = ($settings && isset($settings['school_logo'])) ? $settings['school_logo'] : '';
    // Check if 'school_logo' column exists
    $columnsRes = $conn->query("SHOW COLUMNS FROM settings LIKE 'school_logo'");
    $has_logo_col = $columnsRes && $columnsRes->num_rows > 0;
    if (isset($_FILES['school_logo']) && $_FILES['school_logo']['error'] === UPLOAD_ERR_OK && $has_logo_col) {
        $ext = strtolower(pathinfo($_FILES['school_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
            $logo_path = 'uploads/school_logo_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['school_logo']['tmp_name'], $logo_path);
        }
    }
    if ($has_logo_col) {
        $stmt = $conn->prepare('UPDATE settings SET school_name=?, school_logo=?, promotion_grade_point=?, headmaster_name=? WHERE id=1');
        $stmt->bind_param('ssis', $school_name, $logo_path, $grade_point, $headmaster_name);
    } else {
        $stmt = $conn->prepare('UPDATE settings SET school_name=?, promotion_grade_point=?, headmaster_name=? WHERE id=1');
        $stmt->bind_param('sis', $school_name, $grade_point, $headmaster_name);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: admin_dashboard.php?msg=School+settings+updated');
    exit();
}
// Handle grading system settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grading_settings'])) {
    $grades = [];
    if (isset($_POST['grade']) && is_array($_POST['grade'])) {
        foreach ($_POST['grade'] as $i => $g) {
            $min = intval($_POST['min'][$i]);
            $max = intval($_POST['max'][$i]);
            $remark = trim($_POST['remark'][$i]);
            $grade = trim($g);
            if ($grade !== '' && $remark !== '' && $min <= $max) {
                $grades[] = ["grade"=>$grade, "min"=>$min, "max"=>$max, "remark"=>$remark];
            }
        }
    }
    $grades_json = json_encode($grades);
    $stmt = $conn->prepare('UPDATE settings SET grading_system=? WHERE id=1');
    $stmt->bind_param('s', $grades_json);
    $stmt->execute();
    $stmt->close();
    $settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();
    $success = 'Grading system updated.';
}
// Handle color/layout settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_layout_settings'])) {
    $primary_color = $_POST['primary_color'];
    $secondary_color = $_POST['secondary_color'];
    $background_color = $_POST['background_color'];
    $button_color = $_POST['button_color'];
    $button_text_color = $_POST['button_text_color'];
    $font_family = $_POST['font_family'];
    $stmt = $conn->prepare('UPDATE settings SET primary_color=?, secondary_color=?, background_color=?, button_color=?, button_text_color=?, font_family=? WHERE id=1');
    $stmt->bind_param('ssssss', $primary_color, $secondary_color, $background_color, $button_color, $button_text_color, $font_family);
    $stmt->execute();
    $stmt->close();
    $settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();
    $success = 'Layout and color settings updated.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6fb; }
        .settings-container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px #eaf0fa; padding: 2.5em 2.5em 2em 2.5em; }
        h2, h3 { color: #2a4d8f; margin-bottom: 18px; }
        .settings-section { background: #eaf0fa; border-radius: 10px; padding: 2em 2em 1.5em 2em; margin-bottom: 2em; box-shadow: 0 1px 6px #eaf0fa; }
        label { display: block; margin: 1.2em 0 0.5em; font-weight: 500; }
        input[type="text"], input[type="number"], input[type="file"], input[type="color"] { width: 100%; padding: 0.7em; border-radius: 6px; border: 1px solid #d1d5db; background: #f9fafb; font-size: 1rem; }
        input[type="checkbox"] { margin-right: 8px; }
        button { background: #2a4d8f; color: #fff; border: none; border-radius: 6px; padding: 10px 28px; font-size: 1rem; cursor: pointer; margin-top: 1.5em; }
        button:hover { background: #18305c; }
        .success, .msg { color: #1a7f37; background: #e6f4ea; padding: 10px; border-radius: 6px; margin-bottom: 16px; }
        .logo-preview { max-height: 60px; display: block; margin-bottom: 8px; }
        .settings-flex { display: flex; gap: 2em; flex-wrap: wrap; }
        .settings-flex > div { flex: 1 1 320px; }
    </style>
</head>
<body>
<header>
    <h1 style="color:#2a4d8f; text-align:center; margin-bottom:12px;">Admin Settings Dashboard</h1>
    <h2 style="color:#2a4d8f; text-align:center; margin-bottom:32px; font-weight:400;">Manage all school, grading, and system settings</h2>
</header>
<div class="settings-container">
    <?php if ($msg): ?><div class="msg"> <?= htmlspecialchars($msg) ?> </div><?php endif; ?>
    <?php if ($success): ?><div class="success"> <?= htmlspecialchars($success) ?> </div><?php endif; ?>
    <div class="settings-section">
        <h2>School Grading System</h2>
        <form method="post">
            <table style="width:100%; background:#fff; border-radius:8px; box-shadow:0 1px 6px #eaf0fa; margin-bottom:1em;">
                <thead>
                    <tr style="background:#eaf0fa; color:#2a4d8f;">
                        <th>Grade</th>
                        <th>Min (%)</th>
                        <th>Max (%)</th>
                        <th>Remark</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="grading-rows">
                <?php
                $grading = [];
                if (!empty($settings['grading_system'])) {
                    $grading = json_decode($settings['grading_system'], true);
                }
                if (!$grading) {
                    $grading = [
                        ["grade"=>"A+","min"=>80,"max"=>100,"remark"=>"Excellent"],
                        ["grade"=>"A","min"=>70,"max"=>79,"remark"=>"Very Good"],
                        ["grade"=>"B","min"=>60,"max"=>69,"remark"=>"Good"],
                        ["grade"=>"C","min"=>50,"max"=>59,"remark"=>"Credit"],
                        ["grade"=>"D","min"=>40,"max"=>49,"remark"=>"Pass"],
                        ["grade"=>"F","min"=>0,"max"=>39,"remark"=>"Fail"]
                    ];
                }
                foreach ($grading as $i => $g): ?>
                    <tr>
                        <td><input type="text" name="grade[]" value="<?= htmlspecialchars($g['grade']) ?>" required style="width:60px;"></td>
                        <td><input type="number" name="min[]" value="<?= htmlspecialchars($g['min']) ?>" min="0" max="100" required style="width:70px;"></td>
                        <td><input type="number" name="max[]" value="<?= htmlspecialchars($g['max']) ?>" min="0" max="100" required style="width:70px;"></td>
                        <td><input type="text" name="remark[]" value="<?= htmlspecialchars($g['remark']) ?>" required></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
                <!-- Empty row for adding new grade -->
                <tr class="grading-row">
                    <td><input type="text" name="grade[]" value="" style="width:60px;"></td>
                    <td><input type="number" name="min[]" value="" min="0" max="100" style="width:70px;"></td>
                    <td><input type="number" name="max[]" value="" min="0" max="100" style="width:70px;"></td>
                    <td><input type="text" name="remark[]" value=""></td>
                    <td><button type="button" class="remove-row" style="background:#c00;color:#fff;padding:2px 10px;border-radius:4px;">&times;</button></td>
                </tr>
                </tbody>
            </table>
            <button type="button" id="add-grade-row" style="background:#2a4d8f;color:#fff;padding:6px 18px;border-radius:6px;margin-bottom:1em;">Add Grade Row</button>
</form>
<script>
document.getElementById('add-grade-row').onclick = function() {
    var tbody = document.getElementById('grading-rows');
    var newRow = document.createElement('tr');
    newRow.className = 'grading-row';
    newRow.innerHTML = `
        <td><input type="text" name="grade[]" value="" style="width:60px;"></td>
        <td><input type="number" name="min[]" value="" min="0" max="100" style="width:70px;"></td>
        <td><input type="number" name="max[]" value="" min="0" max="100" style="width:70px;"></td>
        <td><input type="text" name="remark[]" value=""></td>
        <td><button type="button" class="remove-row" style="background:#c00;color:#fff;padding:2px 10px;border-radius:4px;">&times;</button></td>
    `;
    tbody.appendChild(newRow);
};
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('remove-row')) {
        var row = e.target.closest('tr');
        if (row) row.remove();
    }
});
</script>
                </tbody>
            </table>
            <button type="submit" name="save_grading_settings">Save Grading System</button>
        </form>
    </div>
        <h2>Account & Security</h2>
        <form method="post">
            <label>
                <input type="checkbox" name="admin_registration_visible" value="1" <?= $visible ? 'checked' : '' ?>>
                Allow admin account creation on login page
            </label>
            <button type="submit" name="toggle_admin_registration">Save</button>
        </form>
    </div>
    <div class="settings-section">
        <h2>School Settings</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="settings-flex">
                <div>
                    <label>School Name:
                        <input type="text" name="school_name" value="<?= htmlspecialchars($settings['school_name'] ?? '') ?>" required>
                    </label>
                    <label>Promotion Grade Point:
                        <input type="number" name="promotion_grade_point" value="<?= htmlspecialchars($settings['promotion_grade_point'] ?? 200) ?>" min="0" max="1000" required>
                    </label>
                    <label>Headmaster/Headmistress Name:
                        <input type="text" name="headmaster_name" value="<?= htmlspecialchars($settings['headmaster_name'] ?? '') ?>">
                    </label>
                </div>
                <div>
                    <label>School Logo:
                        <?php if (!empty($settings['school_logo'])): ?>
                            <img src="<?= htmlspecialchars($settings['school_logo']) ?>" alt="School Logo" class="logo-preview">
                        <?php endif; ?>
                        <input type="file" name="school_logo" accept="image/*">
                    </label>
                </div>
            </div>
            <button type="submit" name="save_school_settings">Save School Settings</button>
        </form>
    </div>
    <div class="settings-section">
        <h2>Customize Colors & Layout</h2>
        <form method="post">
            <div class="settings-flex">
                <div>
                    <label>Primary Color: <input type="color" name="primary_color" value="<?= htmlspecialchars($settings['primary_color']) ?>"></label>
                    <label>Secondary Color: <input type="color" name="secondary_color" value="<?= htmlspecialchars($settings['secondary_color']) ?>"></label>
                    <label>Background Color: <input type="color" name="background_color" value="<?= htmlspecialchars($settings['background_color']) ?>"></label>
                </div>
                <div>
                    <label>Button Color: <input type="color" name="button_color" value="<?= htmlspecialchars($settings['button_color']) ?>"></label>
                    <label>Button Text Color: <input type="color" name="button_text_color" value="<?= htmlspecialchars($settings['button_text_color']) ?>"></label>
                    <label>Font Family: <input type="text" name="font_family" value="<?= htmlspecialchars($settings['font_family']) ?>"></label>
                </div>
            </div>
            <button type="submit" name="save_layout_settings">Save Layout Settings</button>
        </form>
    </div>
</div>
<footer style="background:#eaf0fa; color:#2a4d8f; text-align:center; padding:18px 0; margin-top:40px; border-radius: 0 0 12px 12px; font-size:1.1em;">
    &copy; <?= date('Y') ?> School Management System
</footer>
</body>
</html>