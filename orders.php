<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT * 
    FROM `orders`
    WHERE customer_id = ?
    ORDER BY order_id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
</head>
<body>

<h2>My Orders</h2>

<?php if ($result->num_rows > 0): ?>

    <?php while($row = $result->fetch_assoc()): ?>
        <div style="border:1px solid #ccc; padding:15px; margin-bottom:10px;">
            <p>Order ID: <?php echo $row['order_id']; ?></p>
            <p>Total: RM <?php echo number_format($row['total_amount'],2); ?></p>
            <p>Status: <?php echo $row['order_status']; ?></p>
        </div>
    <?php endwhile; ?>

<?php else: ?>
    <p>No orders found.</p>
<?php endif; ?>

</body>
</html>