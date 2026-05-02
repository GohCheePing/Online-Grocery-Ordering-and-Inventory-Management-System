<?php
session_start();
require 'db.php';

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart - FreshMart</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="cart-page">

<div class="cart-header">
    <a href="homepage.php" class="back-btn">← Continue Shopping</a>
    <div class="cart-title">Shopping Cart</div>
</div>

<h2>🛒 Your Shopping Cart</h2>

<?php if (!empty($_SESSION['cart'])): ?>

<div class="cart-container">
    <div class="cart-items">

        <?php foreach ($_SESSION['cart'] as $product_id => $qty): ?>

            <?php
            $stmt = $conn->prepare("SELECT * FROM product WHERE product_id=?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $p = $stmt->get_result()->fetch_assoc();

            $sub = $p['price'] * $qty;
            $total += $sub;
            ?>

            <div class="cart-row"
                 data-id="<?php echo $product_id; ?>"
                 data-price="<?php echo $p['price']; ?>">

                <div class="cart-img">
                    <img src="images/<?php echo strtolower(str_replace(' ','_',$p['product_name'])); ?>.jpg"
                         onerror="this.src='images/default.jpeg'">
                </div>

                <div class="cart-left">
                    <div class="cart-name"><?php echo $p['product_name']; ?></div>
                    <div class="cart-price">RM <?php echo number_format($p['price'],2); ?></div>
                </div>

                <div class="cart-middle">
                    <button onclick="updateCart(<?php echo $product_id; ?>,'minus')">-</button>

                    <input class="qty-input" type="number"
                           value="<?php echo $qty; ?>"
                           onchange="setQty(<?php echo $product_id; ?>, this.value)">

                    <button onclick="updateCart(<?php echo $product_id; ?>,'plus')">+</button>
                </div>

                <div class="cart-right subtotal">
                    RM <?php echo number_format($sub,2); ?>
                </div>

                <div class="cart-remove">
                    <button onclick="updateCart(<?php echo $product_id; ?>,'remove')">Remove</button>
                </div>

            </div>

        <?php endforeach; ?>

    </div>

    <div class="cart-summary">
        <h3>Order Summary</h3>

        <p>Total Items:
            <span id="items"><?php echo array_sum($_SESSION['cart']); ?></span>
        </p>

        <h2 id="total">RM <?php echo number_format($total,2); ?></h2>

        <form action="checkout_process.php" method="POST">
            <button class="checkout-btn">Checkout</button>
        </form>
    </div>

</div>

<?php else: ?>
    <div class="cart-container">
        <p>Your cart is empty 😢 </p>
    </div>
<?php endif; ?>

<script>
function updateCart(id, action){
    fetch('manage_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id, action })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === "success"){
            updateUI(data.cart, data.totalItems);
        } else {
            showToast(data.msg || "Error");
        }
    });
}

function setQty(id, value){
    value = parseInt(value);
    if(value <= 0){
        updateCart(id, 'remove');
        return;
    }

    fetch('manage_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id, action: 'set', qty: value })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === "success"){
            updateUI(data.cart, data.totalItems);
        } else {
            showToast(data.msg || "Stock limit reached");
        }
    });
}

function updateUI(cart, totalItems){
    let total = 0;

    document.querySelectorAll('.cart-row').forEach(row => {
        let id = row.dataset.id;

        if(!cart[id]){
            row.remove();
            return;
        }

        let qty = cart[id];
        let price = parseFloat(row.dataset.price);

        row.querySelector('.qty-input').value = qty;
        row.querySelector('.subtotal').innerText = "RM " + (qty * price).toFixed(2);

        total += qty * price;
    });

    document.getElementById("total").innerText = "RM " + total.toFixed(2);
    document.getElementById("items").innerText = totalItems;

    if(Object.keys(cart).length === 0){
        document.querySelector('.cart-container').innerHTML =
        "<p>Your cart is empty 😢</p>";
    }
}
</script>
</body>
</html>