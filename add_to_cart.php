<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "database.php";

header("Content-Type: application/json");

// =========================
// MUST BE LOGGED IN TO BUY
// =========================
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "requiresLogin" => true,
        "message" => "Please login first to add items to your cart."
    ]);
    exit();
}

$productId = isset($_POST["product_id"]) ? (int) $_POST["product_id"] : 0;
$requestedQty = isset($_POST["quantity"]) ? max(1, (int) $_POST["quantity"]) : 1;

if ($productId <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid product."]);
    exit();
}

// =========================
// VERIFY PRODUCT + STOCK
// =========================
$stmt = $conn->prepare("SELECT id, quantity, status FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Product not found."]);
    exit();
}

$product = $result->fetch_assoc();

if ($product["status"] === "sold_out") {
    echo json_encode(["success" => false, "message" => "This product is currently sold out."]);
    exit();
}

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

$currentQty = isset($_SESSION["cart"][$productId]) ? $_SESSION["cart"][$productId] : 0;
$newQty = $currentQty + $requestedQty;

if ($newQty > (int) $product["quantity"]) {
    echo json_encode([
        "success" => false,
        "message" => "Only " . (int) $product["quantity"] . " left in stock."
    ]);
    exit();
}

$_SESSION["cart"][$productId] = $newQty;

echo json_encode([
    "success" => true,
    "message" => "Added to cart!",
    "cartCount" => array_sum($_SESSION["cart"])
]);
