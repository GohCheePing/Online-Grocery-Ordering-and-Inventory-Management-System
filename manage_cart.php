<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? 'add';

if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$res = $conn->query("SELECT stock_quantity FROM product WHERE product_id = $id");
$p = $res->fetch_assoc();

if($p){

    if($action == 'plus'){
        if(!isset($_SESSION['cart'][$id]) || $_SESSION['cart'][$id] < $p['stock_quantity']){
            $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
        }
    }

    elseif($action == 'minus'){
        if(isset($_SESSION['cart'][$id])){
            $_SESSION['cart'][$id]--;
            if($_SESSION['cart'][$id] <= 0){
                unset($_SESSION['cart'][$id]);
            }
        }
    }

    elseif($action == 'remove'){
        unset($_SESSION['cart'][$id]);
    }

    elseif($action == 'set'){
        $qty = (int)$_GET['qty'];

        if($qty <= 0){
            unset($_SESSION['cart'][$id]);
        } else {
            if($qty > $p['stock_quantity']){
                $qty = $p['stock_quantity'];
            }
            $_SESSION['cart'][$id] = $qty;
        }
    }

    else {
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    }

    echo json_encode([
        "status" => "success",
        "cart" => $_SESSION['cart']
    ]);

} else {
    echo json_encode(["status"=>"fail"]);
}
exit();