<?php
// 1. Initialize the session to identify the logged-in user
session_start();

// 2. Include the database connection
require 'db.php';

/**
 * Access Control:
 * Retrieve the current User ID from the session. 
 * (Note: You should add a check here to redirect to login.php if ID is missing)
 */
$id = $_SESSION['user_id'];

/**
 * Data Retrieval:
 * Fetch all orders belonging specifically to this customer.
 * Uses a WHERE clause to filter by the logged-in user's ID.
 */
$res = $conn->query("SELECT * FROM `order` WHERE customer_id=$id");

// Display Title
echo "<h2>My Order History</h2>";

/**
 * Data Rendering:
 * Iterate through the result set and display each order's summary.
 */
while($o = $res->fetch_assoc()){
    // Display Order Number, Price (Formatted), and Status
    echo "<p>";
    echo "Order #{$o['order_id']} - ";
    echo "RM " . number_format($o['total_amount'], 2) . " - ";
    echo "<strong>Status: {$o['order_status']}</strong>";
    echo "</p><hr>";
}

/**
 * Empty State Handling:
 * If no orders are found, provide a link back to the shop.
 */
if($res->num_rows == 0) {
    echo "<p>You haven't placed any orders yet. <a href='homepage.php'>Start shopping!</a></p>";
}
?>