<?php
require_once "../includes/session.php";
requireRole(['patient']);
require_once "../config/db_connect.php";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$patientId = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.reason_for_visit,
            u.full_name AS doctor_name, dp.specialty, dp.room_number,
            (TIMESTAMP(a.appointment_date, a.appointment_time) > NOW()) AS is_upcoming
     FROM appointments a
     JOIN doctor_profiles dp ON dp.id = a.doctor_id
     JOIN users u ON u.id = dp.user_id
     WHERE a.patient_id = ?
     ORDER BY a.appointment_date DESC, a.appointment_time DESC"
);
mysqli_stmt_bind_param($stmt, 'i', $patientId);
mysqli_stmt_execute($stmt);
$appointments = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .wrap{max-width:1000px;margin:35px auto;padding:0 18px}
        .panel{background:#fff;padding:22px;border-radius:12px;box-shadow:0 3px 15px #00000012;margin:18px 0}
        .table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse}
        th,td{text-align:left;padding:12px;border-bottom:1px solid #e6e9ee;vertical-align:top}
        th{background:#f5f7fa}
        .notice{padding:12px 15px;border-radius:7px;margin:12px 0;background:#e9f7ef;color:#17653b}
        .notice.error{background:#fdecec;color:#9b2222}
        .badge{padding:4px 10px;border-radius:12px;font-size:13px;font-weight:600;display:inline-block}
        .pending{background:#fff4d6;color:#8a6100}
        .approved{background:#e3f4e8;color:#17653b}
        .cancelled{background:#fdecec;color:#9b2222}
        .completed,.complete{background:#e6eefc;color:#1d4fa0}
        .cancel-btn{padding:7px 11px;border:0;border-radius:6px;background:#b42318;color:#fff;cursor:pointer}
        .muted{color:#687386}
    </style>
</head>
<body>
<nav class="navbar">
    <h1>Hospital Management</h1>
    <div class="navbar-user">
        <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
        <a href="../dashboard.php" class="btn btn-primary">Dashboard</a>
    </div>
</nav>

<main class="wrap">
    <h2>My Appointments</h2>
    <a href="search_specialist.php" class="btn btn-primary">Book a new appointment</a>

    <?php if ($flash): ?>
        <div class="notice <?php echo $flash['type'] === 'error' ? 'error' : ''; ?>">
            <?php echo htmlspecialchars($flash['text']); ?>
        </div>
    <?php endif; ?>

    <section class="panel">
        <?php if ($appointments && mysqli_num_rows($appointments) > 0): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th><th>Time</th><th>Doctor</th><th>Room</th>
                        <th>Reason</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($appointments)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('D, d M Y', strtotime($row['appointment_date']))); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['appointment_time'], 0, 5)); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['doctor_name']); ?><br>
                            <span class="muted"><?php echo htmlspecialchars($row['specialty']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($row['room_number'] ?: 'Not assigned'); ?></td>
                        <td><?php echo htmlspecialchars($row['reason_for_visit'] ?: '-'); ?></td>
                        <td>
                            <span class="badge <?php echo htmlspecialchars($row['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['is_upcoming'] && in_array($row['status'], ['pending', 'approved'], true)): ?>
                                <form method="post" action="cancel_appointment.php"
                                      onsubmit="return confirm('Cancel this appointment?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="appointment_id" value="<?php echo (int)$row['id']; ?>">
                                    <button type="submit" class="cancel-btn">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span class="muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="muted">You have no appointments yet.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>