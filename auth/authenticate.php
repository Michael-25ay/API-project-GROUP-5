<?php
session_start();
require_once "../config/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    header("Location: login.php?error=1");
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    if (password_verify($password, $user["password"])) {
        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = $user["role"];

        header("Location: ../dashboard.php");
        exit;
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

header("Location: login.php?error=1");
exit;
?>
