<?php
session_start();
require 'db.php';

$conn->begin_transaction();

try{

$total=$_POST['total'];
$cust_id=$_SESSION['user_id'];

$stmt=$conn->prepare("INSERT INTO `order` (customer_id,total_amount) VALUES (?,?)");
$stmt->bind_param("id",$cust_id,$total);
$stmt->execute();

$order_id=$conn->insert_id;

foreach($_SESSION['cart'] as $id=>$qty){

$stmt=$conn->prepare("SELECT price,stock_quantity FROM product WHERE product_id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$p=$stmt->get_result()->fetch_assoc();

if($p['stock_quantity']<$qty) throw new Exception("Stock not enough");

$upd=$conn->prepare("UPDATE product SET stock_quantity=stock_quantity-? WHERE product_id=?");
$upd->bind_param("ii",$qty,$id);
$upd->execute();

$item=$conn->prepare("INSERT INTO order_item(order_id,product_id,quantity,price) VALUES(?,?,?,?)");
$item->bind_param("iiid",$order_id,$id,$qty,$p['price']);
$item->execute();
}

$conn->commit();
unset($_SESSION['cart']);

echo "Order Success <a href='homepage.php'>Back</a>";

}catch(Exception $e){
$conn->rollback();
echo "Error: ".$e->getMessage();
}
?>