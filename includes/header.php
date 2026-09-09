<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = isset($_SESSION["cart"]) ? array_sum($_SESSION["cart"]) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>ACE PLUS Energy Drink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet"
          href="css/style.css">
</head>
<body>
<header>
    <div class="navbar">
        <!-- LOGO -->
        <a href="index.php"
           class="logo-link">
            <img src="assets/images/logo-removebg-preview.png"
                 class="logo"
                 alt="ACE PLUS Logo">
        </a>
        <!-- HAMBURGER BUTTON (Mobile) -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <!-- NAVIGATION -->
        <nav id="navMenu">
            <a href="index.php#home">HOME</a>
            <a href="index.php#products">PRODUCTS</a>
            <a href="index.php#latest">THE LATEST</a>
            <a href="index.php#benefits">BENEFITS</a>
        </nav>
        <!-- ACCOUNT BUTTONS -->
        <div class="account-buttons">
            <!-- CART -->
            <a href="cart.php" class="cart-link">
                🛒 CART <span class="cart-count"><?php echo (int) $cartCount; ?></span>
            </a>
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="profile.php" class="account-link">
                    <span class="welcome-user">
                        HI,
                        <?php
                        echo htmlspecialchars(
                            $_SESSION["user_name"]
                        );
                        ?>
                    </span>
                </a>
                <?php if ($_SESSION["role"] === "admin"): ?>
                    <a href="admin/dashboard.php">
                        <button class="signup-btn">
                            ADMIN PANEL
                        </button>
                    </a>
                <?php endif; ?>
                <a href="logout.php">
                    <button class="login-btn">
                        LOGOUT
                    </button>
                </a>
            <?php else: ?>
                <a href="signup.php">
                    <button class="signup-btn">
                        SIGN UP
                    </button>
                </a>
                <a href="login.php">
                    <button class="login-btn">
                        LOGIN
                    </button>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
