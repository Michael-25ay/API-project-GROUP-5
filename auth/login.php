<?php
session_start();
if (isset($_SESSION["user_id"])) {
    header("Location: ../dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Hospital Appointment System</title>
    <!-- Including Google Font for a modern look -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Embedded Directly so it ALWAYS works */
        :root {
            --primary: #003B5C; /* Deep Blue */
            --accent: #E4B43A;  /* Gold */
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
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 50px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 59, 92, 0.08);
            border-top: 6px solid var(--accent);
        }

        .brand {
            text-align: center;
            margin-bottom: 35px;
        }

        .brand h1 {
            color: var(--primary);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .brand p {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            transition: all 0.2s ease;
            background-color: #F9FAFB;
        }

        .form-group input:focus {
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
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #002a42;
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .alert {
            background-color: #FEE2E2;
            color: #B91C1C;
            padding: 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 22px;
            text-align: center;
            border: 1px solid #F87171;
        }

        .footer-links {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .footer-links a:hover {
            text-decoration: underline;
            color: var(--accent);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand">
        <h1>Hospital Appointment</h1>
        <p>Management System</p>
    </div>

    <?php if (isset($_GET["error"])): ?>
        <div class="alert">
            Invalid email or password. Please try again.
        </div>
    <?php endif; ?>

    <form action="authenticate.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="name@example.com" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn-login">Sign In</button>
    </form>

    <div class="footer-links">
        New patient? <a href="register.php">Create an account</a>
    </div>
</div>

</body>
</html>