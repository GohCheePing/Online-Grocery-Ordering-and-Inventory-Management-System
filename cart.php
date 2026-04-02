<?php
session_start();
require 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="padding: 40px;">
    <h2>Your Shopping Cart</h2>
    <div style="background: white; padding: 20px; border-radius: 8px;">
        <?php
        $total = 0;
        if(!empty($_SESSION['cart'])):
            foreach($_SESSION['cart'] as $id => $qty):
                $p = $conn->query("SELECT * FROM product WHERE product_id = $id")->fetch_assoc();
                $sub = $p['price'] * $qty;
                $total += $sub;
        ?>
            <div class="cart-item" style="border-bottom: 1px solid #eee; padding: 10px 0;">
                <span><?php echo $p['product_name']; ?></span>
                <span>RM <?php echo number_format($p['price'], 2); ?> x <?php echo $qty; ?></span>
                <span style="float:right;">RM <?php echo number_format($sub, 2); ?></span>
            </div>
        <?php endforeach; ?>
            <h3 style="text-align: right; margin-top: 20px;">Total: RM <?php echo number_format($total, 2); ?></h3>
            <form action="checkout_process.php" method="POST">
                <input type="hidden" name="total" value="<?php echo $total; ?>">
                <button class="btn-checkout" style="margin-top: 10px;">Proceed to Checkout</button>
            </form>
        <?php else: ?>
            <p>Your cart is empty. <a href="homepage.php">Go shopping</a></p>
        <?php endif; ?>
    </div>
</body>
</html>