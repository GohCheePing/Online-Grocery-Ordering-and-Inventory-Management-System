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

    <!-- SUMMARY -->
    <div class="cart-summary">

        <h3>Order Summary</h3>

        <p>Total Items:
            <span id="items"><?php echo array_sum($_SESSION['cart']); ?></span>
        </p>

        <h4>Subtotal: RM <span id="subtotal"><?php echo number_format($total,2); ?></span></h4>

        <h3>Discount: -RM <span id="discount">0.00</span></h3>

        <h2 id="total">RM <?php echo number_format($total,2); ?></h2>

        <!-- PROMO -->
        <div class="promo-box">
            <div class="promo-title">🎁 Promo Code</div>

            <div class="promo-input-row">
                <input type="text" id="promo_code" placeholder="SAVE10">
                <button type="button" onclick="applyPromo()">Apply</button>
            </div>

            <div id="promo_msg" class="promo-msg"></div>
        </div>

        <!-- CHECKOUT -->
        <form action="checkout_process.php" method="POST" onsubmit="return attachPromo()">

            <input type="hidden" name="promo_code" id="promo_hidden">
            <input type="hidden" name="discount_amount" id="discount_hidden">

            <button class="checkout-btn">
                Checkout
            </button>

        </form>

    </div>

</div>

<?php else: ?>
    <div class="cart-container">
        <p>Your cart is empty 😢</p>
    </div>
<?php endif; ?>

<script>
let currentPromo = null;
let discountAmount = 0;
let subtotal = <?php echo $total; ?>;

/* ================= CART ================= */

function updateCart(id, action){
    fetch('manage_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id, action })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === "success"){
            location.reload(); // keep simple + stable
        }
    });
}

function setQty(id, value){
    value = parseInt(value);
    if(value <= 0){
        updateCart(id,'remove');
        return;
    }

    fetch('manage_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id, action:'set', qty:value })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === "success"){
            location.reload();
        }
    });
}

/* ================= PROMO (SHOPEE STYLE) ================= */

function applyPromo(){

    const code = document.getElementById("promo_code").value.trim();

    fetch("promo_check.php", {
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({code, subtotal})
    })
    .then(res => res.json())
    .then(data => {

        if(data.status === "success"){

            currentPromo = data.code;
            discountAmount = data.discount;

            document.getElementById("promo_msg").innerHTML =
                "✔ Saved RM " + discountAmount.toFixed(2);

            document.getElementById("promo_msg").style.color = "green";

            updateFinal();

        } else {

            currentPromo = null;
            discountAmount = 0;

            document.getElementById("promo_msg").innerHTML =
                "✖ " + data.msg;

            document.getElementById("promo_msg").style.color = "red";

            updateFinal();
        }
    });
}

function updateFinal(){

    let final = subtotal - discountAmount;
    if(final < 0) final = 0;

    document.getElementById("discount").innerText = discountAmount.toFixed(2);
    document.getElementById("total").innerText = "RM " + final.toFixed(2);
}

/* pass to checkout */
function attachPromo(){
    document.getElementById("promo_hidden").value = currentPromo || "";
    document.getElementById("discount_hidden").value = discountAmount || 0;
    return true;
}
</script>

</body>
</html>