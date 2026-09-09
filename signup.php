<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "database.php";

$message = "";

if (isset($_POST["signup"])) {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // ===== SERVER-SIDE VALIDATION =====
    if ($name === "" || $email === "" || $password === "" || $confirm_password === "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // CHECK IF EMAIL ALREADY EXISTS
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $message = "An account with that email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "user";

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $_SESSION["success"] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $message = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | ACE PLUS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
<div class="auth-container">
    <a href="index.php" class="auth-logo">ACE <span>PLUS</span></a>

    <h2>CREATE ACCOUNT</h2>
    <p class="auth-subtitle">Join the ACE PLUS community</p>

    <?php if ($message !== ""): ?>
        <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
        <input type="text" name="name" placeholder="Full Name"
               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        <input type="email" name="email" placeholder="Email Address"
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        <input type="password" name="password" placeholder="Password (min. 8 characters)"
               minlength="8" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password"
               minlength="8" required>
        <button type="submit" name="signup" class="auth-button">CREATE ACCOUNT</button>
    </form>

    <p class="auth-switch">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>
</body>
</html>
