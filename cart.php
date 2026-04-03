<?php
// 1. Initialize session to retrieve stored cart items
session_start();
// 2. Connect to the database to get latest product prices and names
require 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Cart - FreshMart</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="padding: 40px; background-color: #f4f7f6;">
    <h2>Your Shopping Cart</h2>
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <?php
        // Initialize total amount variable
        $total = 0;

        /**
         * Cart Validation:
         * Check if the session cart array is not empty before processing.
         */
        if(!empty($_SESSION['cart'])):
            // Loop through each item ID and its quantity stored in the session
            foreach($_SESSION['cart'] as $id => $qty):
                // Fetch current product details from the database using the ID
                $p = $conn->query("SELECT * FROM product WHERE product_id = $id")->fetch_assoc();
                
                // Calculate subtotal for this specific product
                $sub = $p['price'] * $qty;
                // Add the subtotal to the grand total
                $total += $sub;
        ?>
            <div class="cart-item" style="border-bottom: 1px solid #eee; padding: 10px 0; display: flex; justify-content: space-between;">
                <span><strong><?php echo $p['product_name']; ?></strong></span>
                <span>RM <?php echo number_format($p['price'], 2); ?> x <?php echo $qty; ?></span>
                <span style="font-weight: bold;">RM <?php echo number_format($sub, 2); ?></span>
            </div>
        <?php endforeach; ?>

            <h3 style="text-align: right; margin-top: 20px; color: #27ae60;">
                Total: RM <?php echo number_format($total, 2); ?>
            </h3>

            <form action="checkout_process.php" method="POST" style="text-align: right;">
                <input type="hidden" name="total" value="<?php echo $total; ?>">
                <button class="btn-checkout" style="background: #2ecc71; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    Proceed to Checkout
                </button>
            </form>

        <?php else: ?>
            <p>Your cart is empty. <a href="homepage.php" style="color: #2ecc71;">Go shopping now</a></p>
        <?php endif; ?>
    </div>
</body>
</html>