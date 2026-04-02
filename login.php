<?php
// 1. Start the session to track the customer's login status
session_start();

// 2. Include the database connection
require 'db.php';

// 3. Check if the login form has been submitted via POST
if($_POST){
    $email = $_POST['email'];
    $pass = $_POST['password'];

    /**
     * Security: Prepared Statement
     * Use a prepared statement to securely fetch user data by email.
     * This prevents SQL Injection attacks.
     */
    $stmt = $conn->prepare("SELECT * FROM customer WHERE email=?");
    $stmt.bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    /**
     * Password Verification:
     * Check if the user exists and verify the plain-text password 
     * against the hashed password stored in the database.
     */
    if($user && password_verify($pass, $user['password'])){
        // Store the customer's unique ID in the session
        $_SESSION['user_id'] = $user['customer_id'];
        
        // Redirect the user to the store homepage
        header("Location: homepage.php");
        exit();
    } else {
        // Error message for failed login attempts
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