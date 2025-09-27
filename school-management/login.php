

<?php
// Start session before any output
session_start();
require 'db.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
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
						($role === 'admin' && $row['admin_id'] === 'ADM142002' && password_verify($password, $row['password'])) ||
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
	</div>
	</body>
	</html>
