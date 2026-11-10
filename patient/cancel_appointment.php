<?php
require_once "../includes/session.php";
requireRole(['patient']);
require_once "../config/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: my_appointments.php");
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid form request. Please try again.'];
    header("Location: my_appointments.php");
    exit;
}

$appointmentId = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
$patientId = (int)$_SESSION['user_id'];

if (!$appointmentId) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid appointment selected.'];
    header("Location: my_appointments.php");
    exit;
}

// Only cancel the patient's OWN appointment, and only if it is still pending/approved and in the future.
// The row is kept (status = cancelled) so the slot becomes free again automatically.
$stmt = mysqli_prepare(
    $conn,
    "UPDATE appointments SET status = 'cancelled'
     WHERE id = ? AND patient_id = ? AND status IN ('pending','approved')
       AND TIMESTAMP(appointment_date, appointment_time) > NOW()"
);
mysqli_stmt_bind_param($stmt, 'ii', $appointmentId, $patientId);
mysqli_stmt_execute($stmt);
$changed = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if ($changed > 0) {
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Your appointment has been cancelled.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'This appointment cannot be cancelled.'];
}

header("Location: my_appointments.php");
exit;