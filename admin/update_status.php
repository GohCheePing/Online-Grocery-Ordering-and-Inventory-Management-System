<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid");
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("UPDATE `orders` SET order_status='Completed' WHERE order_id=?");
$stmt->bind_param("i",$id);
if (!$stmt->execute()) {
    die("Update failed");
}

header("Location: manage_orders.php");
exit();