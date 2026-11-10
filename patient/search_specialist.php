<?php
require_once "../includes/session.php";
requireRole(['patient']);
require_once "../config/db_connect.php";
$search = trim($_GET['search'] ?? '');
$sql = "SELECT dp.id AS doctor_id, u.full_name, dp.specialty, dp.room_number FROM doctor_profiles dp JOIN users u ON u.id = dp.user_id WHERE u.role = 'doctor'";
if ($search !== '') {
    $sql .= " AND (u.full_name LIKE ? OR dp.specialty LIKE ?)";
    $stmt = mysqli_prepare($conn, $sql . " ORDER BY u.full_name");
    $term = '%' . $search . '%';
    mysqli_stmt_bind_param($stmt, 'ss', $term, $term);
    mysqli_stmt_execute($stmt);
    $doctors = mysqli_stmt_get_result($stmt);
} else {
    $doctors = mysqli_query($conn, $sql . " ORDER BY u.full_name");
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Find a Specialist</title><link rel="stylesheet" href="style.css"><style>.wrap{max-width:1000px;margin:35px auto;padding:0 18px}.panel{background:#fff;padding:22px;border-radius:12px;box-shadow:0 3px 15px #00000012;margin:18px 0}.searchbar{display:flex;gap:10px;flex-wrap:wrap}.searchbar input{flex:1;min-width:220px;padding:12px;border:1px solid #ccd3dc;border-radius:7px}.doctor-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}.doctor{border:1px solid #e4e8ee;border-radius:10px;padding:18px}.doctor h3{margin:0 0 8px}.muted{color:#687386}</style></head><body>
<nav class="navbar"><h1>Hospital Management</h1><div class="navbar-user"><span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span><a href="../dashboard.php" class="btn btn-primary">Dashboard</a></div></nav>
<main class="wrap"><h2>Find a Specialist</h2><p class="muted">Search doctors by name or medical specialty.</p><section class="panel"><form method="get" class="searchbar"><input type="search" name="search" placeholder="e.g. Cardiology or Dr. Adrian" value="<?php echo htmlspecialchars($search); ?>"><button class="btn btn-primary" type="submit">Search</button><a class="btn" href="search_specialist.php">Clear</a></form></section>
<div class="doctor-grid"><?php if ($doctors && mysqli_num_rows($doctors)>0): while($doctor=mysqli_fetch_assoc($doctors)): ?><article class="doctor"><h3><?php echo htmlspecialchars($doctor['full_name']); ?></h3><p><strong>Specialty:</strong> <?php echo htmlspecialchars($doctor['specialty']); ?></p><p><strong>Room:</strong> <?php echo htmlspecialchars($doctor['room_number'] ?: 'Not assigned'); ?></p><a class="btn btn-primary" href="view_slots.php?doctor_id=<?php echo (int)$doctor['doctor_id']; ?>">View Available Slots</a></article><?php endwhile; else: ?><section class="panel">No specialists match your search.</section><?php endif; ?></div></main></body></html>
