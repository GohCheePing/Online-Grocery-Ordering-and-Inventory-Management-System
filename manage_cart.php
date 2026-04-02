<?php
session_start();
require 'db.php';

$id=$_GET['id'];

if(!isset($_SESSION['cart'])) $_SESSION['cart']=[];

$product=$conn->query("SELECT stock_quantity FROM product WHERE product_id=$id")->fetch_assoc();

if(!isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id]=0;

if($_SESSION['cart'][$id] < $product['stock_quantity']){
    $_SESSION['cart'][$id]++;
}
?>