<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$res = $conn->query("
    SELECT o.*, c.name 
    FROM `orders` o
    LEFT JOIN customer c ON o.customer_id = c.customer_id
    ORDER BY o.order_id DESC
");
?>

<h2>Orders</h2>

<table border="1">
<tr>
    <th>ID</th>
    <th>Customer</th>
    <th>Total</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = $res->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['order_id']; ?></td>
    <td><?php echo htmlspecialchars($row['name'] ?? 'Guest'); ?></td>
    <td>RM <?php echo number_format($row['total_amount'] ?? 0, 2); ?></td>
    <td><?php echo $row['order_status']; ?></td>
    <td>
        <?php if($row['order_status'] != 'Completed'): ?>
        <a href="update_status.php?id=<?php echo $row['order_id']; ?>">
            Complete
        </a>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>