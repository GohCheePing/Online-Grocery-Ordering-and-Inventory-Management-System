<?php
session_start();
require 'db.php';
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

<?php
$total = 0;
if(!empty($_SESSION['cart'])):
?>
<div class="cart-container">
    <div class="cart-items">
        <?php foreach($_SESSION['cart'] as $id => $qty):
            $p = $conn->query("SELECT * FROM product WHERE product_id = $id")->fetch_assoc();
            $sub = $p['price'] * $qty;
            $total += $sub;
        ?>
        <div class="cart-row" data-id="<?php echo $id; ?>" data-price="<?php echo $p['price']; ?>">
            <div class="cart-img">
                <img src="images/<?php echo strtolower(str_replace(' ','_',$p['product_name'])); ?>.jpg" onerror="this.src='images/default.jpeg'">
            </div>
            <div class="cart-left">
                <div class="cart-name"><?php echo $p['product_name']; ?></div>
                <div class="cart-price">RM <?php echo number_format($p['price'],2); ?></div>
            </div>
            <div class="cart-middle">
                <button class="qty-btn" onclick="updateCart(<?php echo $id; ?>,'minus')">-</button>
                <input class="qty-input" type="number" min="1" value="<?php echo $qty; ?>" onchange="setQty(<?php echo $id; ?>, this.value)">
                <button class="qty-btn plus" onclick="updateCart(<?php echo $id; ?>,'plus')">+</button>
            </div>
            <div class="cart-right">
                <span class="subtotal">RM <?php echo number_format($sub,2); ?></span>
            </div>
            <div class="cart-remove">
                <button class="remove-btn" onclick="updateCart(<?php echo $id; ?>,'remove')">Remove</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="cart-summary">
        <h3>Order Summary</h3>
        <p>Total Items: <span id="items"><?php echo array_sum($_SESSION['cart']); ?></span></p>
        <h2 id="total">RM <?php echo number_format($total,2); ?></h2>
        <form action="checkout_process.php" method="POST">
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            <button class="checkout-btn">Checkout</button>
        </form>
    </div>
</div>
<?php else: ?>
    <div class="cart-container">
        <p>Your cart is empty 😢 <a href="homepage.php">Go shopping</a></p>
    </div>
<?php endif; ?>

<script>
function setQty(id, value){
    value = parseInt(value);
    if(value <= 0) { updateCart(id, 'remove'); return; }
    fetch(`manage_cart.php?id=${id}&action=set&qty=${value}`)
    .then(res => res.json())
    .then(data => { if(data.status === "success") updateUI(data.cart); });
}

function updateCart(id, action){
    fetch(`manage_cart.php?id=${id}&action=${action}`)
    .then(res => res.json())
    .then(data => { if(data.status === "success") updateUI(data.cart); });
}

function updateUI(cart){
    let total = 0, totalItems = 0;
    const rows = document.querySelectorAll('.cart-row');
    
    rows.forEach(row => {
        let id = row.dataset.id;
        if(!cart[id]){ row.remove(); return; }
        
        let qty = cart[id];
        let price = parseFloat(row.dataset.price);
        row.querySelector('.qty-input').value = qty;
        let sub = qty * price;
        row.querySelector('.subtotal').innerText = "RM " + sub.toFixed(2);
        total += sub;
        totalItems += qty;
    });

    document.getElementById("total").innerText = "RM " + total.toFixed(2);
    document.getElementById("items").innerText = totalItems;

    if(Object.keys(cart).length === 0){
        document.querySelector('.cart-container').innerHTML = "<p>Your cart is empty 😢 <a href='homepage.php'>Go shopping</a></p>";
    }
}
</script>
</body>
</html>