<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* USER INFO */
$stmt = $conn->prepare("SELECT name, email FROM customer WHERE customer_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$name = $user['name'] ?? 'User';
$email = $user['email'] ?? '';

/* ORDER STATS */
function countStatus($conn, $uid, $status=null){
    if($status){
        $stmt = $conn->prepare("SELECT COUNT(*) AS t FROM orders WHERE customer_id=? AND order_status=?");
        $stmt->bind_param("is", $uid, $status);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) AS t FROM orders WHERE customer_id=?");
        $stmt->bind_param("i", $uid);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['t'] ?? 0;
}

$totalOrders = countStatus($conn,$user_id);
$pending = countStatus($conn,$user_id,'Pending');
$completed = countStatus($conn,$user_id,'Completed');

/* SPENDING */
$stmt = $conn->prepare("
    SELECT SUM(total_amount) AS total 
    FROM orders 
    WHERE customer_id=? AND order_status='Completed'
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$spending = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

/* LEVEL */
$points = floor($spending);

if ($spending >= 500) {
    $level = "Gold";
    $next = null;
    $progress = 100;
} elseif ($spending >= 200) {
    $level = "Silver";
    $next = 500;
    $progress = ($spending / 500) * 100;
} else {
    $level = "Basic";
    $next = 200;
    $progress = ($spending / 200) * 100;
}

/* RECENT ORDERS */
$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE customer_id=? 
    ORDER BY order_id DESC 
    LIMIT 5
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>User Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    margin:0;
    font-family:Segoe UI;
    background:#f5f7fb;
    padding:30px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* BACK BUTTON */
.back-btn{
    display:inline-block;
    padding:10px 14px;
    background:#111;
    color:#fff;
    text-decoration:none;
    border-radius:10px;
    margin-bottom:20px;
}

/* BADGE */
.badge{
    padding:8px 14px;
    border-radius:12px;
    background:#111;
    color:#fff;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:15px;
    margin-top:20px;
}

.card{
    background:#fff;
    padding:18px;
    border-radius:14px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

/* LEVEL BAR */
.bar{
    background:#eee;
    border-radius:20px;
    height:12px;
    overflow:hidden;
}

.bar-fill{
    height:100%;
    background:linear-gradient(90deg,#2ecc71,#27ae60);
}

/* TABLE */
table{
    width:100%;
    margin-top:20px;
    background:#fff;
    border-collapse:collapse;
    border-radius:10px;
    overflow:hidden;
}

th,td{
    padding:12px;
    border-bottom:1px solid #eee;
}

th{
    background:#2ecc71;
    color:#fff;
}

/* STATUS */
.status{
    padding:5px 10px;
    border-radius:8px;
    color:#fff;
    font-size:12px;
}

.Pending{background:#f39c12;}
.Completed{background:#2ecc71;}
.Cancelled{background:#e74c3c;}

.section{
    margin-top:30px;
}
</style>
</head>

<body>

<!-- BACK BUTTON -->
<a href="homepage.php" class="back-btn">← Back to Shop</a>

<!-- HEADER -->
<div class="header">
    <div>
        <h2>Welcome, <?php echo htmlspecialchars($name); ?> 👋</h2>
        <small><?php echo htmlspecialchars($email); ?></small>
    </div>

    <div class="badge">
        Level: <?php echo $level; ?>
    </div>
</div>

<!-- LEVEL -->
<h4>Level Progress</h4>
<div class="bar">
    <div class="bar-fill" style="width:<?php echo $progress; ?>%"></div>
</div>

<p style="font-size:13px;color:#666;">
    Points: <?php echo $points; ?>
    <?php if($next): ?>
        | Need RM <?php echo ($next - $spending); ?> to next level
    <?php endif; ?>
</p>

<!-- =======================
     RECENT ORDERS (TOP)
======================= -->
<div class="section">
<h3>Recent Orders</h3>

<table>
<tr>
    <th>ID</th>
    <th>Total</th>
    <th>Status</th>
</tr>

<?php while($o = $orders->fetch_assoc()): ?>
<tr>
    <td>#<?php echo $o['order_id']; ?></td>
    <td>RM <?php echo number_format($o['total_amount'],2); ?></td>
    <td><span class="status <?php echo $o['order_status']; ?>">
        <?php echo $o['order_status']; ?>
    </span></td>
</tr>
<?php endwhile; ?>

</table>
</div>

<!-- =======================
     OVERVIEW (BOTTOM)
======================= -->
<div class="section">
<h3>Spending Overview</h3>

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
        <h3>Spending</h3>
        <p>RM <?php echo number_format($spending,2); ?></p>
    </div>
</div>
</div>

<!-- CHART -->
<canvas id="chart" height="100"></canvas>

<script>
new Chart(document.getElementById('chart'), {
    type: 'line',
    data: {
        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        datasets: [{
            label: 'Spending',
            data: [5, 20, 15, 30, 25, 40, <?php echo (int)$spending; ?>],
            borderColor: '#2ecc71',
            fill: true
        }]
    }
});
</script>

</body>
</html>