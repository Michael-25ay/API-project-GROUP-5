<?php
require_once "includes/session.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Hospital System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Top Navigation Bar -->
    <nav class="navbar">
        <h1>Hospital Management</h1>
        <div class="navbar-user">
            <p><?php echo htmlspecialchars($_SESSION["full_name"]); ?> (<span><?php echo htmlspecialchars($_SESSION["role"]); ?></span>)</p>
            <a href="auth/logout.php" class="btn btn-danger" style="padding: 8px 15px;">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <h2 class="page-title">Welcome to your Dashboard</h2>

        <?php
        switch ($_SESSION["role"]) {
            case "patient":
                echo "<div class='action-card'>";
                echo "<h3>Patient Portal</h3>";
                echo "<p>You can manage your appointments and view your medical history here.</p>";
                echo "<a href='patient/search_specialist.php' class='btn btn-primary btn-dashboard'>Find a Specialist</a>";
                echo "<a href='patient/my_appointments.php' class='btn btn-primary btn-dashboard'>My Appointments</a>";
                echo "</div>";
                break;

            case "doctor":
                echo "<div class='action-card'>";
                echo "<h3>Doctor Portal</h3>";
                echo "<p>You can view your upcoming appointments and patient records here.</p>";
                echo "<button class='btn btn-primary btn-dashboard'>View Today's Schedule</button>";
                echo "</div>";
                break;

            case "receptionist":
                echo "<div class='action-card'>";
                echo "<h3>Receptionist Desk</h3>";
                echo "<p>You can register new patients and manage hospital bookings here.</p>";
                echo "<a href='manage_availability.php' class='btn btn-primary btn-dashboard'>Manage Doctor Availability</a>";
                echo "</div>";
                break;

            case "hospital_admin":
                echo "<div class='action-card'>";
                echo "<h3>Hospital Administrator Access</h3>";
                echo "<p>You can manage hospital users, staff, and overall system operations.</p>";
                echo "<a href='admin_dashboard.php' class='btn btn-primary btn-dashboard'>Go to Admin Dashboard</a>";
                echo "</div>";
                break;

            case "database_admin":
                echo "<div class='action-card'>";
                echo "<h3>Database Administrator Access</h3>";
                echo "<p>You have full access to database management, backups, and system health.</p>";
                echo "<br><a href='admin/admin_dashboard.php' class='btn btn-primary btn-dashboard'>Go to DB Admin Dashboard</a>";
                echo "</div>";
                break;

            default:
                echo "<p>Unknown role.</p>";
        }
        ?>
    </div>

</body>
</html>