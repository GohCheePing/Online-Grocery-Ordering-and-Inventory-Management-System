<?php
session_start();
$conn = new mysqli("localhost", "root", "", "freshmart");

// 获取所有分类 (对应 Scope 中的 Category features) 
$categories = $conn->query("SELECT * FROM category");

// 获取产品数据并关联分类名称
$sql = "SELECT p.*, c.category_name FROM product p 
        LEFT JOIN category c ON p.category_id = c.category_id";
$result = $conn->query($sql);
?>

<div class="categories">
    <div class="cat" onclick="filter('all')">🏢 ALL</div>
    <?php while($cat = $categories->fetch_assoc()): ?>
        <div class="cat" onclick="filter('<?php echo $cat['category_id']; ?>')">
            <?php echo strtoupper($cat['category_name']); ?>
        </div>
    <?php endwhile; ?>
</div>

<div class="grid">
    <?php while($p = $result->fetch_assoc()): ?>
        <div class="card">
            <h4><?php echo $p['product_name']; ?></h4>
            <p class="price">RM <?php echo number_format($p['price'], 2); ?></p>
            <p class="stock-tag">In Stock: <?php echo $p['stock_quantity']; ?></p>
            
            <button <?php echo ($p['stock_quantity'] <= 0) ? 'disabled' : ''; ?> 
                    onclick="add(<?php echo $p['product_id']; ?>)">
                <?php echo ($p['stock_quantity'] <= 0) ? 'Out of Stock' : 'Add to Cart'; ?>
            </button>
        </div>
    <?php endwhile; ?>
</div>