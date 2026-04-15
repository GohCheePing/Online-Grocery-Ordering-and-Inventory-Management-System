<?php
/**
 * 1. Initialize the session to access active data.
 */
session_start();

/**
 * 2. Clear all session variables.
 */
session_unset();

/**
 * 3. Destroy the session entirely.
 */
session_destroy();

/**
 * 4. Display a simple message instead of redirecting.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logout Successful</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding-top: 50px; }
        .msg { color: #27ae60; font-weight: bold; font-size: 20px; }
        a { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <div class="msg">You have been logged out successfully.</div>
    <p>Thank you for shopping at FreshMart!</p>
    <br>
    <a href="homepage.php">Return to Shop</a> | <a href="login.php">Login Again</a>
</body>
</html>