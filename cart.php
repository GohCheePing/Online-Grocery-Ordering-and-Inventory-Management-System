<?php
session_start();
// 连接数据库
$conn = new mysqli("localhost", "root", "", "freshmart");

// 假设购物车数据存在 Session 中
// $_SESSION['cart'] = [ product_id => quantity ]
$cart_items = $_SESSION['cart'] ?? [];

?>
<div class="cart-page">
    <h2>Shopping Cart</h2>
    <table>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>
        <?php 
        $grand_total = 0;
        foreach($cart_items as $id => $qty): 
            $res = $conn->query("SELECT * FROM product WHERE product_id = $id");
            $p = $res->fetch_assoc();
            $subtotal = $p['price'] * $qty;
            $grand_total += $subtotal;
        ?>
        <tr>
            <td><?php echo $p['product_name']; ?></td>
            <td>RM <?php echo number_format($p['price'], 2); ?></td>
            <td><?php echo $qty; ?></td>
            <td>RM <?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <h3>Total: RM <?php echo number_format($grand_total, 2); ?></h3>
    <form action="checkout_process.php" method="POST">
        <button type="submit">Confirm Checkout</button>
    </form>
</div>