<?php
// 1. Include the database connection
require '../db.php';

/** * Data Retrieval:
 * Get the unique Order ID from the URL using the GET method.
 */
$id = $_GET['id'];

/**
 * Update Logic:
 * Execute a SQL query to change the order status to 'Completed'.
 * This identifies the specific order using the ID retrieved above.
 */
$conn->query("UPDATE `order` SET order_status='Completed' WHERE order_id=$id");

/**
 * Redirection:
 * Automatically send the admin back to the Dashboard after the update.
 */
header("Location: dashboard.php");
exit(); // Best practice to stop script execution after a redirect
?>