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

// =========================
// CART CAN'T BE EMPTY
// =========================
if (empty($_SESSION["cart"])) {
    header("Location: cart.php");
    exit();
}

$message = "";

$allowedPaymentMethods = ["Cash on Delivery", "GCash", "Credit/Debit Card"];

// =========================
// PLACE ORDER
// =========================
if (isset($_POST["place_order"])) {
    $paymentMethod = isset($_POST["payment_method"]) ? trim($_POST["payment_method"]) : "";

    if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
        $message = "Please choose a valid payment method.";
    }

    if ($message === "") {
        $ids = array_map("intval", array_keys($_SESSION["cart"]));
        $placeholders = implode(",", array_fill(0, count($ids), "?"));
        $types = str_repeat("i", count($ids));

        $stmt = $conn->prepare(
            "SELECT id, price, quantity FROM products WHERE id IN ($placeholders)"
        );
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        $total = 0.0;
        $stockError = "";

        while ($row = $result->fetch_assoc()) {
            $qty = $_SESSION["cart"][$row["id"]];

            if ($qty > (int) $row["quantity"]) {
                $stockError = "One of your items no longer has enough stock. Please review your cart.";
                break;
            }

            $items[] = [
                "product_id" => $row["id"],
                "qty" => $qty,
                "price" => $row["price"],
            ];
            $total += $qty * (float) $row["price"];
        }

        if ($stockError !== "") {
            $message = $stockError;
        } else {
            $conn->begin_transaction();

            try {
                $userId = (int) $_SESSION["user_id"];

                $orderStmt = $conn->prepare(
                    "INSERT INTO orders (user_id, total_amount, payment_method, order_status)
                     VALUES (?, ?, ?, 'pending')"
                );
                $orderStmt->bind_param("ids", $userId, $total, $paymentMethod);
                $orderStmt->execute();
                $orderId = $conn->insert_id;

            $itemStmt = $conn->prepare(
                "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)"
            );
            $stockStmt = $conn->prepare(
                "UPDATE products SET quantity = quantity - ? WHERE id = ?"
            );
            $statusStmt = $conn->prepare(
                "UPDATE products SET status = 'sold_out' WHERE id = ? AND quantity <= 0"
            );

            foreach ($items as $item) {
                $itemStmt->bind_param(
                    "iiid",
                    $orderId,
                    $item["product_id"],
                    $item["qty"],
                    $item["price"]
                );
                $itemStmt->execute();

                $stockStmt->bind_param("ii", $item["qty"], $item["product_id"]);
                $stockStmt->execute();

                $statusStmt->bind_param("i", $item["product_id"]);
                $statusStmt->execute();
            }

                $conn->commit();
                $_SESSION["cart"] = [];

                header("Location: order_confirmation.php?order_id=" . $orderId);
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Something went wrong placing your order. Please try again.";
            }
        }
    }
}

// =========================
// FETCH CART ITEMS FOR REVIEW
// =========================
$ids = array_map("intval", array_keys($_SESSION["cart"]));
$placeholders = implode(",", array_fill(0, count($ids), "?"));
$types = str_repeat("i", count($ids));

$stmt = $conn->prepare(
    "SELECT id, product_name, flavor, price, image FROM products WHERE id IN ($placeholders)"
);
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$reviewItems = [];
$total = 0.0;

while ($row = $result->fetch_assoc()) {
    $qty = $_SESSION["cart"][$row["id"]];
    $subtotal = $qty * (float) $row["price"];
    $total += $subtotal;
    $reviewItems[] = array_merge($row, ["qty" => $qty, "subtotal" => $subtotal]);
}

include "includes/header.php";
?>

<section class="cart-section">
    <div class="cart-content">
        <h2>CHECKOUT</h2>

        <?php if ($message !== ""): ?>
            <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="cart-list">
            <?php foreach ($reviewItems as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>"
                         alt="<?php echo htmlspecialchars($item['flavor']); ?>">

                    <div class="cart-item-info">
                        <h3>
                            <?php echo htmlspecialchars($item['product_name']); ?>
                            -
                            <?php echo htmlspecialchars($item['flavor']); ?>
                        </h3>
                        <p>
                            Qty: <?php echo (int) $item['qty']; ?>
                            &times; $<?php echo number_format((float) $item['price'], 2); ?>
                        </p>
                    </div>

                    <p class="cart-item-subtotal">
                        $<?php echo number_format($item['subtotal'], 2); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary checkout-summary">
            <p>Total: <strong>$<?php echo number_format($total, 2); ?></strong></p>
            <form method="POST" class="payment-form">
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select payment method</option>
                        <?php foreach ($allowedPaymentMethods as $method): ?>
                            <option value="<?php echo htmlspecialchars($method); ?>"
                                <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === $method) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($method); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="place_order" class="order-btn">PLACE ORDER</button>
            </form>
        </div>
    </div>
</section>

<?php include "includes/footer.php"; ?>
