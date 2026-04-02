<?php
session_start();
require 'db.php';

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first!'); location.href='login.php';</script>";
    exit();
}

// 检查购物车是否为空
if (empty($_SESSION['cart'])) {
    echo "<script>alert('Your cart is empty!'); location.href='homepage.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$total_amount = $_POST['total'];

// --- 开启事务 (Transaction) ---
// 确保订单、明细、库存扣除要么全部成功，要么全部失败
$conn->begin_transaction();

try {
    // 1. 插入主订单表 (order)
    // 注意：这里的 SQL 字段名需对应你的数据库 (customer_id, total_amount, order_status)
    $stmt_order = $conn->prepare("INSERT INTO `order` (customer_id, total_amount, order_status) VALUES (?, ?, 'Pending')");
    $stmt_order->bind_param("id", $user_id, $total_amount);
    $stmt_order->execute();
    
    // 获取刚刚生成的订单 ID
    $order_id = $conn->insert_id;

    // 2. 遍历购物车，处理每一个产品
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        
        // 获取产品的最新价格和库存
        $p_query = $conn->query("SELECT price, stock_quantity FROM product WHERE product_id = $product_id");
        $product_data = $p_query->fetch_assoc();
        $unit_price = $product_data['price'];
        $current_stock = $product_data['stock_quantity'];

        // 检查库存是否足够
        if ($current_stock < $quantity) {
            throw new Exception("Product ID $product_id is out of stock!");
        }

        // 3. 插入订单明细表 (order_item)
        // 【核心修复】：这里的 4 个问号对应 4 个参数 (iiid)
        $stmt_item = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $unit_price);
        $stmt_item->execute();

        // 4. 更新产品库存 (inventory update)
        $stmt_update = $conn->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
        $stmt_update->bind_param("ii", $quantity, $product_id);
        $stmt_update->execute();
    }

    // --- 提交事务 ---
    $conn->commit();

    // 结账成功后清空购物车
    unset($_SESSION['cart']);

    echo "<script>alert('Order placed successfully!'); location.href='orders.php';</script>";

} catch (Exception $e) {
    // --- 出错回滚 ---
    $conn->rollback();
    echo "<script>alert('Checkout failed: " . $e->getMessage() . "'); location.href='cart.php';</script>";
}

$conn->close();
?>