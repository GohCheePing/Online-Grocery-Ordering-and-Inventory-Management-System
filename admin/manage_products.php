<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM product
        WHERE product_id=?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}

$sql = "
    SELECT
    product.*,
    category.category_name

    FROM product

    LEFT JOIN category
    ON product.category_id = category.category_id

    ORDER BY product.product_id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>

<title>Manage Products</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}

body{

    min-height:100vh;

    padding:40px;

    background:
        radial-gradient(circle at top left,#8ec5fc,transparent 30%),
        radial-gradient(circle at bottom right,#e0c3fc,transparent 30%),
        linear-gradient(135deg,#dfe9f3,#ffffff);
}

.header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:30px;
}

h1{

    color:rgba(0,0,0,0.82);

    font-size:34px;
}

.top-actions{

    display:flex;

    gap:14px;
}

/* APPLE LIQUID GLASS BUTTON */

.btn,
.action a{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    position:relative;

    overflow:hidden;

    padding:13px 22px;

    border-radius:22px;

    text-decoration:none;

    font-weight:700;

    color:rgba(0,0,0,0.78);

    background:
        linear-gradient(
            135deg,
            rgba(255,255,255,0.34),
            rgba(255,255,255,0.12)
        );

    backdrop-filter:blur(24px) saturate(190%);

    -webkit-backdrop-filter:blur(24px) saturate(190%);

    border:1px solid rgba(255,255,255,0.45);

    box-shadow:
        inset 0 1px 1px rgba(255,255,255,0.45),
        0 12px 30px rgba(0,0,0,0.16);

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        background 0.25s ease;
}

.btn::before,
.action a::before{

    content:"";

    position:absolute;

    inset:0;

    background:
        radial-gradient(
            circle at var(--x, 50%) var(--y, 50%),
            rgba(255,255,255,0.95),
            transparent 36%
        );

    opacity:0;

    transition:0.2s ease;
}

.btn:hover::before,
.action a:hover::before{

    opacity:1;
}

.btn:hover,
.action a:hover{

    transform:
        translateY(-5px)
        scale(1.04);

    box-shadow:
        inset 0 1px 1px rgba(255,255,255,0.6),
        0 22px 45px rgba(0,0,0,0.22);
}

/* BLUE BUTTON */
.btn{

    color:white;

    background:
        linear-gradient(
            135deg,
            rgba(0,122,255,0.65),
            rgba(88,86,214,0.45)
        );
}

/* EDIT BUTTON */
.edit{

    color:white !important;

    background:
        linear-gradient(
            135deg,
            rgba(52,199,89,0.72),
            rgba(48,209,88,0.42)
        ) !important;

    box-shadow:
        0 12px 30px rgba(52,199,89,0.22);
}

/* DELETE BUTTON */
.delete{

    color:white !important;

    background:
        linear-gradient(
            135deg,
            rgba(255,59,48,0.82),
            rgba(255,149,0,0.42)
        ) !important;

    box-shadow:
        0 12px 30px rgba(255,59,48,0.22);
}

/* HOVER */
.edit:hover{

    box-shadow:
        0 22px 45px rgba(52,199,89,0.35);
}

.delete:hover{

    box-shadow:
        0 22px 45px rgba(255,59,48,0.35);
}

.glass-card{

    padding:28px;

    border-radius:34px;

    background:rgba(255,255,255,0.28);

    backdrop-filter:blur(25px) saturate(180%);

    -webkit-backdrop-filter:blur(25px) saturate(180%);

    border:1px solid rgba(255,255,255,0.38);

    box-shadow:0 20px 50px rgba(0,0,0,0.15);

    overflow-x:auto;
}

table{

    width:100%;

    border-collapse:collapse;
}

th,
td{

    padding:18px;

    text-align:left;

    border-bottom:1px solid rgba(255,255,255,0.35);
}

th{

    color:#111;

    font-size:15px;
}

td{

    color:#333;

    font-weight:500;
}

.product-img{

    width:80px;

    height:80px;

    object-fit:cover;

    border-radius:20px;

    background:rgba(255,255,255,0.35);

    box-shadow:0 10px 25px rgba(0,0,0,0.12);
}

.no-img{

    width:80px;

    height:80px;

    border-radius:20px;

    display:flex;

    align-items:center;

    justify-content:center;

    background:rgba(255,255,255,0.35);

    color:#666;

    font-size:12px;
}

.action{

    display:flex;

    gap:10px;
}

</style>

</head>

<body>

<div class="header">

    <h1>Manage Products</h1>

    <div class="top-actions">

        <a class="btn" href="add_product.php">
            Add Product
        </a>

        <a class="btn" href="dashboard.php">
            Dashboard
        </a>

    </div>

</div>

<div class="glass-card">

<table>

<tr>

    <th>ID</th>

    <th>Image</th>

    <th>Product</th>

    <th>Category</th>

    <th>Price</th>

    <th>Stock</th>

    <th>Min Stock</th>

    <th>Action</th>

</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

    <td>
        <?php echo $row['product_id']; ?>
    </td>

    <td>

        <?php if(!empty($row['image'])): ?>

            <img
                class="product-img"
                src="../images/<?php echo htmlspecialchars($row['image']); ?>"
            >

        <?php else: ?>

            <div class="no-img">
                No Image
            </div>

        <?php endif; ?>

    </td>

    <td>
        <?php echo htmlspecialchars($row['product_name']); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($row['category_name'] ?? 'No Category'); ?>
    </td>

    <td>
        RM <?php echo number_format($row['price'], 2); ?>
    </td>

    <td>
        <?php echo $row['stock_quantity']; ?>
    </td>

    <td>
        <?php echo $row['min_stock_level']; ?>
    </td>

    <td>

        <div class="action">

            <a
                class="edit"
                href="edit_product.php?id=<?php echo $row['product_id']; ?>"
            >
                Edit
            </a>

            <a
                class="delete"
                href="manage_products.php?delete=<?php echo $row['product_id']; ?>"
                onclick="return confirm('Delete this product?');"
            >
                Delete
            </a>

        </div>

    </td>

</tr>

<?php endwhile; ?>

</table>

</div>

<script>

const glassButtons = document.querySelectorAll(".btn, .action a");

glassButtons.forEach((btn) => {

    btn.addEventListener("mousemove", function(e){

        const rect = btn.getBoundingClientRect();

        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        btn.style.setProperty("--x", x + "px");
        btn.style.setProperty("--y", y + "px");
    });
});

</script>

</body>
</html>