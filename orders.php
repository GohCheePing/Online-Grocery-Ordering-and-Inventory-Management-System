<?php
session_start();
require 'db.php';
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM `order` WHERE customer_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
echo "<h2>My Order History</h2>";
while($o = $res->fetch_assoc()){
    echo "<p>";
    echo "Order #{$o['order_id']} - ";
    echo "RM " . number_format($o['total_amount'], 2) . " - ";
    echo "<strong>Status: {$o['order_status']}</strong>";
    echo "</p><hr>";
}
if($res->num_rows == 0) {
    echo "<p>You haven't placed any orders yet. <a href='homepage.php'>Start shopping!</a></p>";
}
?>