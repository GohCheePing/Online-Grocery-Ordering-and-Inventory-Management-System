<?php
require 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"),true);

$code = strtoupper(trim($data['code']));
$subtotal = floatval($data['subtotal']);

$stmt = $conn->prepare("SELECT * FROM discount WHERE code=? AND active=1");
$stmt->bind_param("s",$code);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows == 0){
    echo json_encode([
        "status"=>"error",
        "msg"=>"Invalid promo code"
    ]);
    exit;
}

$p = $res->fetch_assoc();

if($p['type']=='percent'){
    $discount = $subtotal * ($p['value']/100);
} else {
    $discount = $p['value'];
}

echo json_encode([
    "status"=>"success",
    "discount"=>$discount,
    "code"=>$code
]);