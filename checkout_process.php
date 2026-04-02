<?php
session_start();
$conn = new mysqli("localhost", "root", "", "freshmart");

$conn->begin_transaction(); // 开启事务

try {
    // 1. 插入 Order 总表 (对应 freshmart.sql 的 order 表)
    $cust_id = $_SESSION['customer_id'] ?? 1; // 暂时假设 ID 为 1
    $total = $_POST['total_amount'];
    
    $stmt = $conn->prepare("INSERT INTO `order` (customer_id, total_amount) VALUES (?, ?)");
    $stmt->bind_param("id", $cust_id, $total);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // 2. 循环购物车扣库存 (对应目标 3: Reduce inventory shortages)
    foreach($_SESSION['cart'] as $product_id => $quantity) {
        // SQL 检查：只有当库存充足时才扣减
        $update = $conn->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?");
        $update->bind_param("iii", $quantity, $product_id, $quantity);
        $update->execute();

        if($update->affected_rows == 0) {
            throw new Exception("Stock insufficient for some items!");
        }

        // 3. 记录订单明细
        $item = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity) VALUES (?, ?, ?)");
        $item->bind_param("iii", $order_id, $product_id, $quantity);
        $item->execute();
    }

    $conn->commit();
    unset($_SESSION['cart']); // 结账后清空购物车
    echo "Success!";
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>