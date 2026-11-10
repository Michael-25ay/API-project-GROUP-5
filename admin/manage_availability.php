<?php
require_once "../includes/session.php";
requireRole(['hospital_admin', 'database_admin', 'receptionist']);
require_once "../config/db_connect.php";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Invalid form request. Please refresh and try again.';
    } elseif (($_POST['action'] ?? '') === 'add') {
        $doctorId = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
        $date = trim($_POST['available_date'] ?? '');
        $time = trim($_POST['slot_time'] ?? '');
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        $timeObj = DateTime::createFromFormat('H:i', $time);

        if (!$doctorId || !$dateObj || $dateObj->format('Y-m-d') !== $date || $date < date('Y-m-d') || !$timeObj || $timeObj->format('H:i') !== $time) {
            $error = 'Please select a valid doctor, today or a future date, and a valid time.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO doctor_availability (doctor_id, available_date, slot_time, created_by) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'issi', $doctorId, $date, $time, $_SESSION['user_id']);
            if (mysqli_stmt_execute($stmt)) {
                $message = 'Appointment slot added successfully.';
            } elseif (mysqli_errno($conn) === 1062) {
                $error = 'That doctor already has a slot at this date and time.';
            } else {
                $error = 'Could not add the slot. Please check the database setup.';
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (($_POST['action'] ?? '') === 'disable') {
        $slotId = filter_input(INPUT_POST, 'slot_id', FILTER_VALIDATE_INT);
        if ($slotId) {
            $stmt = mysqli_prepare($conn, "UPDATE doctor_availability SET is_available = 0 WHERE id = ? AND available_date >= CURDATE()");
            mysqli_stmt_bind_param($stmt, 'i', $slotId);
            mysqli_stmt_execute($stmt);
            $message = mysqli_stmt_affected_rows($stmt) ? 'Slot removed from availability.' : 'The slot could not be changed.';
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Invalid slot selected.';
        }
    }
}

$doctors = mysqli_query($conn, "SELECT dp.id, u.full_name, dp.specialty FROM doctor_profiles dp JOIN users u ON u.id = dp.user_id WHERE u.role = 'doctor' ORDER BY u.full_name");
$slots = mysqli_query($conn, "SELECT da.id, da.available_date, da.slot_time, da.is_available, u.full_name, dp.specialty,
    (SELECT COUNT(*) FROM appointments a WHERE a.doctor_id = da.doctor_id AND a.appointment_date = da.available_date AND a.appointment_time = da.slot_time AND a.status IN ('pending','approved')) AS is_booked
    FROM doctor_availability da JOIN doctor_profiles dp ON dp.id = da.doctor_id JOIN users u ON u.id = dp.user_id
    WHERE da.available_date >= CURDATE() ORDER BY da.available_date, da.slot_time LIMIT 200");
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Manage Doctor Availability</title><link rel="stylesheet" href="../assets/css/style.css"><style>
.page-wrap{max-width:1050px;margin:35px auto;padding:0 18px}.panel{background:#fff;padding:24px;border-radius:12px;box-shadow:0 3px 15px #00000012;margin:20px 0}.form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:15px;align-items:end}.field label{display:block;font-weight:600;margin-bottom:7px}.field input,.field select{width:100%;padding:11px;border:1px solid #ccd3dc;border-radius:7px}.notice{padding:12px 15px;border-radius:7px;margin:12px 0;background:#e9f7ef;color:#17653b}.error{background:#fdecec;color:#9b2222}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:12px;border-bottom:1px solid #e6e9ee}th{background:#f5f7fa}.small-btn{padding:7px 11px;border:0;border-radius:6px;background:#b42318;color:white;cursor:pointer}.muted{color:#687386}.topline{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}
</style></head><body>
<nav class="navbar"><h1>Hospital Management</h1><div class="navbar-user"><span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span><a href="../dashboard.php" class="btn btn-primary">Dashboard</a></div></nav>
<main class="page-wrap"><div class="topline"><div><h2>Manage Doctor Availability</h2><p class="muted">Add appointment slots for doctors. Patients can see slots that are not booked.</p></div></div>
<?php if ($message): ?><div class="notice"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="notice error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<section class="panel"><h3>Add an appointment slot</h3><form method="post"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"><input type="hidden" name="action" value="add"><div class="form-grid">
<div class="field"><label for="doctor_id">Doctor</label><select name="doctor_id" id="doctor_id" required><option value="">Select a doctor</option><?php while ($doctor = mysqli_fetch_assoc($doctors)): ?><option value="<?php echo (int)$doctor['id']; ?>"><?php echo htmlspecialchars($doctor['full_name'].' — '.$doctor['specialty']); ?></option><?php endwhile; ?></select></div>
<div class="field"><label for="available_date">Date</label><input type="date" id="available_date" name="available_date" min="<?php echo date('Y-m-d'); ?>" required></div>
<div class="field"><label for="slot_time">Start time</label><input type="time" id="slot_time" name="slot_time" required></div>
<div><button type="submit" class="btn btn-primary">Add Slot</button></div></div></form><p class="muted">Each row represents one appointment start time. The slot length is determined by your group's booking process.</p></section>
<section class="panel"><h3>Upcoming slots</h3><div class="table-wrap"><table><thead><tr><th>Doctor</th><th>Specialty</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php if ($slots && mysqli_num_rows($slots) > 0): while ($slot = mysqli_fetch_assoc($slots)): ?><tr><td><?php echo htmlspecialchars($slot['full_name']); ?></td><td><?php echo htmlspecialchars($slot['specialty']); ?></td><td><?php echo htmlspecialchars($slot['available_date']); ?></td><td><?php echo htmlspecialchars(substr($slot['slot_time'],0,5)); ?></td><td><?php echo $slot['is_booked'] ? 'Booked' : ($slot['is_available'] ? 'Available' : 'Disabled'); ?></td><td><?php if (!$slot['is_booked'] && $slot['is_available']): ?><form method="post" onsubmit="return confirm('Remove this slot from availability?');"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"><input type="hidden" name="action" value="disable"><input type="hidden" name="slot_id" value="<?php echo (int)$slot['id']; ?>"><button class="small-btn" type="submit">Remove</button></form><?php else: ?><span class="muted">—</span><?php endif; ?></td></tr><?php endwhile; else: ?><tr><td colspan="6">No upcoming slots have been added.</td></tr><?php endif; ?>
</tbody></table></div></section></main></body></html>
