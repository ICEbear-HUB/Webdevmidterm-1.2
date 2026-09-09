<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// MUST BE LOGGED IN
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// MUST BE ADMIN
if ($_SESSION["role"] !== "admin") {
    header("Location: ../index.php");
    exit();
}
