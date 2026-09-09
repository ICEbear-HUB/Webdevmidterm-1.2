<?php
include "database.php";
include "includes/header.php";

$cartItems = [];
$total = 0.0;

if (!empty($_SESSION["cart"])) {
    $ids = array_map("intval", array_keys($_SESSION["cart"]));
    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $types = str_repeat("i", count($ids));

    $stmt = $conn->prepare(
        "SELECT id, product_name, flavor, price, image, quantity
         FROM products
         WHERE id IN ($placeholders)"
    );
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $qty = $_SESSION["cart"][$row["id"]];
        $subtotal = $qty * (float) $row["price"];
        $total += $subtotal;

        $cartItems[] = [
            "id" => $row["id"],
            "product_name" => $row["product_name"],
            "flavor" => $row["flavor"],
            "price" => $row["price"],
            "image" => $row["image"],
            "stock" => $row["quantity"],
            "qty" => $qty,
            "subtotal" => $subtotal,
        ];
    }
}
?>

<section class="cart-section">
    <div class="cart-content">
        <h2>YOUR CART</h2>

        <?php if (empty($cartItems)): ?>
            <p class="cart-empty">
                Your cart is empty.
                <a href="index.php#products">Browse products</a>.
            </p>
        <?php else: ?>
            <div class="cart-list">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>"
                             alt="<?php echo htmlspecialchars($item['flavor']); ?>">

                        <div class="cart-item-info">
                            <h3>
                                <?php echo htmlspecialchars($item['product_name']); ?>
                                -
                                <?php echo htmlspecialchars($item['flavor']); ?>
                            </h3>
                            <p>$<?php echo number_format((float) $item['price'], 2); ?> each</p>
                        </div>

                        <form method="POST" action="update_cart.php" class="cart-qty-form">
                            <input type="hidden" name="product_id" value="<?php echo (int) $item['id']; ?>">
                            <input type="number"
                                   name="quantity"
                                   min="1"
                                   max="<?php echo (int) $item['stock']; ?>"
                                   value="<?php echo (int) $item['qty']; ?>">
                            <button type="submit" name="update" class="btn-edit">UPDATE</button>
                            <button type="submit" name="remove" class="btn-delete">REMOVE</button>
                        </form>

                        <p class="cart-item-subtotal">
                            $<?php echo number_format($item['subtotal'], 2); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <p>Total: <strong>$<?php echo number_format($total, 2); ?></strong></p>
                <a href="checkout.php" class="order-btn">PROCEED TO CHECKOUT</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include "includes/footer.php"; ?>
