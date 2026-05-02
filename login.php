<?php
session_start();
require 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['customer_id'];
        $_SESSION['user_email'] = $user['email'];
        header("Location: homepage.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Login</title>
<link rel="stylesheet" href="auth.css">
</head>

<body>

<div class="auth-card">
    <h2>Welcome back</h2>
    <p class="subtitle">Sign in to your Freshmart account</p>

    <?php if (!empty($error)) : ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="input-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <div class="password-wrap">
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                <button type="button" class="toggle-password" onclick="togglePassword()">👁</button>
            </div>
        </div>

        <button type="submit" class="auth-btn">Login</button>

    </form>

    <p class="switch-text">
        Don’t have an account? <a href="register.php">Register here</a>
    </p>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    passwordInput.type = passwordInput.type === "password" ? "text" : "password";
}
</script>

<a href="homepage.php" class="home-btn">← Home</a>

</body>
</html>