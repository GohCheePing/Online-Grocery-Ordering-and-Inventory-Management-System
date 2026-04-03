<?php
// 1. Initialize session to store cart data globally
session_start();

// 2. Include database connection to check real-time stock
require 'db.php';

/**
 * Data Retrieval:
 * Get the Product ID from the URL. 
 * Use (int) casting to ensure the ID is a valid number for security.
 */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize the cart as an empty array if it doesn't exist yet
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

/**
 * Real-time Inventory Check:
 * Query the database to find the current stock for the selected product.
 */
$res = $conn->query("SELECT stock_quantity FROM product WHERE product_id = $id");
$p = $res->fetch_assoc();

/**
 * Logic Validation:
 * 1. Check if the product exists in the database ($p).
 * 2. Check if the current quantity in the cart is less than the available stock.
 */
if($p && (!isset($_SESSION['cart'][$id]) || $_SESSION['cart'][$id] < $p['stock_quantity'])) {
    
    // Increment the product quantity in the session cart
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    
    // Return "success" to the AJAX fetch() call in homepage.php
    echo "success";
} else {
    // Return "fail" if the product is out of stock or does not exist
    echo "fail";
}

// Terminate script execution after providing the response
exit();