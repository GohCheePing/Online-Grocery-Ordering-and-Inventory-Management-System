<?php
session_start();
require 'db.php';

if($_POST){
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // 修复：这里必须使用 -> 符号
    $stmt = $conn->prepare("SELECT * FROM customer WHERE email=?");
    $stmt->bind_param("s", $email); // <--- 这里改成了 ->
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if($user && password_verify($pass, $user['password'])){
        $_SESSION['user_id'] = $user['customer_id'];
        header("Location: homepage.php");
        exit(); // 习惯性加上 exit，防止跳转后代码继续执行
    } else {
        echo "<p style='color:red;'>Login Failed: Invalid email or password.</p>";
    }
}
?>

<h2>Customer Login</h2>
<form method="POST">
    <label>Email Address:</label><br>
    <input name="email" type="email" required><br><br>
    
    <label>Password:</label><br>
    <input name="password" type="password" required><br><br>
    
    <button type="submit">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register here</a></p>