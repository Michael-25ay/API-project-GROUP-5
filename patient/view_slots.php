<?php
require_once "../includes/session.php";
requireRole(['patient']);
require_once "../config/db_connect.php";
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$doctorId = filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT);
$date = trim($_GET['date'] ?? '');
if (!$doctorId) { header('Location: search_specialist.php'); exit; }
$doctorStmt = mysqli_prepare($conn, "SELECT dp.id, u.full_name, dp.specialty, dp.room_number FROM doctor_profiles dp JOIN users u ON u.id = dp.user_id WHERE dp.id = ? AND u.role = 'doctor' LIMIT 1");
mysqli_stmt_bind_param($doctorStmt, 'i', $doctorId);
mysqli_stmt_execute($doctorStmt);
$doctor = mysqli_fetch_assoc(mysqli_stmt_get_result($doctorStmt));
if (!$doctor) { http_response_code(404); exit('Specialist not found.'); }
$sql = "SELECT da.available_date, da.slot_time FROM doctor_availability da WHERE da.doctor_id = ? AND da.is_available = 1 AND da.available_date >= CURDATE() AND NOT EXISTS (SELECT 1 FROM appointments a WHERE a.doctor_id = da.doctor_id AND a.appointment_date = da.available_date AND a.appointment_time = da.slot_time AND a.status IN ('pending','approved'))";
if ($date !== '') { $sql .= " AND da.available_date = ?"; }
$sql .= " ORDER BY da.available_date, da.slot_time";
$slotStmt = mysqli_prepare($conn, $sql);
if ($date !== '') { mysqli_stmt_bind_param($slotStmt, 'is', $doctorId, $date); } else { mysqli_stmt_bind_param($slotStmt, 'i', $doctorId); }
mysqli_stmt_execute($slotStmt);
$slots = mysqli_stmt_get_result($slotStmt);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Available Appointment Slots</title><link rel="stylesheet" href="style.css"><style>.wrap{max-width:850px;margin:35px auto;padding:0 18px}.panel{background:#fff;padding:22px;border-radius:12px;box-shadow:0 3px 15px #00000012;margin:18px 0}.filter{display:flex;gap:10px;align-items:end;flex-wrap:wrap}.filter label{display:block;font-weight:600;margin-bottom:7px}.filter input{padding:10px;border:1px solid #ccd3dc;border-radius:7px}.slot-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}.slot{padding:14px;border:1px solid #dce5df;border-radius:8px;background:#f4fbf6}.muted{color:#687386}</style></head><body>
<nav class="navbar"><h1>Hospital Management</h1><div class="navbar-user"><span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span><a href="../dashboard.php" class="btn btn-primary">Dashboard</a></div></nav>
<main class="wrap"><a href="search_specialist.php">&larr; Back to specialists</a><section class="panel"><h2><?php echo htmlspecialchars($doctor['full_name']); ?></h2><p><strong>Specialty:</strong> <?php echo htmlspecialchars($doctor['specialty']); ?> &nbsp; <strong>Room:</strong> <?php echo htmlspecialchars($doctor['room_number'] ?: 'Not assigned'); ?></p><form method="get" class="filter"><input type="hidden" name="doctor_id" value="<?php echo (int)$doctorId; ?>"><div><label for="date">Filter by date</label><input type="date" id="date" name="date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($date); ?>"></div><button class="btn btn-primary" type="submit">Filter</button><a class="btn" href="view_slots.php?doctor_id=<?php echo (int)$doctorId; ?>">Show all</a></form></section><section class="panel"><h3>Available appointment slots</h3><?php if (mysqli_num_rows($slots)>0): ?><div class="slot-list"><?php while($slot=mysqli_fetch_assoc($slots)): ?><div class="slot"><strong><?php echo htmlspecialchars(date('D, d M Y', strtotime($slot['available_date']))); ?></strong><p><?php echo htmlspecialchars(substr($slot['slot_time'],0,5)); ?></p><form method="post" action="book_appointment.php">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
<input type="hidden" name="doctor_id" value="<?php echo (int)$doctorId; ?>">
<input type="hidden" name="appointment_date" value="<?php echo htmlspecialchars($slot['available_date']); ?>">
<input type="hidden" name="appointment_time" value="<?php echo htmlspecialchars(substr($slot['slot_time'],0,5)); ?>">
<button class="btn btn-primary" type="submit">Book</button>
</form></div><?php endwhile; ?></div><?php else: ?><p class="muted">There are no available slots for this specialist<?php echo $date ? ' on the selected date' : ''; ?>. Please try another date or specialist.</p><?php endif; ?></section></main></body></html>
