<?php
// Start session and include DB
session_start();
require 'db.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid session token.';
    } else {
        // Validate and sanitize input
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $age = intval($_POST['age'] ?? 0);
        $gender = in_array($_POST['gender'] ?? '', ['Male','Female','Other']) ? $_POST['gender'] : '';
        if (!$name || !$email || !$age || !$gender) {
            $error = 'All fields are required and must be valid.';
        } else {
            // Insert using prepared statement
            $stmt = $conn->prepare('INSERT INTO admission_applications (name, email, age, gender) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssis', $name, $email, $age, $gender);
            if ($stmt->execute()) {
                $success = 'Application submitted successfully!';
            } else {
                $error = 'Database error. Please try again.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Form</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(120deg, #eaf0fa 0%, #f4f6fb 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
        }
        .form-container {
            max-width: 420px;
            margin: 60px auto;
            background: #fff;
            padding: 2.5em 2em 2em 2em;
            border-radius: 16px;
            box-shadow: 0 6px 32px 0 rgba(42,77,143,0.10), 0 1.5px 6px 0 rgba(42,77,143,0.08);
        }
        h2 {
            color: #2a4d8f;
            text-align: center;
            margin-bottom: 1.5em;
            font-weight: 600;
            letter-spacing: 1px;
        }
        label {
            display: block;
            margin-top: 1.2em;
            font-weight: 500;
            color: #2a4d8f;
        }
        input, select {
            width: 100%;
            padding: 0.7em 0.9em;
            margin-top: 0.4em;
            border: 1px solid #bfcbe2;
            border-radius: 6px;
            font-size: 1em;
            background: #f8fafd;
            transition: border 0.2s;
        }
        input:focus, select:focus {
            border: 1.5px solid #2a4d8f;
            outline: none;
            background: #fff;
        }
        button {
            background: linear-gradient(90deg, #2a4d8f 60%, #4e7be6 100%);
            color: #fff;
            border: none;
            padding: 0.9em 2em;
            border-radius: 6px;
            margin-top: 2em;
            font-size: 1.1em;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            box-shadow: 0 2px 8px 0 rgba(42,77,143,0.08);
            transition: background 0.2s;
        }
        button:hover {
            background: linear-gradient(90deg, #1d3266 60%, #2a4d8f 100%);
        }
        .error {
            color: #c00;
            margin-top: 1em;
            text-align: center;
            font-weight: 500;
        }
        .success {
            color: #080;
            margin-top: 1em;
            text-align: center;
            font-weight: 500;
        }
        @media (max-width: 600px) {
            .form-container {
                padding: 1.2em 0.5em 1em 0.5em;
            }
        }
    </style>
</head>
<body>
<div class="form-container">
    <img src="assets/logo.png" alt="School Logo" style="display:block;margin:0 auto 1.5em auto;max-width:90px;">
    <h2>Admission Application</h2>
    <div style="text-align:center;color:#888;font-size:1em;margin-bottom:1.5em;">Please fill in all required fields. Fields marked with * are mandatory.</div>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label for="name">Full Name*:
            <span title="Enter your full legal name." style="color:#888;cursor:help;">&#9432;</span>
        </label>
        <input type="text" name="name" id="name" required placeholder="e.g. John Doe">

        <label for="email">Email*:
            <span title="We'll send a confirmation to this address." style="color:#888;cursor:help;">&#9432;</span>
        </label>
        <input type="email" name="email" id="email" required placeholder="e.g. john@email.com">

        <label for="age">Age*:</label>
        <input type="number" name="age" id="age" min="1" max="120" required placeholder="e.g. 12">

        <label for="gender">Gender*:</label>
        <select name="gender" id="gender" required>
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>

        <label for="parent_contact">Parent/Guardian Contact*:
            <span title="Parent or guardian's phone number or email." style="color:#888;cursor:help;">&#9432;</span>
        </label>
        <input type="text" name="parent_contact" id="parent_contact" required placeholder="e.g. 08012345678 or parent@email.com">

        <label for="desired_class">Desired Class/Grade*:</label>
        <select name="desired_class" id="desired_class" required>
            <option value="">Select</option>
            <option value="Primary 1">Primary 1</option>
            <option value="Primary 2">Primary 2</option>
            <option value="Primary 3">Primary 3</option>
            <option value="Primary 4">Primary 4</option>
            <option value="Primary 5">Primary 5</option>
            <option value="Primary 6">Primary 6</option>
            <option value="JSS 1">JSS 1</option>
            <option value="JSS 2">JSS 2</option>
            <option value="JSS 3">JSS 3</option>
            <option value="SS 1">SS 1</option>
            <option value="SS 2">SS 2</option>
            <option value="SS 3">SS 3</option>
        </select>

        <label for="photo">Profile Photo:
            <span title="Upload a recent passport photo (JPG/PNG, max 2MB)." style="color:#888;cursor:help;">&#9432;</span>
        </label>
        <input type="file" name="photo" id="photo" accept="image/png, image/jpeg">

        <button type="submit">Apply</button>
    </form>
    <div style="margin-top:2em;text-align:center;">
        <a href="application_status.php" style="color:#2a4d8f;text-decoration:underline;font-size:0.98em;">Check Application Status</a>
        <br><br>
        <a href="admission_form_pdf.php" style="color:#2a4d8f;text-decoration:underline;font-size:0.98em;">Download Blank Application (PDF)</a>
    </div>
</div>
</body>
</html>
