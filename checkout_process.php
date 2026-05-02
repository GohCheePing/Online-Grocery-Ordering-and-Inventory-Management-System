<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) die("Login required");
if (empty($_SESSION['cart'])) die("Cart empty");

$user_id = $_SESSION['user_id'];

$total = 0;

/* promo from cart */
$promo = $_POST['promo_code'] ?? null;
$discount = floatval($_POST['discount_amount'] ?? 0);

/* calculate */
foreach ($_SESSION['cart'] as $pid => $qty){

    $stmt = $conn->prepare("SELECT price FROM product WHERE product_id=?");
    $stmt->bind_param("i",$pid);
    $stmt->execute();
    $price = $stmt->get_result()->fetch_assoc()['price'];

    $total += $price * $qty;
}

$final = $total - $discount;
if($final < 0) $final = 0;

$conn->begin_transaction();

try{

    $stmt = $conn->prepare("INSERT INTO orders(customer_id,total_amount,order_status) VALUES(?,?, 'Pending')");
    $stmt->bind_param("id",$user_id,$final);
    $stmt->execute();

    $order_id = $conn->insert_id;

    foreach($_SESSION['cart'] as $pid=>$qty){

        $stmt = $conn->prepare("SELECT price,stock_quantity FROM product WHERE product_id=?");
        $stmt->bind_param("i",$pid);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();

        if($p['stock_quantity'] < $qty){
            throw new Exception("Stock not enough");
        }

        $stmt = $conn->prepare("INSERT INTO order_item(order_id,product_id,quantity,price) VALUES(?,?,?,?)");
        $stmt->bind_param("iiid",$order_id,$pid,$qty,$p['price']);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE product SET stock_quantity=stock_quantity-? WHERE product_id=?");
        $stmt->bind_param("ii",$qty,$pid);
        $stmt->execute();
    }

    $conn->commit();
    unset($_SESSION['cart']);

    echo "Order success. Paid RM ".number_format($final,2);

}catch(Exception $e){
    $conn->rollback();
    echo "Error: ".$e->getMessage();
}