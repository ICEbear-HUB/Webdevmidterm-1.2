<?php
include "../includes/auth.php";
include "../database.php";

// =========================
// DASHBOARD STATISTICS
// =========================
$productData = $conn->query("SELECT COUNT(*) AS total_products FROM products")->fetch_assoc();

$customerData = $conn->query(
    "SELECT COUNT(*) AS total_customers FROM users WHERE role = 'user'"
)->fetch_assoc();

$orderData = $conn->query("SELECT COUNT(*) AS total_orders FROM orders")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | ACE PLUS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">
<div class="admin-container">

    <!-- HEADER -->
    <div class="admin-header">
        <div>
            <h1>ACE PLUS ADMIN</h1>
            <p>Dashboard Overview</p>
        </div>
        <a href="../logout.php" class="admin-logout">LOGOUT</a>
    </div>

    <!-- WELCOME -->
    <div class="admin-welcome">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?></h2>
        <p>Manage your ACE PLUS Energy Drink system.</p>
    </div>

    <!-- STATISTICS -->
    <div class="dashboard-cards">
        <div class="dashboard-card">
            <h3>TOTAL PRODUCTS</h3>
            <p><?php echo (int) $productData["total_products"]; ?></p>
        </div>

        <div class="dashboard-card">
            <h3>TOTAL CUSTOMERS</h3>
            <p><?php echo (int) $customerData["total_customers"]; ?></p>
        </div>

        <div class="dashboard-card">
            <h3>TOTAL ORDERS</h3>
            <p><?php echo (int) $orderData["total_orders"]; ?></p>
        </div>
    </div>

    <!-- ADMIN MENU -->
    <div class="admin-menu">
        <a href="products.php">
            <div class="admin-menu-card">
                <h3>📦 MANAGE PRODUCTS</h3>
                <p>View and manage all ACE PLUS products.</p>
            </div>
        </a>

        <a href="add_product.php">
            <div class="admin-menu-card">
                <h3>➕ ADD PRODUCT</h3>
                <p>Add new products to the system.</p>
            </div>
        </a>

        <a href="orders.php">
            <div class="admin-menu-card">
                <h3>🛒 CUSTOMER ORDERS</h3>
                <p>Track customer purchases and orders.</p>
            </div>
        </a>

        <a href="customers.php">
            <div class="admin-menu-card">
                <h3>👥 CUSTOMERS</h3>
                <p>View registered customers.</p>
            </div>
        </a>

        <a href="../index.php">
            <div class="admin-menu-card">
                <h3>🌐 VIEW WEBSITE</h3>
                <p>Return to the ACE PLUS website.</p>
            </div>
        </a>
    </div>

</div>
</body>
</html>
