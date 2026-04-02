<?php
session_start();
$conn = new mysqli("localhost", "root", "", "freshmart");

// 1. 开启事务，确保数据一致性
$conn->begin_transaction();

try {
    $customer_id = $_SESSION['user_id']; // 假设已登录
    $total = $_POST['total_amount'];

    // 2. 创建订单
    $stmt = $conn->prepare("INSERT INTO `order` (customer_id, total_amount, order_status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("id", $customer_id, $total);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // 3. 处理购物车里的每一件商品
    foreach($_SESSION['cart'] as $id => $qty) {
        // 检查并扣除库存
        $update_stock = $conn->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?");
        $update_stock->bind_param("iii", $qty, $id, $qty);
        $update_stock->execute();

        if ($update_stock->affected_rows == 0) {
            throw new Exception("Stock not enough for product ID: $id");
        }

        // 插入订单明细
        $item_stmt = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity) VALUES (?, ?, ?)");
        $item_stmt->bind_param("iii", $order_id, $id, $qty);
        $item_stmt->execute();
    }

    $conn->commit();
    echo "Order Successful!";
} catch (Exception $e) {
    $conn->rollback();
    echo "Order Failed: " . $e->getMessage();
}
?>