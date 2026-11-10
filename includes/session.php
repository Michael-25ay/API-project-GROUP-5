<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

function requireRole(array $allowedRoles): void
{
    if (!isset($_SESSION["role"])) {
        header("Location: ../auth/login.php");
        exit;
    }

    if (!in_array($_SESSION["role"], $allowedRoles, true)) {
        header("Location: ../access_denied.php");
        exit;
    }
}
?>
