<?php
session_start();
require '../db.php';

if(!isset($_SESSION['admin'])) die("No access");

$res=$conn->query("SELECT * FROM `order`");
?>

<h2>Admin Dashboard</h2>

<?php while($o=$res->fetch_assoc()): ?>
<p>
Order #<?php echo $o['order_id']; ?> -
RM <?php echo $o['total_amount']; ?> -
<?php echo $o['order_status']; ?>

<a href="update_status.php?id=<?php echo $o['order_id']; ?>">
Complete
</a>
</p>
<?php endwhile; ?>