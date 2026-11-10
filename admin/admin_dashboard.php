<?php
require_once "../includes/session.php";
requireRole(['database_admin', 'hospital_admin']); 
require_once "../config/db_connect.php";

$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$appt_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments"))['count'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>DB Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h2>Database Administration</h2>
            <a href="../auth/logout.php" class="btn btn-logout">Logout</a>
        </header>

        <div class="stats-grid">
            <div class="stat-card"><h3>Total Users</h3><p><?php echo $user_count; ?></p></div>
            <div class="stat-card"><h3>Appointments</h3><p><?php echo $appt_count; ?></p></div>
        </div>

        <div class="admin-actions">
            <h3>Actions</h3>
            <a href="backup.php" class="btn btn-primary">Manage Database Backups</a>
            <a href="manage_availability.php" class="btn btn-primary">Manage Doctor Availability</a>
        </div>
    </div>
</body>
</html>