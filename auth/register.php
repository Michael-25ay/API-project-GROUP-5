<?php
require_once "../config/db_connect.php";

$message = "";
$messageType = "black";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";

    $allowed_roles = [
        "patient",
        "doctor",
        "receptionist",
        "hospital_admin",
        "database_admin"
    ];

    if ($full_name === "" || $email === "" || $password === "" || $role === "") {
        $message = "All fields are required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email address.";
        $messageType = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";
        $messageType = "error";
    } elseif (!in_array($role, $allowed_roles, true)) {
        $message = "Invalid user role.";
        $messageType = "error";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $message = "Email already exists.";
            $messageType = "error";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "ssss", $full_name, $email, $hashed_password, $role);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Registration successful! You can now login.";
                $messageType = "success";
            } else {
                $message = "Registration failed: " . mysqli_error($conn);
                $messageType = "error";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Hospital Appointment System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #003B5C;
            --accent: #E4B43A;
            --bg: #F0F4F8;
            --text: #1F2937;
            --text-muted: #6B7280;
            --border: #D1D5DB;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            background-image: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text);
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 59, 92, 0.08);
            border-top: 6px solid var(--accent);
        }

        .brand { text-align: center; margin-bottom: 25px; }
        .brand h1 { color: var(--primary); font-size: 24px; font-weight: 700; letter-spacing: -0.5px; margin-bottom: 8px; }
        .brand p { color: var(--text-muted); font-size: 14px; font-weight: 500; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 16px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            transition: all 0.2s ease;
            background-color: #F9FAFB;
            color: var(--text);
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 59, 92, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.2s;
        }

        .btn-login:hover { background-color: #002a42; }

        .alert { padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 500; margin-bottom: 20px; text-align: center; }
        .alert.error { background-color: #FEE2E2; color: #B91C1C; border: 1px solid #F87171; }
        .alert.success { background-color: #D1FAE5; color: #047857; border: 1px solid #34D399; }

        .footer-links { text-align: center; margin-top: 20px; font-size: 14px; color: var(--text-muted); }
        .footer-links a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .footer-links a:hover { text-decoration: underline; color: var(--accent); }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand">
        <h1>Create Account</h1>
        <p>Join the Hospital System</p>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="John Doe" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="name@example.com" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Min. 8 characters" minlength="8" required>
        </div>

        <div class="form-group">
            <label>Register As</label>
            <select name="role" required>
                <option value="patient">Patient</option>
                <option value="doctor">Doctor</option>
                <option value="receptionist">Receptionist</option>
                <option value="hospital_admin">Hospital Admin</option>
                <option value="database_admin">Database Admin</option>
            </select>
        </div>

        <button type="submit" class="btn-login">Register</button>
    </form>

    <div class="footer-links">
        Already have an account? <a href="login.php">Sign In</a>
    </div>
</div>

</body>
</html>