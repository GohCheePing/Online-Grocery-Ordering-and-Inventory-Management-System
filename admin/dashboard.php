<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin = $_SESSION['admin_full_name'] ?? "Admin";

$product = $conn->query("SELECT COUNT(*) AS total FROM product")->fetch_assoc()['total'];
$order   = $conn->query("SELECT COUNT(*) AS total FROM `order`")->fetch_assoc()['total'];
$user    = $conn->query("SELECT COUNT(*) AS total FROM customer")->fetch_assoc()['total'];

$row = $conn->query("SELECT SUM(total_amount) AS total FROM `order`")->fetch_assoc();
$rev = $row['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<style>
*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    min-height:100vh;
    padding:40px;

    background:
        radial-gradient(circle at top left,#8ec5fc,transparent 32%),
        radial-gradient(circle at bottom right,#e0c3fc,transparent 32%),
        linear-gradient(135deg,#dfe9f3,#ffffff);
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

h2{
    font-size:32px;
    color:#111;
}

.card-container{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

.card{
    padding:25px;
    border-radius:28px;
    background:rgba(255,255,255,0.28);
    backdrop-filter:blur(22px) saturate(180%);
    -webkit-backdrop-filter:blur(22px) saturate(180%);
    border:1px solid rgba(255,255,255,0.4);
    box-shadow:0 15px 35px rgba(0,0,0,0.12);
    transition:0.3s ease;
}

.card:hover{
    transform:translateY(-6px);
    box-shadow:0 25px 50px rgba(0,0,0,0.18);
}

.card h3{
    margin:0 0 10px;
    color:#222;
}

.card p{
    font-size:30px;
    font-weight:bold;
    margin:0;
}

.menu{
    margin-top:35px;
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}

.menu a,
.logout{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    position:relative;
    overflow:hidden;

    padding:16px 28px;
    border-radius:22px;

    text-decoration:none;
    color:rgba(0,0,0,0.78);
    font-weight:700;
    letter-spacing:0.4px;

    background:
        linear-gradient(
            135deg,
            rgba(255,255,255,0.32),
            rgba(255,255,255,0.08)
        );

    backdrop-filter:blur(24px) saturate(190%);
    -webkit-backdrop-filter:blur(24px) saturate(190%);

    border:1px solid rgba(255,255,255,0.45);

    box-shadow:
        inset 0 1px 1px rgba(255,255,255,0.5),
        inset 0 -1px 1px rgba(255,255,255,0.1),
        0 12px 30px rgba(0,0,0,0.18);

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        background 0.25s ease;
}

.logout{
    background:
        linear-gradient(
            135deg,
            rgba(255, 80, 80, 0.89),
            rgba(255, 80, 80, 0.88)
        );
}

.menu a::before,
.logout::before{
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
    transition:opacity 0.2s ease;
}

.menu a::after,
.logout::after{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:50%;

    background:
        linear-gradient(
            to bottom,
            rgba(255,255,255,0.45),
            transparent
        );

    pointer-events:none;
}

.menu a:hover::before,
.logout:hover::before{
    opacity:1;
}

.menu a:hover,
.logout:hover{
    transform:translateY(-7px) scale(1.06);

    background:
        linear-gradient(
            135deg,
            rgba(255,255,255,0.45),
            rgba(255,255,255,0.14)
        );

    box-shadow:
        inset 0 1px 1px rgba(255,255,255,0.6),
        0 22px 50px rgba(0,0,0,0.25);
}

.logout:hover{
    background:
        linear-gradient(
            135deg,
            rgba(255,59,48,0.45),
            rgba(255,149,0,0.18)
        );
}

.menu a span,
.logout span{
    position:relative;
    z-index:3;
}

.menu a:active,
.logout:active{
    transform:scale(0.95);
}
</style>
</head>

<body>

<div class="header">
    <h2>Welcome <?php echo htmlspecialchars($admin); ?></h2>
    <a class="logout glow-btn" href="logout.php">
        <span>Logout</span>
    </a>
</div>

<div class="card-container">
    <div class="card">
        <h3>Total Products</h3>
        <p><?php echo $product; ?></p>
    </div>

    <div class="card">
        <h3>Total Orders</h3>
        <p><?php echo $order; ?></p>
    </div>

    <div class="card">
        <h3>Total Customers</h3>
        <p><?php echo $user; ?></p>
    </div>

    <div class="card">
        <h3>Revenue</h3>
        <p>RM <?php echo number_format($rev, 2); ?></p>
    </div>
</div>

<div class="menu">
    <a class="glow-btn" href="manage_products.php">
        <span>Manage Products</span>
    </a>

    <a class="glow-btn" href="add_product.php">
        <span>Add Product</span>
    </a>

    <a class="glow-btn" href="manage_orders.php">
        <span>Manage Orders</span>
    </a>
</div>

<script>
const buttons = document.querySelectorAll(".glow-btn");

buttons.forEach((btn) => {
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