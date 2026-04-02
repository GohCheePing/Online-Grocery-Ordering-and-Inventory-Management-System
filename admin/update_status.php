<?php
require '../db.php';

$id=$_GET['id'];

$conn->query("UPDATE `order` SET order_status='Completed' WHERE order_id=$id");

header("Location: dashboard.php");
?>