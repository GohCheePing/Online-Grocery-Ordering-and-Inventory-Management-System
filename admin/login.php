<?php
session_start();
require '../db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // FIXED (ONLY ONCE)

    $stmt->close();

    if ($user && password_verify($pass, $user['admin_password'])) {

        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_full_name'] = $user['full_name'];

        header("Location: dashboard.php");
        exit();

    } else {
        $error = "Invalid email or password.";
    }
}
?>

<h2>Admin Login</h2>

<?php if ($error) echo "<p style='color:red'>".htmlspecialchars($error)."</p>"; ?>

<form method="POST">
Email: <input type="email" name="email" required><br><br>
Password: <input type="password" name="password" required><br><br>
<button type="submit">Login</button>
</form>

<p><a href="admin_register.php">Register</a></p>