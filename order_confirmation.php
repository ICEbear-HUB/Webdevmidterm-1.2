<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$orderId = isset($_GET["order_id"]) ? (int) $_GET["order_id"] : 0;
$userId = (int) $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT id, total_amount, payment_method, order_status, created_at
     FROM orders
     WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$order = $result->fetch_assoc();

include "includes/header.php";
?>

<section class="cart-section">
    <div class="cart-content confirmation">
        <h2>THANK YOU!</h2>
        <p>Your order <strong>#<?php echo (int) $order['id']; ?></strong> has been placed successfully.</p>
        <p>Total: <strong>$<?php echo number_format((float) $order['total_amount'], 2); ?></strong></p>
        <p>Payment Method: <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong></p>
        <p>
            Status:
            <span class="status-badge status-<?php echo htmlspecialchars($order['order_status']); ?>">
                <?php echo strtoupper($order['order_status']); ?>
            </span>
        </p>
        <a href="profile.php" class="order-btn">VIEW MY ORDERS</a>
        <a href="index.php" class="order-btn order-btn-outline">CONTINUE SHOPPING</a>
    </div>
</section>

<?php include "includes/footer.php"; ?>
    