<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // headmaster or headmistress
    $stmt = $conn->prepare('INSERT INTO staff (name, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $password, $role);
    $stmt->execute();
    $stmt->close();
    $success = 'Headmaster/Headmistress created.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Headmaster/Headmistress</title>
</head>
<body>
    <h2>Create Headmaster/Headmistress</h2>
    <?php if (!empty($success)) echo '<p style="color:green;">'.$success.'</p>'; ?>
    <form method="post">
        <label>Name: <input type="text" name="name" required></label><br>
        <label>Email: <input type="email" name="email" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <label>Role:
            <select name="role" required>
                <option value="headmaster">Headmaster</option>
                <option value="headmistress">Headmistress</option>
            </select>
        </label><br>
        <button type="submit">Create</button>
    </form>
    <p><a href="admin_settings.php">Back to Admin Settings</a></p>
</body>
</html>
