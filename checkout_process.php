<?php
// 1. Initialize session to identify the user and retrieve cart items
session_start();
// 2. Database connection
require 'db.php';

/**
 * Authentication Check:
 * Ensure the user is logged in before placing an order.
 */
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first!'); location.href='login.php';</script>";
    exit();
}

/**
 * Validation Check:
 * Ensure the cart contains items before processing.
 */
if (empty($_SESSION['cart'])) {
    echo "<script>alert('Your cart is empty!'); location.href='homepage.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$total_amount = $_POST['total'];

/**
 * Database Transaction:
 * Begin a transaction to ensure all operations (Order, Items, Inventory) 
 * succeed together or fail together (Data Integrity).
 */

$conn->begin_transaction();

try {
    // 1. Create a record in the main 'order' table
    $stmt_order = $conn->prepare("INSERT INTO `order` (customer_id, total_amount, order_status) VALUES (?, ?, 'Pending')");
    $stmt_order->bind_param("id", $user_id, $total_amount);
    $stmt_order->execute();
    
    // Retrieve the unique ID generated for this order
    $order_id = $conn->insert_id;

    // 2. Loop through each item in the session cart
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        
        // Fetch current price and inventory level for the product
        $p_query = $conn->query("SELECT price, stock_quantity FROM product WHERE product_id = $product_id");
        $product_data = $p_query->fetch_assoc();
        $unit_price = $product_data['price'];
        $current_stock = $product_data['stock_quantity'];

        /**
         * Stock Validation:
         * If the requested quantity exceeds available stock, stop the process.
         */
        if ($current_stock < $quantity) {
            throw new Exception("Product ID $product_id is out of stock!");
        }

        // 3. Create a record for each product in the 'order_item' table
        $stmt_item = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $unit_price);
        $stmt_item->execute();

        // 4. Inventory Management: Deduct the purchased quantity from the stock
        $stmt_update = $conn->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
        $stmt_update->bind_param("ii", $quantity, $product_id);
        $stmt_update->execute();
    }

    /**
     * Commit Transaction:
     * Permanently save all changes to the database.
     */
    $conn->commit();

    // Clear the shopping cart after successful checkout
    unset($_SESSION['cart']);

    echo "<script>alert('Order placed successfully!'); location.href='orders.php';</script>";

} catch (Exception $e) {
    /**
     * Rollback Transaction:
     * If an error occurs (e.g., out of stock), undo all changes.
     */
    $conn->rollback();
    echo "<script>alert('Checkout failed: " . $e->getMessage() . "'); location.href='cart.php';</script>";
}

// Close connection to free up resources
$conn->close();
?>