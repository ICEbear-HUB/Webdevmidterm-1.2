<?php
include "../includes/auth.php";
include "../database.php";

// =========================
// FETCH CUSTOMERS + ORDER STATS
// =========================
$customers = $conn->query(
    "SELECT
        u.id,
        u.name,
        u.email,
        u.created_at,
        COUNT(o.id) AS order_count,
        COALESCE(SUM(o.total_amount), 0) AS total_spent
     FROM users u
     LEFT JOIN orders o ON o.user_id = u.id
     WHERE u.role = 'user'
     GROUP BY u.id, u.name, u.email, u.created_at
     ORDER BY u.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers | ACE PLUS Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">
<div class="admin-container">

    <!-- HEADER -->
    <div class="admin-header">
        <div>
            <h1>CUSTOMERS</h1>
            <p>Everyone registered on the ACE PLUS website.</p>
        </div>
        <a href="dashboard.php" class="admin-logout">BACK TO DASHBOARD</a>
    </div>

    <!-- CUSTOMERS TABLE -->
    <table class="admin-table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Orders</th>
            <th>Total Spent</th>
            <th>Joined</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($customers && $customers->num_rows > 0): ?>
            <?php while ($row = $customers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                    <td><?php echo (int) $row["order_count"]; ?></td>
                    <td>$<?php echo number_format((float) $row["total_spent"], 2); ?></td>
                    <td><?php echo date("M j, Y", strtotime($row["created_at"])); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="admin-empty">No customers have registered yet.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>
