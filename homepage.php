<?php
session_start();
require 'db.php';

// 搜索逻辑
$search_query = "";
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $q = $conn->real_escape_string($_GET['query']);
    $search_query = " WHERE product_name LIKE '%$q%'";
}

$categories_res = $conn->query("SELECT * FROM category");
$products_res = $conn->query("SELECT * FROM product" . $search_query);
$products = [];
while($row = $products_res->fetch_assoc()) { $products[] = $row; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshMart | Online Grocery</title>
    <style>
        /* === 1. 基础重置 === */
        :root { --main-green: #2ecc71; --dark-green: #27ae60; --text: #333; }
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: #f4f7f6; color: var(--text); }

        /* === 2. 顶部工具栏 (Top Bar) === */
        .top-bar { background: #fff; padding: 10px 10%; display: flex; justify-content: flex-end; gap: 20px; font-size: 13px; border-bottom: 1px solid #eee; }
        .top-bar a { color: #666; text-decoration: none; }
        .top-bar a:hover { color: var(--main-green); }

        /* === 3. 主 Header 区域 === */
        .main-header { background: #fff; padding: 20px 10%; display: flex; align-items: center; justify-content: space-between; gap: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .logo-text { font-size: 28px; font-weight: 800; color: var(--main-green); text-decoration: none; letter-spacing: -1px; }
        
        .search-container { flex: 1; max-width: 600px; display: flex; background: #f0f2f1; border-radius: 30px; overflow: hidden; border: 2px solid transparent; transition: 0.3s; }
        .search-container:focus-within { border-color: var(--main-green); background: #fff; }
        .search-container input { flex: 1; border: none; padding: 12px 20px; outline: none; background: transparent; font-size: 14px; }
        .search-container button { background: var(--main-green); color: white; border: none; padding: 0 25px; cursor: pointer; font-weight: 600; }

        .cart-btn { display: flex; align-items: center; gap: 8px; color: var(--main-green); font-weight: 700; cursor: pointer; text-decoration: none; }

        /* === 4. 绿色导航栏 (Nav) === */
        .nav-bar { background: var(--main-green); padding: 12px 10%; display: flex; justify-content: center; gap: 40px; }
        .nav-item { color: white; text-decoration: none; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .nav-item:hover { color: #f1c40f; transform: translateY(-2px); }

        /* === 5. Banner 区域 === */
        .hero-banner { background: linear-gradient(rgba(46,204,113,0.8), rgba(39,174,96,0.9)), url('images/banner_bg.jpg'); height: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; text-align: center; }
        .hero-banner h1 { margin: 0; font-size: 42px; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }

        /* === 6. 产品网格 (Grid) === */
        .container { padding: 40px 10%; }
        .section-title { font-size: 24px; margin-bottom: 25px; border-left: 5px solid var(--main-green); padding-left: 15px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; }
        
        .product-card { background: white; border-radius: 15px; padding: 20px; text-align: center; transition: 0.3s; border: 1px solid #eee; position: relative; }
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .product-card img { width: 100%; height: 160px; object-fit: contain; margin-bottom: 15px; }
        .product-card h3 { margin: 10px 0; font-size: 18px; }
        .product-card .price { color: var(--main-green); font-size: 20px; font-weight: 800; margin-bottom: 15px; }
        
        .add-btn { background: var(--main-green); color: white; border: none; padding: 10px 0; width: 100%; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .add-btn:hover { background: var(--dark-green); }
        .add-btn:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>

    <div class="top-bar">
        <a href="admin/login.php">Admin Dashboard</a>
        <a href="orders.php">My Orders</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
    </div>

    <div class="main-header">
        <a href="homepage.php" class="logo-text">FRESHMART</a>
        
        <form class="search-container" action="homepage.php" method="GET">
            <input type="text" name="query" placeholder="Search fresh groceries..." value="<?php echo $_GET['query'] ?? ''; ?>">
            <button type="submit">Search</button>
        </form>

        <a href="cart.php" class="cart-btn">
            🛒 My Cart ( <span id="count"><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></span> )
        </a>
    </div>

    <div class="nav-bar">
        <div class="nav-item" onclick="filter('all')">🏢 ALL</div>
        <?php while($cat = $categories_res->fetch_assoc()): ?>
            <div class="nav-item" onclick="filter('<?php echo $cat['category_id']; ?>')">
                <?php echo strtoupper($cat['category_name']); ?>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="hero-banner">
        <h1>FRESHMART</h1>
        <p>Premium Quality Groceries Delivered Fast</p>
    </div>

    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        <div class="product-grid" id="grid">
            </div>
    </div>

    <script>
        const allProducts = <?php echo json_encode($products); ?>;

        function renderProducts(list) {
            const grid = document.getElementById("grid");
            let html = "";
            list.forEach(p => {
                const isOut = parseInt(p.stock_quantity) <= 0;
                let fileName = p.product_name.toLowerCase().replace(/\s+/g, '_');
                html += `
                    <div class="product-card">
                        <img src="images/${fileName}.jpg" onerror="this.onerror=null; this.src='images/default.jpg';">
                        <h3>${p.product_name}</h3>
                        <div class="price">RM ${parseFloat(p.price).toFixed(2)}</div>
                        <div style="font-size:12px; color:#888; margin-bottom:10px;">Stock: ${p.stock_quantity}</div>
                        <button class="add-btn" ${isOut ? 'disabled' : ''} onclick="addToCart(${p.product_id})">
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
            fetch('manage_cart.php?id=' + id)
            .then(res => res.text())
            .then(data => {
                if(data.trim() === "success") {
                    let cnt = document.getElementById("count");
                    cnt.innerText = parseInt(cnt.innerText) + 1;
                    alert("Added to cart!");
                }
            });
        }

        renderProducts(allProducts);
    </script>
</body>
</html>