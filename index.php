<?php
include "database.php";
include "includes/header.php";

// =========================
// FETCH PRODUCTS FOR THE STOREFRONT
// =========================
$products = $conn->query(
    "SELECT id, product_name, flavor, price, image
     FROM products
     WHERE status = 'available' AND quantity > 0
     ORDER BY id ASC"
);
?>

<!-- HERO SECTION -->
<section id="home" class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <p class="green-text">
                THE NEW ERA OF ENERGY DRINK IS HERE
            </p>
            <h1>
                MEET THE NEW<br>
                LIFE <span>BOOST</span>
            </h1>
            <p class="description">
                ACE PLUS gives you the energy you need to keep moving,
                stay focused, and push beyond your limits.
            </p>
        </div>
        <div class="hero-image">
            <!-- TWO CANS IMAGE -->
            <img src="assets/images/Drinks.png"
                 alt="ACE PLUS Energy Drinks">
        </div>
    </div>
</section>

<!-- WHAT'S NEW -->
<section id="latest" class="latest">
    <div class="latest-wrapper">
        <!-- LEFT ORANGE -->
        <div class="latest-side-product">
            <img src="assets/images/orangewhatsnew.svg"
                 alt="Orange Flavor">
        </div>
        <!-- CENTER -->
        <div class="latest-center">
            <h2>WHAT'S NEW WITH ACE</h2>
            <div class="new-flavor-box">
                <img src="assets/images/whatsnewfront.svg"
                     alt="New ACE PLUS Flavors">
                <div class="flavor-text">
                    <h3>NEW FLAVORS!!!</h3>
                    <p>PLUS ULTRA</p>
                </div>
            </div>
        </div>
        <!-- RIGHT APPLE -->
        <div class="latest-side-product">
            <img src="assets/images/applewhatsnew.svg"
                 alt="Apple Flavor">
        </div>
    </div>
</section>

<!-- PRODUCTS -->
<section id="products" class="products">
    <div class="products-content">
        <h2>EXPLORE THE LINEUP</h2>
        <button onclick="orderNow()" class="order-btn">
            ORDER NOW
        </button>
        <div class="product-list">
            <?php if ($products && $products->num_rows > 0): ?>
                <?php while ($p = $products->fetch_assoc()): ?>
                    <div class="product" data-product-id="<?php echo (int) $p['id']; ?>">
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($p['image']); ?>"
                                 alt="<?php echo htmlspecialchars($p['product_name']); ?> <?php echo htmlspecialchars($p['flavor']); ?>">
                        </div>
                        <h3 class="product-flavor"><?php echo htmlspecialchars($p['flavor']); ?></h3>
                        <p class="price">$<?php echo number_format((float) $p['price'], 2); ?></p>
                        <div class="product-actions" onclick="event.stopPropagation()">
                            <div class="product-qty-picker">
                                <button type="button" class="qty-btn qty-minus" aria-label="Decrease quantity">−</button>
                                <input type="number" class="qty-input" value="1" min="1" max="<?php echo (int) $p['quantity']; ?>" readonly>
                                <button type="button" class="qty-btn qty-plus" aria-label="Increase quantity">+</button>
                            </div>
                            <button type="button" class="add-to-cart-btn" data-product-id="<?php echo (int) $p['id']; ?>">
                                ADD TO CART
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#cccccc; text-align:center; width:100%;">
                    No products available right now — check back soon.
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- BENEFITS -->
<section id="benefits" class="benefits">
    <div class="benefits-content">
        <h2>BENEFITS</h2>
        <div class="benefit-container">
            <div class="benefit">
                <div class="benefit-icon">
                    <img src="assets/images/icons/iconenergy.svg" alt="Energy Boost icon">
                </div>
                <div class="benefit-text">
                    <h3>ENERGY BOOST</h3>
                    <p>
                        A fast-acting caffeine and taurine blend that gets you moving 
                        without the jitters or the crash.
                    </p>
                </div>
            </div>
            <div class="benefit">
                <div class="benefit-icon">
                    <img src="assets/images/icons/iconPerformance.svg" alt="Performance icon">
                </div>
                <div class="benefit-text">
                    <h3>PERFORMANCE</h3>
                    <p>
                        Formulated to support stamina and output, whether you're training, 
                        working, or grinding a deadline.
                    </p>
                </div>
            </div>
            <div class="benefit">
                <div class="benefit-icon">
                    <img src="assets/images/icons/iconbattery.svg" alt="Endurance icon">
                </div>
                <div class="benefit-text">
                    <h3>ENDURANCE</h3>
                    <p>
                        Formulated to support stamina and output, 
                        whether you're training, working, or grinding a deadline.
                    </p>
                </div>
            </div>
            <div class="benefit">
                <div class="benefit-icon">
                    <img src="assets/images/icons/iconfocus.svg" alt="Better Focus icon">
                </div>
                <div class="benefit-text">
                    <h3>BETTER FOCUS</h3>
                    <p>
                        Stay focused while studying,
                        working or exercising.A clean formula 
                        that sharpens concentration, so your mind keeps pace with your body.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section id="about" class="about">
    <div class="about-content">
        <h2>ABOUT US</h2>
        <p>
            ACE PLUS is an energy drink made for people who want
            to stay active, focused, and energized. Our goal is
            to provide a refreshing drink that helps you keep moving.
        </p>
    </div>
</section>

<?php
include "includes/footer.php";
?>
