<?php
include "../includes/auth.php";
include "../database.php";

$message = "";
$messageType = "";

// =========================
// DELETE PRODUCT
// =========================
if (isset($_GET["delete"])) {
    $deleteId = (int) $_GET["delete"];

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $deleteId);

    if ($stmt->execute()) {
        $message = "Product deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Could not delete product. It may be linked to an existing order.";
        $messageType = "error";
    }
}

// =========================
// FETCH ALL PRODUCTS
// =========================
$products = $conn->query(
    "SELECT id, product_name, flavor, price, image, quantity, status
     FROM products
     ORDER BY id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | ACE PLUS Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">
<div class="admin-container">

    <!-- HEADER -->
    <div class="admin-header">
        <div>
            <h1>MANAGE PRODUCTS</h1>
            <p>View, edit, and remove ACE PLUS products.</p>
        </div>
        <a href="dashboard.php" class="admin-logout">BACK TO DASHBOARD</a>
    </div>

    <?php if ($message !== ""): ?>
        <div class="<?php echo $messageType === "success" ? "success-message" : "error-message"; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- ACTION BAR -->
    <div class="admin-actionbar">
        <a href="add_product.php" class="order-btn">+ ADD NEW PRODUCT</a>
    </div>

    <!-- PRODUCTS TABLE -->
    <table class="admin-table">
        <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Flavor</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($products && $products->num_rows > 0): ?>
            <?php while ($row = $products->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="../<?php echo htmlspecialchars($row["image"]); ?>"
                             alt="<?php echo htmlspecialchars($row["product_name"]); ?>"
                             class="admin-thumb">
                    </td>
                    <td><?php echo htmlspecialchars($row["product_name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["flavor"]); ?></td>
                    <td>$<?php echo number_format((float) $row["price"], 2); ?></td>
                    <td><?php echo (int) $row["quantity"]; ?></td>
                    <td>
                        <?php if ($row["status"] === "sold_out"): ?>
                            <span class="status-badge status-cancelled">SOLD OUT</span>
                        <?php else: ?>
                            <span class="status-badge status-completed">AVAILABLE</span>
                        <?php endif; ?>
                    </td>
                    <td class="admin-actions">
                        <a href="edit_product.php?id=<?php echo (int) $row['id']; ?>" class="btn-edit">EDIT</a>
                        <a href="products.php?delete=<?php echo (int) $row['id']; ?>"
                           class="btn-delete"
                           onclick="return confirm('Delete this product? This cannot be undone.');">DELETE</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="admin-empty">No products yet. Add your first product.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>
