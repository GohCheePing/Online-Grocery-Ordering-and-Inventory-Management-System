<?php
session_start();
require 'db.php';

header("Content-Type: application/json");

if(!isset($_SESSION['user_id'])){
    echo json_encode(["status"=>"error"]);
    exit();
}

$uid = $_SESSION['user_id'];
$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id=? AND customer_id=?");
$stmt->bind_param("ii",$id,$uid);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if(!$order){
    echo json_encode(["status"=>"error"]);
    exit();
}

$stmt = $conn->prepare("
    SELECT oi.quantity, oi.price, p.product_name
    FROM order_item oi
    JOIN product p ON oi.product_id=p.product_id
    WHERE oi.order_id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();

$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    "status"=>"success",
    "order"=>$order,
    "items"=>$items
]);