<?php
session_start();
require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// 检查数据库库存
$res = $conn->query("SELECT stock_quantity FROM product WHERE product_id = $id");
$p = $res->fetch_assoc();

if($p && (!isset($_SESSION['cart'][$id]) || $_SESSION['cart'][$id] < $p['stock_quantity'])) {
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    echo "success";
} else {
    echo "fail";
}
exit();