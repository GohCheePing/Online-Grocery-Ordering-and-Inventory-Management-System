<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =======================
   GET USER INFO (FIXED FIELD)
======================= */
$stmt = $conn->prepare("SELECT name, email FROM customer WHERE customer_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$name = $user['name'] ?? 'User';
$email = $user['email'] ?? '';

/* =======================
   ORDER STATS
======================= */
function getCount($conn, $user_id, $status = null) {
    if ($status) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM orders WHERE customer_id=? AND order_status=?");
        $stmt->bind_param("is", $user_id, $status);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM orders WHERE customer_id=?");
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
}

$totalOrders = getCount($conn, $user_id);
$pending = getCount($conn, $user_id, 'Pending');
$completed = getCount($conn, $user_id, 'Completed');
$cancelled = getCount($conn, $user_id, 'Cancelled');

/* =======================
   TOTAL SPENDING (ONLY COMPLETED)
======================= */
$stmt = $conn->prepare("SELECT SUM(total_amount) AS total FROM orders WHERE customer_id=? AND order_status='Completed'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$spending = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

/* =======================
   USER LEVEL SYSTEM
======================= */
if ($spending > 500) {
    $level = "Gold 🥇";
} elseif ($spending > 200) {
    $level = "Silver 🥈";
} else {
    $level = "Basic 🥉";
}

/* =======================
   RECENT ORDERS
======================= */
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id=? ORDER BY order_id DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>User Dashboard</title>

<style>
body{
    margin:0;
    font-family:Segoe UI;
    background:#f4f6f9;
    padding:30px;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.level{
    padding:10px 15px;
    background:#111;
    color:#fff;
    border-radius:10px;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
    margin-top:20px;
}

.card{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

.card h3{
    margin:0;
    font-size:14px;
    color:#666;
}

.card p{
    font-size:26px;
    margin:10px 0 0;
    font-weight:bold;
}

.menu{
    margin-top:25px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.menu a{
    padding:12px 18px;
    background:#2ecc71;
    color:white;
    text-decoration:none;
    border-radius:10px;
}

table{
    width:100%;
    margin-top:25px;
    border-collapse:collapse;
    background:white;
    border-radius:10px;
    overflow:hidden;
}

th,td{
    padding:12px;
    text-align:left;
    border-bottom:1px solid #eee;
}

th{
    background:#2ecc71;
    color:white;
}
</style>
</head>

<body>

<div class="header">
    <div>
        <h2>Welcome, <?php echo htmlspecialchars($name); ?> 👋</h2>
        <small><?php echo htmlspecialchars($email); ?></small>
    </div>

    <div class="level">
        Level: <?php echo $level; ?>
    </div>
</div>

<!-- STATS -->
<div class="grid">
    <div class="card">
        <h3>Total Orders</h3>
        <p><?php echo $totalOrders; ?></p>
    </div>

    <div class="card">
        <h3>Pending</h3>
        <p><?php echo $pending; ?></p>
    </div>

    <div class="card">
        <h3>Completed</h3>
        <p><?php echo $completed; ?></p>
    </div>

    <div class="card">
        <h3>Total Spending</h3>
        <p>RM <?php echo number_format($spending,2); ?></p>
    </div>
</div>

<!-- MENU -->
<div class="menu">
    <a href="homepage.php">🛒 Shop</a>
    <a href="cart.php">Cart</a>
    <a href="orders.php">My Orders</a>
</div>

<!-- RECENT ORDERS -->
<h3 style="margin-top:30px;">Recent Orders</h3>

<table>
<tr>
    <th>Order ID</th>
    <th>Total</th>
    <th>Status</th>
</tr>

<?php while($o = $orders->fetch_assoc()): ?>
<tr>
    <td>#<?php echo $o['order_id']; ?></td>
    <td>RM <?php echo number_format($o['total_amount'],2); ?></td>
    <td><?php echo $o['order_status']; ?></td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>