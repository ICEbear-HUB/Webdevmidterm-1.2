<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "database.php";

// =========================
// MUST BE LOGGED IN
// =========================
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

// =========================
// ACCOUNT INFO
// =========================
$stmt = $conn->prepare("SELECT name, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: logout.php");
    exit();
}

// =========================
// ORDER HISTORY
// =========================
$orders = [];
$orderStmt = $conn->prepare(
    "SELECT id, total_amount, payment_method, order_status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$orderStmt->bind_param("i", $userId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

while ($order = $orderResult->fetch_assoc()) {
    // Fetch the items that belong to this order
    $itemStmt = $conn->prepare(
        "SELECT oi.quantity, oi.price, p.product_name, p.flavor, p.image
         FROM order_items oi
         LEFT JOIN products p ON p.id = oi.product_id
         WHERE oi.order_id = ?"
    );
    $itemStmt->bind_param("i", $order["id"]);
    $itemStmt->execute();
    $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $order["items"] = $items;
    $orders[] = $order;
}
?>
<?php include "includes/header.php"; ?>

<section class="cart-section">
    <div class="cart-content profile-content">

        <h2>MY ACCOUNT</h2>

        <!-- ACCOUNT INFO -->
        <div class="profile-card">
            <div class="profile-info-row">
                <span class="profile-label">Name</span>
                <span class="profile-value"><?php echo htmlspecialchars($user["name"]); ?></span>
            </div>
            <div class="profile-info-row">
                <span class="profile-label">Email</span>
                <span class="profile-value"><?php echo htmlspecialchars($user["email"]); ?></span>
            </div>
            <div class="profile-info-row">
                <span class="profile-label">Member Since</span>
                <span class="profile-value"><?php echo date("F j, Y", strtotime($user["created_at"])); ?></span>
            </div>
        </div>

        <h2 class="profile-section-title">MY ORDERS</h2>

        <?php if (empty($orders)): ?>
            <p class="cart-empty">
                You haven't placed any orders yet.
                <a href="index.php#products">Browse products</a>.
            </p>
        <?php else: ?>
            <div class="order-history">
                <?php foreach ($orders as $order): ?>
                    <details class="order-card">
                        <summary class="order-card-summary">
                            <span>Order #<?php echo (int) $order["id"]; ?></span>
                            <span><?php echo date("M j, Y g:ia", strtotime($order["created_at"])); ?></span>
                            <span class="status-badge status-<?php echo htmlspecialchars($order["order_status"]); ?>">
                                <?php echo strtoupper($order["order_status"]); ?>
                            </span>
                            <span class="order-card-total">$<?php echo number_format((float) $order["total_amount"], 2); ?></span>
                        </summary>

                        <div class="order-card-body">
                            <p class="order-payment">
                                Payment Method:
                                <strong><?php echo htmlspecialchars($order["payment_method"] ?? "N/A"); ?></strong>
                            </p>

                            <div class="order-items-list">
                                <?php foreach ($order["items"] as $item): ?>
                                    <div class="cart-item">
                                        <img src="<?php echo htmlspecialchars($item["image"] ?? 'assets/images/logo-removebg-preview.png'); ?>"
                                             alt="<?php echo htmlspecialchars($item["flavor"] ?? ''); ?>">
                                        <div class="cart-item-info">
                                            <h3>
                                                <?php echo htmlspecialchars($item["product_name"] ?? "Product removed"); ?>
                                                <?php if (!empty($item["flavor"])): ?>
                                                    - <?php echo htmlspecialchars($item["flavor"]); ?>
                                                <?php endif; ?>
                                            </h3>
                                            <p>
                                                Qty: <?php echo (int) $item["quantity"]; ?>
                                                &times; $<?php echo number_format((float) $item["price"], 2); ?>
                                            </p>
                                        </div>
                                        <p class="cart-item-subtotal">
                                            $<?php echo number_format($item["quantity"] * (float) $item["price"], 2); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include "includes/footer.php"; ?>
