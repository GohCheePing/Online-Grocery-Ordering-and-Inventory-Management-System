<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "msg" => "not logged in"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? ($_GET['id'] ?? 0));
$action = $data['action'] ?? ($_GET['action'] ?? '');
$qty = (int)($data['qty'] ?? ($_GET['qty'] ?? 0));

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function getStock($conn, $id) {
    $stmt = $conn->prepare("SELECT stock_quantity FROM product WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['stock_quantity'] ?? 0;
}

function getCartQty($cart, $id) {
    return $cart[$id] ?? 0;
}

$stock = getStock($conn, $id);
$current = getCartQty($_SESSION['cart'], $id);

if ($stock <= 0 && $action !== "remove") {
    echo json_encode(["status" => "error", "msg" => "out of stock", "cart" => $_SESSION['cart']]);
    exit();
}

/* ========================
   CORE LOGIC (SAFE VERSION)
======================== */

switch ($action) {

    case "add":
    case "plus":
        if ($current < $stock) {
            $_SESSION['cart'][$id] = $current + 1;
        }
        break;

    case "minus":
        if ($current > 1) {
            $_SESSION['cart'][$id]--;
        } else {
            unset($_SESSION['cart'][$id]);
        }
        break;

    case "set":
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } elseif ($qty <= $stock) {
            $_SESSION['cart'][$id] = $qty;
        } else {
            $_SESSION['cart'][$id] = $stock;
        }
        break;

    case "remove":
        unset($_SESSION['cart'][$id]);
        break;
}

/* ========================
   RESPONSE (REAL-TIME SAFE)
======================== */

$totalItems = array_sum($_SESSION['cart']);

echo json_encode([
    "status" => "success",
    "cart" => $_SESSION['cart'],
    "totalItems" => $totalItems,
    "stock" => $stock
]);