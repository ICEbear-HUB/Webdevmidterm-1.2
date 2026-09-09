<?php
include "../includes/auth.php";
include "../database.php";

$message = "";

// =========================
// UPDATE ORDER STATUS
// =========================
if (isset($_POST["update_status"])) {
    $orderId = (int) $_POST["order_id"];
    $status = $_POST["status"];

    $allowedStatuses = ["pending", "processing", "completed", "cancelled"];
    if (in_array($status, $allowedStatuses, true)) {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $orderId);
        $stmt->execute();
        $message = "Order #" . $orderId . " updated to " . strtoupper($status) . ".";
    }
}

// =========================
// FETCH ORDERS WITH CUSTOMER + ITEM COUNT
// =========================
$orders = $conn->query(
    "SELECT
        o.id,
        o.total_amount,
        o.payment_method,
        o.order_status,
        o.created_at,
        u.name AS customer_name,
        u.email AS customer_email,
        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
     FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders | ACE PLUS Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">
<div class="admin-container">

    <!-- HEADER -->
    <div class="admin-header">
        <div>
            <h1>CUSTOMER ORDERS</h1>
            <p>Track and update the status of customer orders.</p>
        </div>
        <a href="dashboard.php" class="admin-logout">BACK TO DASHBOARD</a>
    </div>

    <?php if ($message !== ""): ?>
        <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- ORDERS TABLE -->
    <table class="admin-table">
        <thead>
        <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Date</th>
            <th>Update</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($orders && $orders->num_rows > 0): ?>
            <?php while ($row = $orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo (int) $row["id"]; ?></td>
                    <td>
                        <?php echo htmlspecialchars($row["customer_name"]); ?><br>
                        <small><?php echo htmlspecialchars($row["customer_email"]); ?></small>
                    </td>
                    <td><?php echo (int) $row["item_count"]; ?></td>
                    <td>$<?php echo number_format((float) $row["total_amount"], 2); ?></td>
                    <td><?php echo htmlspecialchars($row["payment_method"] ?? '—'); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo htmlspecialchars($row["order_status"]); ?>">
                            <?php echo strtoupper($row["order_status"]); ?>
                        </span>
                    </td>
                    <td><?php echo date("M j, Y g:ia", strtotime($row["created_at"])); ?></td>
                    <td>
                        <form method="POST" class="inline-status-form">
                            <input type="hidden" name="order_id" value="<?php echo (int) $row['id']; ?>">
                            <select name="status">
                                <option value="pending" <?php echo $row["order_status"] === "pending" ? "selected" : ""; ?>>Pending</option>
                                <option value="processing" <?php echo $row["order_status"] === "processing" ? "selected" : ""; ?>>Processing</option>
                                <option value="completed" <?php echo $row["order_status"] === "completed" ? "selected" : ""; ?>>Completed</option>
                                <option value="cancelled" <?php echo $row["order_status"] === "cancelled" ? "selected" : ""; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn-edit">SAVE</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="admin-empty">No orders yet.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>
