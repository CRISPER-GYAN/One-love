
	<?php
	session_start();
	require 'db.php';
	$error = '';
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$id = trim($_POST['id'] ?? '');
		$password = $_POST['password'] ?? '';
		$role = $_POST['role'] ?? '';
		$table = '';
		$id_field = '';
		switch ($role) {
			case 'admin':
				$table = 'admins';
				$id_field = 'username';
				break;
			case 'teacher':
				$table = 'teachers';
				$id_field = 'email';
				break;
			case 'headmaster':
			case 'headmistress':
				$table = 'staff';
				$id_field = 'email';
				break;
			case 'staff':
				$table = 'staff';
				$id_field = 'email';
				break;
			case 'student':
				$table = 'students';
				$id_field = 'email';
				break;
			case 'parent':
				$table = 'parents';
				$id_field = 'email';
				break;
			default:
				$error = 'Invalid role selected.';
		}
		if ($table && $id_field) {
			$stmt = $conn->prepare("SELECT * FROM $table WHERE $id_field = ?");
			$stmt->bind_param('s', $id);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($row = $result->fetch_assoc()) {
				if (password_verify($password, $row['password'])) {
					$_SESSION['user'] = $row['id'];
					$_SESSION['user_type'] = $role;
					$_SESSION['name'] = $row['name'] ?? $row['username'] ?? '';
					// Redirect based on role
					switch ($role) {
						case 'admin':
							header('Location: admin_dashboard.php'); exit;
						case 'teacher':
							header('Location: teacher_dashboard.php'); exit;
						case 'headmaster':
						case 'headmistress':
							header('Location: headmaster_dashboard.php'); exit;
						case 'staff':
							header('Location: staff_dashboard.php'); exit;
						case 'student':
							header('Location: student_portal.php'); exit;
						case 'parent':
							header('Location: parent_portal.php'); exit;
					}
				} else {
					$error = 'Invalid password.';
				}
			} else {
				$error = 'User not found.';
			}
			$stmt->close();
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
		<form method="post">
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
			<input type="text" name="id" id="id" required>
			<label for="password">Password:</label>
			<input type="password" name="password" id="password" required>
			<button type="submit">Login</button>
		</form>
	</div>
	</body>
	</html>
