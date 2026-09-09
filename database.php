<?php

// =========================
// ERROR HANDLING (no raw PHP errors shown to users)
// =========================
error_reporting(E_ALL);
ini_set("display_errors", "0");
ini_set("log_errors", "1");
ini_set("error_log", __DIR__ . "/logs/php-error.log");

// =========================
// DATABASE CONNECTION
// =========================
$host = "localhost";
$username = "root";
$password = "";
$database = "aceplus_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    // Log the real error for the developer, show a safe message to the user
    error_log("Database connection failed: " . $conn->connect_error);
    die("We're having trouble connecting right now. Please try again shortly.");
}

$conn->set_charset("utf8mb4");
