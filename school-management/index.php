<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome | School Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(120deg, #eaf0fa 0%, #f4f6fb 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
        }
        .hero {
            max-width: 600px;
            margin: 60px auto 0 auto;
            background: #fff;
            padding: 2.5em 2em 2em 2em;
            border-radius: 16px;
            box-shadow: 0 6px 32px 0 rgba(42,77,143,0.10), 0 1.5px 6px 0 rgba(42,77,143,0.08);
            text-align: center;
        }
        .hero img {
            max-width: 100px;
            margin-bottom: 1.2em;
        }
        h1 {
            color: #2a4d8f;
            font-size: 2.2em;
            margin-bottom: 0.3em;
            font-weight: 700;
        }
        .subtitle {
            color: #4e7be6;
            font-size: 1.2em;
            margin-bottom: 1.5em;
        }
        .nav {
            margin: 2em 0 1.5em 0;
        }
        .nav a {
            display: inline-block;
            margin: 0 1em;
            padding: 0.7em 2em;
            background: linear-gradient(90deg, #2a4d8f 60%, #4e7be6 100%);
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1em;
            box-shadow: 0 2px 8px 0 rgba(42,77,143,0.08);
            transition: background 0.2s;
        }
        .nav a:hover {
            background: linear-gradient(90deg, #1d3266 60%, #2a4d8f 100%);
        }
        .features {
            margin-top: 2.5em;
            text-align: left;
        }
        .features h3 {
            color: #2a4d8f;
            margin-bottom: 0.5em;
        }
        .features ul {
            color: #333;
            font-size: 1.05em;
            padding-left: 1.2em;
        }
        @media (max-width: 700px) {
            .hero { padding: 1.2em 0.5em 1em 0.5em; }
            .nav a { margin: 0.5em 0.2em; padding: 0.7em 1em; font-size: 1em; }
        }
    </style>
</head>
<body>
    <div class="hero">
        <img src="assets/logo.png" alt="School Logo">
        <h1>Welcome To Crisdital Web Inte. School</h1>
        <div class="subtitle">Empowering students, teachers, and parents for a brighter future.</div>
        <div class="nav">
            <a href="login.php">Login</a>
            <a href="admission_form.php">Apply for Admission</a>
            <a href="application_status.php">Check Application Status</a>
            <a href="student_portal.php">Student Portal</a>
            <a href="parent_portal.php">Parent Portal</a>
            <a href="staff_dashboard.php">Staff Portal</a>
            <a href="teacher_dashboard.php">Teacher Portal</a>
            <a href="admin_dashboard.php">Admin Login</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
        </div>
        <div class="features">
            <h3>Features:</h3>
            <ul>
                <li>Online admission and application tracking</li>
                <li>Student, teacher, and staff portals</li>
                <li>Attendance and results management</li>
                <li>Modern, secure, and mobile-friendly design</li>
            </ul>
        </div>
    </div>
</body>
</html>
