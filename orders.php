<?php
session_start();
require 'db.php';

$id=$_SESSION['user_id'];

$res=$conn->query("SELECT * FROM `order` WHERE customer_id=$id");

while($o=$res->fetch_assoc()){
echo "<p>Order #{$o['order_id']} - RM {$o['total_amount']} - {$o['order_status']}</p>";
}
?>