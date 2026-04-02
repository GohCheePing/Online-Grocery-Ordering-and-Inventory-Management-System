<?php
// 1. Start the session to store administrator login data
session_start();

// 2. Include database connection file
require '../db.php';

// 3. Check if the login form has been submitted
if($_POST){
    // Retrieve credentials from the POST request
    $user = $_POST['username'];
    $pass = $_POST['password'];

    /** * Database Query:
     * Search the 'admin' table for the matching username.
     */
    $res = $conn->query("SELECT * FROM admin WHERE username='$user'");
    $a = $res->fetch_assoc();

    /**
     * Authentication Check:
     * If the user exists and the password matches, grant access.
     */
    if($a && $pass == $a['password']){
        // Create an admin session variable to lock/unlock protected pages
        $_SESSION['admin'] = 1;
        
        // Redirect to the Admin Dashboard upon successful login
        header("Location: dashboard.php");
        exit();
    } else {
        // Simple error feedback for incorrect credentials
        echo "<p style='color:red;'>Invalid Username or Password!</p>";
    }
}
?>

<h2>Administrator Login</h2>
<form method="POST">
    <label>Username:</label><br>
    <input name="username" required><br><br>
    
    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>
    
    <button type="submit">Login to Dashboard</button>
</form>