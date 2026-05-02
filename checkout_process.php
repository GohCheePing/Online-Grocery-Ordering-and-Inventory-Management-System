<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first!'); location.href='login.php';</script>";
    exit();
}
if (empty($_SESSION['cart'])) {
    echo "<script>alert('Your cart is empty!'); location.href='homepage.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$total_amount = 0;

foreach ($_SESSION['cart'] as $product_id => $qty) {

    $stmt = $conn->prepare("SELECT price FROM product WHERE product_id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $price = $stmt->get_result()->fetch_assoc()['price'];

    $total_amount += $price * $qty;
}
$conn->begin_transaction();

try {
    $stmt_order = $conn->prepare("INSERT INTO `orders` (customer_id, total_amount, order_status) VALUES (?, ?, 'Pending')");
    $stmt_order->bind_param("id", $user_id, $total_amount);
    $stmt_order->execute();
    
    $order_id = $conn->insert_id;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        
        $stmt_p = $conn->prepare("SELECT price, stock_quantity FROM product WHERE product_id = ?");
        $stmt_p->bind_param("i", $product_id);
        $stmt_p->execute();
        $product_data = $stmt_p->get_result()->fetch_assoc();
        $unit_price = $product_data['price'];
        $current_stock = $product_data['stock_quantity'];

        if ($current_stock < $quantity) {
            throw new Exception("Product ID $product_id is out of stock!");
        }
        $stmt_item = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $unit_price);
        $stmt_item->execute();

        $stmt_update = $conn->prepare(
            "UPDATE product 
            SET stock_quantity = stock_quantity - ? 
            WHERE product_id = ? AND stock_quantity >= ?"
        );
        $stmt_update->bind_param("iii", $quantity, $product_id, $quantity);
        $stmt_update->execute();

        if ($stmt_update->affected_rows === 0) {
            throw new Exception("Product ID $product_id is out of stock!");
        }
    }
    $conn->commit();
    unset($_SESSION['cart']);
    echo "<script>alert('Order placed successfully!'); location.href='orders.php';</script>";
} catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('Checkout failed: " . $e->getMessage() . "'); location.href='cart.php';</script>";
}
$conn->close();
?>