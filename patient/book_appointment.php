<?php
require_once "../includes/session.php";
requireRole(['patient']);
require_once "../config/db_connect.php";

function back_with(string $type, string $text, int $doctorId): void
{
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
    header("Location: my_appointments.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: search_specialist.php");
    exit;
}

$doctorId = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
if (!$doctorId) {
    header("Location: search_specialist.php");
    exit;
}

// 1. CSRF check
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    back_with('error', 'Invalid form request. Please try again.', $doctorId);
}

// 2. Validate the inputs
$date   = trim($_POST['appointment_date'] ?? '');
$time   = trim($_POST['appointment_time'] ?? '');
$reason = trim($_POST['reason_for_visit'] ?? '');
$reason = mb_substr($reason, 0, 500);

$dateObj = DateTime::createFromFormat('Y-m-d', $date);
$timeObj = DateTime::createFromFormat('H:i', $time);
if (!$dateObj || $dateObj->format('Y-m-d') !== $date || !$timeObj || $timeObj->format('H:i') !== $time) {
    back_with('error', 'Invalid date or time selected.', $doctorId);
}

$patientId = (int)$_SESSION['user_id'];

// Everything below runs as one transaction so two people can't grab the same slot
mysqli_begin_transaction($conn);

try {
    // 3. Lock the slot row and make sure it is still open and in the future
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM doctor_availability
         WHERE doctor_id = ? AND available_date = ? AND slot_time = ? AND is_available = 1
           AND TIMESTAMP(available_date, slot_time) > NOW()
         LIMIT 1 FOR UPDATE"
    );
    mysqli_stmt_bind_param($stmt, 'iss', $doctorId, $date, $time);
    mysqli_stmt_execute($stmt);
    $slot = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$slot) {
        mysqli_rollback($conn);
        back_with('error', 'That slot is no longer available.', $doctorId);
    }

    // 4. Is the slot already booked by someone else?
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM appointments
         WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?
           AND status IN ('pending','approved') LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'iss', $doctorId, $date, $time);
    mysqli_stmt_execute($stmt);
    $taken = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($taken) {
        mysqli_rollback($conn);
        back_with('error', 'Sorry, that slot has just been booked by someone else.', $doctorId);
    }

    // 5. Does this patient already have another appointment at the same time?
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM appointments
         WHERE patient_id = ? AND appointment_date = ? AND appointment_time = ?
           AND status IN ('pending','approved') LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'iss', $patientId, $date, $time);
    mysqli_stmt_execute($stmt);
    $clash = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($clash) {
        mysqli_rollback($conn);
        back_with('error', 'You already have an appointment at that date and time.', $doctorId);
    }

    // 6. Save the appointment
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, reason_for_visit)
         VALUES (?, ?, ?, ?, 'pending', ?)"
    );
    mysqli_stmt_bind_param($stmt, 'iisss', $patientId, $doctorId, $date, $time, $reason);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_commit($conn);
} catch (Throwable $e) {
    mysqli_rollback($conn);
    back_with('error', 'Could not book the appointment. Please try again.', $doctorId);
}

$_SESSION['flash'] = ['type' => 'success', 'text' => 'Appointment booked. It is now pending approval.'];
header("Location: my_appointments.php");
exit;