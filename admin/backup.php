<?php
require_once "../includes/session.php";
requireRole(['database_admin', 'hospital_admin']); // Security check!
require_once "../config/db_connect.php";

$message = "";

// Check if the backup button was clicked
if (isset($_POST['backup'])) {
    $dbname = "appointmentsystem";
    $filename = "backups/backup_" . date("Y-m-d_H-i-s") . ".sql";

    // 1. Create the backups folder if it doesn't exist
    if (!is_dir('backups')) { 
        mkdir('backups', 0777, true); 
    }

    // Path to mysqldump in XAMPP
    $mysqldumpPath = "C:/xampp/mysql/bin/mysqldump.exe";

    // Command to export the database
    $command = "\"$mysqldumpPath\" --user=root --password= --databases $dbname > $filename";

    // Execute the command
    system($command, $output);

    if ($output === 0) {
        $message = "<div class='alert success'>✅ Backup Successful! File created: <a href='$filename' target='_blank'>Download Backup</a></div>";
    } else {
        $message = "<div class='alert error'>❌ Backup failed. Check XAMPP permissions.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Backup System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Database Backup Management</h1>
        
        <?php echo $message; ?>

        <!-- This form triggers your PHP code above! -->
        <form method="POST">
            <p>Click the button below to generate a full SQL backup of the database.</p>
            <button type="submit" name="backup" class="btn btn-primary">📥 Generate & Download Backup</button>
        </form>
        
        <br>
        <a href="admin_dashboard.php">← Back to Admin Dashboard</a>
    </div>
</body>
</html>