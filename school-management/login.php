	// Admission manager login
	$stmt = $conn->prepare('SELECT * FROM staff WHERE email = ? AND role = "admission"');
	$stmt->bind_param('s', $id);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($row = $result->fetch_assoc()) {
		if (password_verify($password, $row['password'])) {
			$_SESSION['user'] = $row['email'];
			$_SESSION['user_type'] = 'staff';
			$_SESSION['user_name'] = $row['email'];
			header('Location: staff_dashboard.php');
			exit();
		}
	}
	$stmt->close();
