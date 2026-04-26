<?php
session_start();
require 'db.php';

// Search
$search_query = "";
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $q = $conn->real_escape_string($_GET['query']);
    $search_query = " WHERE product_name LIKE '%$q%'";
}

// Categories
$categories_res = $conn->query("SELECT * FROM category");

// Products
$products_res = $conn->query("SELECT * FROM product" . $search_query);
$products = [];
while($row = $products_res->fetch_assoc()) { 
    $products[] = $row; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FreshMart</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="topbar">
    <a href="admin/login.php">Admin</a>
    <a href="orders.php">My Orders</a>

    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a> | <a href="register.php">Register</a>
    <?php endif; ?>
</div>

<div class="header">
    <div class="logo">
        <a href="homepage.php" style="text-decoration:none; font-size:24px; font-weight:bold; color:var(--main-green);">
            FRESHMART
        </a>
    </div>

    <form class="search" method="GET">
        <input type="text" name="query" placeholder="Search..." value="<?php echo $_GET['query'] ?? ''; ?>">
        <button>Search</button>
    </form>

    <a href="cart.php" class="cart">
    <i class="fa-solid fa-cart-shopping"></i>
    <span>Cart</span>
    (<span id="count"><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></span>)
    </a>
</div>

<div class="categories">
    <div class="cat" onclick="filter('all')">ALL</div>
    <?php while($cat = $categories_res->fetch_assoc()): ?>
        <div class="cat" onclick="filter('<?php echo $cat['category_id']; ?>')">
            <?php echo strtoupper($cat['category_name']); ?>
        </div>
    <?php endwhile; ?>
</div>

<div class="banner">
    <h1>FRESHMART</h1>
    <p>Premium Grocery Delivery</p>
</div>

<div style="padding:40px 10%;">
    <h2>Featured Products</h2>

    <div id="grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:25px;"></div>
</div>

<!-- Toast -->
<div id="toast" style="
    position:fixed;
    bottom:30px;
    right:30px;
    background:#2ecc71;
    color:white;
    padding:12px 18px;
    border-radius:8px;
    display:none;
    z-index:9999;
"></div>

<script>
const allProducts = <?php echo json_encode($products); ?>;

/* TOAST (fixed) */
function showToast(msg, color="#2ecc71") {
    const toast = document.getElementById("toast");

    toast.innerText = msg;
    toast.style.background = color;
    toast.style.display = "block";

    clearTimeout(window.toastTimer);

    window.toastTimer = setTimeout(() => {
        toast.style.display = "none";
    }, 1500);
}

/* RENDER PRODUCTS */
function renderProducts(list) {
    const grid = document.getElementById("grid");
    let html = "";

    list.forEach(p => {
        const isOut = parseInt(p.stock_quantity) <= 0;
        let fileName = p.product_name.toLowerCase().replace(/\s+/g,'_');

        html += `
        <div style="
            background:white;
            padding:20px;
            border-radius:12px;
            text-align:center;
            border:1px solid #eee;
            transition:0.25s ease;
            cursor:pointer;
        "
        onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.08)'"
        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
        >

            <img src="images/${fileName}.jpg"
                onerror="this.src='images/default.jpeg'"
                style="width:100%; height:150px; object-fit:contain;">

            <h3>${p.product_name}</h3>

            <p style="color:green; font-weight:bold;">RM ${parseFloat(p.price).toFixed(2)}</p>

            <p style="font-size:12px;">Stock: ${p.stock_quantity}</p>

            <button id="btn-${p.product_id}"
                onclick="addToCart(${p.product_id})"
                ${isOut ? 'disabled' : ''}
                style="
                    width:100%;
                    padding:10px;
                    border:none;
                    border-radius:8px;
                    background:linear-gradient(135deg,#2ecc71,#27ae60);
                    color:white;
                    cursor:pointer;
                    transition:0.25s ease;
                "
                onmouseover="this.style.transform='scale(1.03)'"
                onmouseout="this.style.transform='scale(1)'"
            >
                ${isOut ? 'Out of Stock' : 'Add to Cart'}
            </button>

        </div>`;
    });

    grid.innerHTML = html;
}

/* FILTER */
function filter(id){
    if(id === 'all') renderProducts(allProducts);
    else renderProducts(allProducts.filter(p => p.category_id == id));
}

/* ADD TO CART */
function addToCart(id){
    const btn = document.getElementById("btn-" + id);

    btn.innerText = "Adding...";
    btn.disabled = true;

    fetch("manage_cart.php?id=" + id)
    .then(r => r.text())
    .then(res => {
        if(res.trim() === "success"){
            document.getElementById("count").innerText++;

            showToast("✔ Added to cart");
        } else {
            showToast("Out of stock", "#e74c3c");
        }
    })
    .finally(() => {
        btn.innerText = "Add to Cart";
        btn.disabled = false;
    });
}

renderProducts(allProducts);
</script>

<<<<<<< HEAD
        /**
         * Asynchronous Cart Update (AJAX):
         * Sends data to manage_cart.php without refreshing the current page.
         */
        function addToCart(id) {
            fetch('manage_cart.php?id=' + id)
            .then(res => res.json())
            .then(data => {

                if(data.status === "success"){

                    let total = 0;
                    for(let k in data.cart){
                        total += data.cart[k];
                    }

                    document.getElementById("count").innerText = total;

                    showToast("Added to cart");
                } else {
                    showToast("Out of stock");
                }
            });
        }

        // Execute initial render
        renderProducts(allProducts);
    </script>
    <div id="toast"></div>

    <script>
    function showToast(msg){
        let t = document.getElementById("toast");
        t.innerText = msg;
        t.style.display = "block";

        setTimeout(()=>{
            t.style.display = "none";
        },1500);
    }
    </script>
</body>
</html>