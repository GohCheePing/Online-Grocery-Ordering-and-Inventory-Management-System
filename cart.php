<div class="cart-container">
    <h2>Shopping Cart</h2>
    <table border="1" width="100%">
        <tr>
            <th>Item</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
        </tr>
        <?php
        $total = 0;
        if(isset($_SESSION['cart'])) {
            foreach($_SESSION['cart'] as $id => $qty) {
                $res = $conn->query("SELECT * FROM product WHERE product_id = $id");
                $p = $res->fetch_assoc();
                $sub = $p['price'] * $qty;
                $total += $sub;
                echo "<tr>
                        <td>{$p['product_name']}</td>
                        <td>RM " . number_format($p['price'], 2) . "</td>
                        <td>$qty</td>
                        <td>RM " . number_format($sub, 2) . "</td>
                      </tr>";
            }
        }
        ?>
    </table>
    <h3>Grand Total: RM <?php echo number_format($total, 2); ?></h3>
    
    <form action="checkout_process.php" method="POST">
        <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
        <button type="submit" class="btn-checkout">Confirm Checkout</button>
    </form>
</div>