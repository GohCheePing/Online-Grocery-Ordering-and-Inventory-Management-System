<?php
// Start session to track login state
session_start();

// Import database connection
require '../db.php';

// Security: If the user is not an admin, block access
if(!isset($_SESSION['admin'])) {
    die("Error: No administrative access.");
}

// Fetch all orders from the database
$res = $conn->query("SELECT * FROM `order`");
?>

<h2>Admin Dashboard</h2>

<?php 
// Loop through every order in the database
while($o = $res->fetch_assoc()): 
?>
<p>
    Order #<?php echo $o['order_id']; ?> -
    RM <?php echo $o['total_amount']; ?> -
    Status: <?php echo $o['order_status']; ?>

    <a href="update_status.php?id=<?php echo $o['order_id']; ?>" style="color: green; font-weight: bold;">
        Mark as Complete
    </a>
</p>
<?php endwhile; ?>