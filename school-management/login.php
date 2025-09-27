

<?php
// Start session before any output
session_start();
require 'db.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

// Handle admin registration
if (isset($_POST['register_admin'])) {
	if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
		$error = 'Invalid session token.';
	} else {
		$admin_email = trim($_POST['admin_email'] ?? '');
		$admin_id = trim($_POST['admin_id'] ?? '');
		$admin_password = $_POST['admin_password'] ?? '';
		if (!$admin_email || !$admin_id || !$admin_password) {
			$error = 'All fields are required for admin registration.';
		} elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
			$error = 'Invalid email format.';
		} else {
			// Check if admin already exists
			$stmt = $conn->prepare('SELECT id FROM admins WHERE email = ? OR admin_id = ?');
			$stmt->bind_param('ss', $admin_email, $admin_id);
			$stmt->execute();
			$stmt->store_result();
			if ($stmt->num_rows > 0) {
				$error = 'Admin with this email or ID already exists.';
			} else {
				$hash = password_hash($admin_password, PASSWORD_DEFAULT);
				$stmt = $conn->prepare('INSERT INTO admins (email, admin_id, password) VALUES (?, ?, ?)');
				$stmt->bind_param('sss', $admin_email, $admin_id, $hash);
				if ($stmt->execute()) {
					$success = 'Admin account created successfully. You can now log in.';
				} else {
					$error = 'Failed to create admin account.';
				}
			}
			$stmt->close();
		}
	}
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['register_admin'])) {
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
				$id_fields = ['email'];
				break;
			case 'headmaster':
			case 'headmistress':
				$table = 'staff';
				$id_fields = ['email'];
				break;
			case 'staff':
				$table = 'staff';
				$id_fields = ['email'];
				break;
			case 'student':
				$table = 'students';
				$id_fields = ['email'];
				break;
			case 'parent':
				$table = 'parents';
				$id_fields = ['email'];
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
					if (
						($role === 'admin' && password_verify($password, $row['password'])) ||
						($role !== 'admin' && password_verify($password, $row['password']))
					) {
						$_SESSION['user'] = $row['id'];
						$_SESSION['user_type'] = $role;
						$_SESSION['name'] = $row['name'] ?? $row['username'] ?? '';
						if (isset($redirects[$role])) {
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
		<?php
		// Check if admin registration is visible
		$show_admin_registration = true;
		$settings_result = $conn->query("SELECT admin_registration_visible FROM settings LIMIT 1");
		if ($settings_result && $settings_row = $settings_result->fetch_assoc()) {
			$show_admin_registration = (int)$settings_row['admin_registration_visible'] === 1;
		}
		?>
		<?php if ($show_admin_registration): ?>
		<hr style="margin:2em 0;">
		<h3>Create Admin Account</h3>
		<form method="post" autocomplete="off">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
			<input type="hidden" name="register_admin" value="1">
			<label for="admin_email">Email:</label>
			<input type="email" name="admin_email" id="admin_email" required>
			<label for="admin_id">Admin ID:</label>
			<input type="text" name="admin_id" id="admin_id" required pattern="[A-Za-z0-9]+">
			<label for="admin_password">Password:</label>
			<input type="password" name="admin_password" id="admin_password" required>
			<button type="submit">Create Admin</button>
		</form>
		<?php endif; ?>
	</div>
	</body>
	</html>
