<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user_id'])) {
    die("Please <a href='login.php'>Login</a> to checkout.");
}

$conn->begin_transaction();
try {
    $total = $_POST['total'];
    $user_id = $_SESSION['user_id'];

    // 1. 创建订单
    $stmt = $conn->prepare("INSERT INTO `order` (customer_id, total_amount, order_status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // 2. 扣除库存并记录明细
    foreach($_SESSION['cart'] as $id => $qty) {
        // SQL 锁：确保库存充足才更新
        $upd = $conn->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?");
        $upd->bind_param("iii", $qty, $id, $qty);
        $upd->execute();

        if($upd->affected_rows == 0) throw new Exception("Insufficient stock for an item.");

        $item = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, price) VALUES (?, ?, ?, 0)");
        $p_res = $conn->query("SELECT price FROM product WHERE product_id=$id")->fetch_assoc();
        $item->bind_param("iiid", $order_id, $id, $qty, $p_res['price']);
        $item->execute();
    }

    $conn->commit();
    unset($_SESSION['cart']);
    echo "<script>alert('Order Success!'); location.href='orders.php';</script>";
} catch (Exception $e) {
    $conn->rollback();
    echo "Order Failed: " . $e->getMessage();
}
?>