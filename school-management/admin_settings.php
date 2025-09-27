<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
$msg = '';
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
?>
<h2>Admin Settings</h2>
<?php if ($msg): ?><div style="color:green;"> <?= htmlspecialchars($msg) ?> </div><?php endif; ?>
<form method="post">
    <label>
        <input type="checkbox" name="admin_registration_visible" value="1" <?= $visible ? 'checked' : '' ?>>
        Allow admin account creation on login page
    </label>
    <button type="submit" name="toggle_admin_registration">Save</button>
</form>
<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
// Fetch current settings
$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <title>Admin Settings - Customize Layout</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Admin Settings</h1>
</header>
<div class="container">
    <h2>Customize Colors & Layout</h2>
    <?php if (!empty($success)) echo '<div class="success">'.$success.'</div>'; ?>
    <form method="post">
        <label>Primary Color: <input type="color" name="primary_color" value="<?= htmlspecialchars($settings['primary_color']) ?>"></label>
        <label>Secondary Color: <input type="color" name="secondary_color" value="<?= htmlspecialchars($settings['secondary_color']) ?>"></label>
        <label>Background Color: <input type="color" name="background_color" value="<?= htmlspecialchars($settings['background_color']) ?>"></label>
        <label>Button Color: <input type="color" name="button_color" value="<?= htmlspecialchars($settings['button_color']) ?>"></label>
        <label>Button Text Color: <input type="color" name="button_text_color" value="<?= htmlspecialchars($settings['button_text_color']) ?>"></label>
        <label>Font Family: <input type="text" name="font_family" value="<?= htmlspecialchars($settings['font_family']) ?>"></label>
        <button type="submit">Save Settings</button>
    </form>
</div>
<footer>
    &copy; <?= date('Y') ?> School Management System
</footer>
</body>
</html>