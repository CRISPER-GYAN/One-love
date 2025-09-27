

<?php
// Start session before any output
session_start();
require 'db.php';
require_once 'auth.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

$show_admin_registration = false;
// ...existing code...
$show_admin_registration = false;
// Check if admin registration is enabled in settings or if no admin exists
$settings = $conn->query("SELECT admin_registration_visible FROM settings LIMIT 1");
if ($settings && ($row = $settings->fetch_assoc())) {
	$show_admin_registration = (bool)$row['admin_registration_visible'];
}
$admin_count = $conn->query("SELECT COUNT(*) as cnt FROM admins");
if ($admin_count && ($row = $admin_count->fetch_assoc())) {
	if ((int)$row['cnt'] === 0) $show_admin_registration = true;
}

if (isset($_POST['register_admin'])) {
	$admin_id = trim($_POST['admin_id'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$name = trim($_POST['name'] ?? '');
	$password = $_POST['password'] ?? '';
	$confirm = $_POST['confirm_password'] ?? '';
	if (!$admin_id || !$email || !$name || !$password || !$confirm) {
		$reg_error = 'All fields are required.';
	} elseif ($password !== $confirm) {
		$reg_error = 'Passwords do not match.';
	} else {
	$stmt = $conn->prepare("INSERT INTO admins (admin_id, email, name, password) VALUES (?, ?, ?, ?)");
	$hash = create_hashed_password($password);
	$stmt->bind_param('ssss', $admin_id, $email, $name, $hash);
		if ($stmt->execute()) {
			$reg_success = 'Admin account created. You can now log in.';
		} else {
			$reg_error = 'Error creating admin: ' . htmlspecialchars($stmt->error);
		}
	}
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// CSRF protection
	if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
		$error = 'Invalid session token.';
	} else {
		// Sanitize and validate user input
		$id = trim($_POST['id'] ?? '');
		$password = $_POST['password'] ?? '';
		$role = $_POST['role'] ?? '';
		$table = '';
		$id_fields = [];
		$redirects = [
			'admin' => 'admin_dashboard.php',
			'teacher' => 'teacher_dashboard.php',
			'headmaster' => 'headmaster_dashboard.php',
			'headmistress' => 'headmaster_dashboard.php',
			'staff' => 'staff_dashboard.php',
			'student' => 'student_portal.php',
			'parent' => 'parent_portal.php',
		];
		switch ($role) {
			case 'admin':
				$table = 'admins';
				$id_fields = ['email', 'admin_id']; // allow login by email or admin_id
				break;
			case 'teacher':
				$table = 'teachers';
				$id_fields = ['email', 'teacher_id']; // allow login by email or teacher_id
				break;
			case 'headmaster':
			case 'headmistress':
				$table = 'staff';
				$id_fields = ['email', 'staff_id']; // allow login by email or staff_id
				break;
			case 'staff':
				$table = 'staff';
				$id_fields = ['email', 'staff_id']; // allow login by email or staff_id
				break;
			case 'student':
				$table = 'students';
				$id_fields = ['email', 'student_id']; // allow login by email or student_id
				break;
			case 'parent':
				$table = 'parents';
				$id_fields = ['email', 'parent_id']; // allow login by email or parent_id
				break;
			default:
				$error = 'Invalid role selected.';
		}
		if ($table && $id_fields && !$error) {
			// Build query for multiple possible id fields (for admin)
			$where = implode(' = ? OR ', $id_fields) . ' = ?';
			$query = "SELECT * FROM $table WHERE $where LIMIT 1";
			$stmt = $conn->prepare($query);
			if ($stmt) {
				$params = array_fill(0, count($id_fields), $id);
				$types = str_repeat('s', count($id_fields));
				$stmt->bind_param($types, ...$params);
				$stmt->execute();
				$result = $stmt->get_result();
				if ($row = $result->fetch_assoc()) {
					$verify = verify_password($password, $row['password']);
					if ($verify === true) {
					// Upgrade legacy hash if needed
					if ($verify === 'upgrade') {
						$newHash = create_hashed_password($password);
						$update = $conn->prepare("UPDATE $table SET password=? WHERE id=?");
						$update->bind_param('si', $newHash, $row['id']);
						$update->execute();
						$row['password'] = $newHash;
					}
						$_SESSION['user'] = $row['id'];
						$_SESSION['user_type'] = $role;
						$_SESSION['name'] = $row['name'] ?? $row['username'] ?? '';
						if (isset($redirects[$role])) {
							// Always redirect to the correct dashboard/portal for the role
							header('Location: ' . $redirects[$role]);
							exit();
						} else {
							$error = 'No dashboard found for this role.';
						}
					} else {
						$error = 'Invalid password.';
					}
				} else {
					$error = 'User not found.';
				}
				$stmt->close();
			} else {
				$error = 'Database error.';
			}
		}
	}
}
?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Login</title>
		<link rel="stylesheet" href="style.css">
		<style>
			body { background: #f4f6fb; font-family: Segoe UI, Arial, sans-serif; }
			.login-container { max-width: 400px; margin: 40px auto; background: #fff; padding: 2em; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
			h2 { color: #2a4d8f; }
			label { display: block; margin-top: 1em; }
			input, select { width: 100%; padding: 0.5em; margin-top: 0.5em; }
			button { background: #2a4d8f; color: #fff; border: none; padding: 0.7em 2em; border-radius: 4px; margin-top: 1em; cursor: pointer; }
			.error { color: #c00; margin-top: 1em; }
		</style>
	</head>
	<body>
	<div class="login-container">
		<h2>Login</h2>
		<?php if ($error): ?>
			<div class="error"><?= htmlspecialchars($error) ?></div>
		<?php endif; ?>
		<?php if ($success): ?>
			<div class="success" style="color:green; margin-top:1em;"> <?= htmlspecialchars($success) ?> </div>
		<?php endif; ?>
		<form method="post" autocomplete="off">
			<!-- CSRF token for security -->
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
			<label for="role">Role:</label>
			<select name="role" id="role" required>
				<option value="">Select Role</option>
				<option value="admin">Admin</option>
				<option value="teacher">Teacher</option>
				<option value="headmaster">Headmaster</option>
				<option value="headmistress">Headmistress</option>
				<option value="staff">Staff</option>
				<option value="student">Student</option>
				<option value="parent">Parent</option>
			</select>
			<label for="id">Username/Email/ID:</label>
			<input type="text" name="id" id="id" required pattern="[A-Za-z0-9@.]+">
			<label for="password">Password:</label>
			<input type="password" name="password" id="password" required>
			<button type="submit">Login</button>
		</form>
		<?php if ($show_admin_registration): ?>
		<div class="admin-register-box" style="margin-top:2em; background:#eaf0fa; padding:1.5em; border-radius:8px;">
			<h2>Admin Registration</h2>
			<?php if (!empty($reg_error)): ?><div class="error"><?= htmlspecialchars($reg_error) ?></div><?php endif; ?>
			<?php if (!empty($reg_success)): ?><div class="success" style="color:green; margin-top:1em;"> <?= htmlspecialchars($reg_success) ?> </div><?php endif; ?>
			<form method="post" autocomplete="off">
				<input type="hidden" name="register_admin" value="1">
				<label for="admin_id">Admin ID:</label>
				<input type="text" name="admin_id" id="admin_id" required><br>
				<label for="email">Email:</label>
				<input type="email" name="email" id="email" required><br>
				<label for="name">Name:</label>
				<input type="text" name="name" id="name" required><br>
				<label for="password">Password:</label>
				<input type="password" name="password" id="password" required><br>
				<label for="confirm_password">Confirm Password:</label>
				<input type="password" name="confirm_password" id="confirm_password" required><br>
				<button type="submit">Register Admin</button>
			</form>
		</div>
		<?php endif; ?>
	</div>
	</body>
	</html>
