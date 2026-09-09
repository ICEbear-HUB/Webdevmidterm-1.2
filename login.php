<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "database.php";

$message = "";
$success = "";

if (isset($_SESSION["success"])) {
    $success = $_SESSION["success"];
    unset($_SESSION["success"]);
}

if (isset($_POST["login"])) {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $message = "Please fill in both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            session_regenerate_id(true);

            header("Location: " . ($user["role"] === "admin" ? "admin/dashboard.php" : "index.php"));
            exit();
        } else {
            // Same generic message whether the account is missing or the
            // password is wrong. Avoids revealing which one it was.
            $message = "Incorrect email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ACE PLUS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
<div class="auth-container">
    <a href="index.php" class="auth-logo">ACE <span>PLUS</span></a>

    <h2>WELCOME BACK</h2>
    <p class="auth-subtitle">Login to your ACE PLUS account</p>

    <?php if ($success !== ""): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($message !== ""): ?>
        <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
        <input type="email" name="email" placeholder="Email Address"
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login" class="auth-button">LOGIN</button>
    </form>

    <p class="auth-switch">
        Don't have an account? <a href="signup.php">Sign up here</a>
    </p>
</div>
</body>
</html>
