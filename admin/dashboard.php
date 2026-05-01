<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin = $_SESSION['admin_full_name'];

$product = $conn->query("SELECT COUNT(*) AS total FROM product")->fetch_assoc()['total'];
$order   = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
$user    = $conn->query("SELECT COUNT(*) AS total FROM customer")->fetch_assoc()['total'];

$row = $conn->query("SELECT SUM(total_amount) AS total FROM orders")->fetch_assoc();
$rev = $row['total'] ?? 0;
?>

<h2>Welcome <?php echo htmlspecialchars($admin); ?></h2>

<p>Total Products: <?php echo $product; ?></p>
<p>Total Orders: <?php echo $order; ?></p>
<p>Total Customers: <?php echo $user; ?></p>
<p>Revenue: RM <?php echo number_format($rev,2); ?></p>

<a href="manage_orders.php">Manage Orders</a><br>
<a href="logout.php">Logout</a>