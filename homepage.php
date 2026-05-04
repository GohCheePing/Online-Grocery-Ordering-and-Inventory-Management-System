<?php
session_start();
require 'db.php';

$search_query = "";
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $q = $conn->real_escape_string($_GET['query']);
    $search_query = " WHERE product_name LIKE '%$q%'";
}

$categories_res = $conn->query("SELECT * FROM category");
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
    <title>FreshMart | Online Grocery Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <a href="admin/login.php">Admin Dashboard</a>
        <a href="orders.php">My Orders</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
    </div>

    <div class="header">
        <div class="logo"><a href="homepage.php" style="text-decoration:none; color:var(--main-green); font-size:24px; font-weight:bold;">FRESHMART</a></div>
        <form class="search" action="homepage.php" method="GET">
            <input type="text" name="query" placeholder="Search fresh groceries..." value="<?php echo $_GET['query'] ?? ''; ?>">
            <button type="submit">Search</button>
        </form>
        <a href="cart.php" class="cart" style="text-decoration:none;">
            🛒 My Cart ( <span id="count"><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></span> )
        </a>
        <a href="user_dashboard.php">My Dashboard</a>
    </div>

    <div class="categories">
        <div class="cat" onclick="filter('all')">ALL</div>
        <?php while($cat = $categories_res->fetch_assoc()): ?>
            <div class="cat" onclick="filter('<?php echo $cat['category_id']; ?>')"><?php echo strtoupper($cat['category_name']); ?></div>
        <?php endwhile; ?>
    </div>

    <div class="banner">
        <h1>FRESHMART</h1>
        <p>Premium Quality Groceries Delivered Fast</p>
    </div>

    <div style="padding: 40px 10%;">
        <h2 style="border-left: 5px solid var(--main-green); padding-left: 15px; margin-bottom: 25px;">Featured Products</h2>
        <div id="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px;"></div>
    </div>

    <div id="toast"></div>

    <script>
        const allProducts = <?php echo json_encode($products); ?>;

        function renderProducts(list) {
    const grid = document.getElementById("grid");
    let html = "";

    list.forEach(p => {
        const isOut = parseInt(p.stock_quantity) <= 0;
        let img = p.image ? p.image : 'default.jpeg';
        html += `
            <div class="product-card" style="background: white; border-radius: 15px; padding: 20px; text-align: center; border: 1px solid #eee;">
                
                <img src="images/${img}" 
                     onerror="this.onerror=null; this.src='images/default.jpeg';"
                     style="width: 100%; height: 160px; object-fit: contain; margin-bottom: 15px;">

                <h3 style="margin: 10px 0;">${p.product_name}</h3>

                <div style="color: var(--main-green); font-size: 20px; font-weight: 800; margin-bottom: 15px;">
                    RM ${parseFloat(p.price).toFixed(2)}
                </div>

                <div style="font-size:12px; color:#888; margin-bottom:10px;">
                    Stock: ${p.stock_quantity}
                </div>

                <button onclick="addToCart(${p.product_id})" ${isOut ? 'disabled' : ''} 
                        style="background: var(--main-green); color: white; border: none; padding: 10px 0; width: 100%; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    ${isOut ? 'Out of Stock' : 'Add to Cart'}
                </button>

            </div>`;
    });

    grid.innerHTML = html || "<p>No products found.</p>";
}

        function filter(id) {
            id === 'all' ? renderProducts(allProducts) : renderProducts(allProducts.filter(x => x.category_id == id));
        }

        function addToCart(id) {
            fetch('manage_cart.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ id, action: 'add' })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === "success"){
                    updateCartCount(data.totalItems);
                    showToast("Added to cart");
                } else {
                    showToast("Out of stock");
                }
            });
        }

        function showToast(msg){
            let t = document.getElementById("toast");
            t.innerText = msg;
            t.style.display = "block";
            setTimeout(() => { t.style.display = "none"; }, 1500);
        }

        renderProducts(allProducts);

        function updateCartCount(totalItems){
            document.getElementById("count").innerText = totalItems;
        }
    </script>
</body>
</html>