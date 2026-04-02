<?php
// 1. Initialize session and database connection
session_start();
require 'db.php';

/**
 * Search Logic:
 * Check if the user has entered a search term.
 * Use real_escape_string to prevent basic SQL Injection.
 */
$search_query = "";
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $q = $conn->real_escape_string($_GET['query']);
    $search_query = " WHERE product_name LIKE '%$q%'";
}

// Fetch categories for the navigation bar
$categories_res = $conn->query("SELECT * FROM category");

// Fetch products based on the search query (if any)
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
    <title>FreshMart | Online Grocery</title>
    <style>
        /* [CSS Styles: Handling the Grid Layout and Theme Colors] */
        :root { --main-green: #2ecc71; --dark-green: #27ae60; --text: #333; }
        /* ... (styles omitted for brevity) ... */
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
        /**
         * PHP to JavaScript Bridge:
         * Convert the PHP array into a JSON object for client-side processing.
         */
        const allProducts = <?php echo json_encode($products); ?>;

        /**
         * Render Function:
         * Dynamically generates HTML cards for each product.
         */
        function renderProducts(list) {
            const grid = document.getElementById("grid");
            let html = "";
            list.forEach(p => {
                const isOut = parseInt(p.stock_quantity) <= 0;
                // Normalize file name: Lowecase and replace spaces with underscores
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

        /**
         * Category Filter:
         * Filters the product list based on Category ID without reloading the page.
         */
        function filter(id) {
            id === 'all' ? renderProducts(allProducts) : renderProducts(allProducts.filter(x => x.category_id == id));
        }

        /**
         * AJAX Add to Cart:
         * Communicates with manage_cart.php in the background to update the session.
         */
        function addToCart(id) {
            fetch('manage_cart.php?id=' + id)
            .then(res => res.text())
            .then(data => {
                if(data.trim() === "success") {
                    // Update the cart counter UI immediately
                    let cnt = document.getElementById("count");
                    cnt.innerText = parseInt(cnt.innerText) + 1;
                    alert("Added to cart!");
                }
            });
        }

        // Initial render on page load
        renderProducts(allProducts);
    </script>
</body>
</html>