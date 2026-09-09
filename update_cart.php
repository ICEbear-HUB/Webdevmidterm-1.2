<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================
// MUST BE LOGGED IN
// =========================
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$productId = isset($_POST["product_id"]) ? (int) $_POST["product_id"] : 0;
$quantity = isset($_POST["quantity"]) ? (int) $_POST["quantity"] : 0;

if ($productId > 0) {
    if (isset($_POST["remove"]) || $quantity <= 0) {
        unset($_SESSION["cart"][$productId]);
    } elseif (isset($_POST["update"])) {
        $_SESSION["cart"][$productId] = $quantity;
    }
}

header("Location: cart.php");
exit();
