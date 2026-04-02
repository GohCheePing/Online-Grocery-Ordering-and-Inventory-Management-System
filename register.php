<?php
// 1. Include the database connection file
require 'db.php';

/**
 * Form Submission Check:
 * Process the data only if the user has submitted the form via POST.
 */
if($_POST){
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    /**
     * Security: Password Hashing
     * Instead of saving the plain password, we use password_hash().
     * This creates a secure, encrypted string that cannot be easily reversed.
     */
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    /**
     * Security: Prepared Statement
     * Use '?' placeholders to safely insert user data.
     * This is the standard method to prevent SQL Injection.
     */
    $stmt = $conn->prepare("INSERT INTO customer (name, email, password) VALUES (?, ?, ?)");
    
    // Bind the variables: "sss" means three strings (name, email, password)
    $stmt->bind_param("sss", $name, $email, $pass);
    
    /**
     * Execution and Feedback:
     * If the database insert is successful, provide a link to the login page.
     */
    if($stmt->execute()) {
        echo "<p style='color:green; padding:20px;'>Registration Successful! <a href='login.php'>Click here to Login</a></p>";
    } else {
        // Display database error (e.g., if the email is already registered)
        echo "<p style='color:red; padding:20px;'>Error: " . $conn->error . "</p>";
    }
}
?>

<form method="POST" style="padding: 50px; font-family: Arial, sans-serif;">
    <h2>Customer Registration</h2>
    
    <label>Full Name:</label><br>
    <input name="name" placeholder="Enter your full name" required style="padding: 8px; width: 250px;"><br><br>
    
    <label>Email Address:</label><br>
    <input name="email" type="email" placeholder="example@mail.com" required style="padding: 8px; width: 250px;"><br><br>
    
    <label>Password:</label><br>
    <input name="password" type="password" placeholder="Create a password" required style="padding: 8px; width: 250px;"><br><br>
    
    <button type="submit" style="background: #2ecc71; color: white; padding: 10px 20px; border: none; cursor: pointer;">
        Register Account
    </button>
    
    <p>Already have an account? <a href="login.php">Login here</a></p>
</form>