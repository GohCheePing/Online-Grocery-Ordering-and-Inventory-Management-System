<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "msg" => "not logged in"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? 0);
$action = $data['action'] ?? '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function getStock($conn, $id) {
    $stmt = $conn->prepare("SELECT stock_quantity FROM product WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['stock_quantity'] ?? 0;
}

$stock = getStock($conn, $id);
$current = $_SESSION['cart'][$id] ?? 0;

/* PLUS */
if ($action === "plus") {

    if ($current < $stock) {
        $_SESSION['cart'][$id] = $current + 1;
    } else {
        echo json_encode(["status" => "error", "msg" => "out of stock"]);
        exit();
    }
}

/* MINUS */
elseif ($action === "minus") {

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]--;

        if ($_SESSION['cart'][$id] <= 0) {
            unset($_SESSION['cart'][$id]);
        }
    }
}

/* REMOVE */
elseif ($action === "remove") {
    unset($_SESSION['cart'][$id]);
}

/* SET */
elseif ($action === "set") {

    $qty = (int)($data['qty'] ?? 0);

    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
    }
    elseif ($qty <= $stock) {
        $_SESSION['cart'][$id] = $qty;
    }
}

echo json_encode([
    "status" => "success",
    "cart" => $_SESSION['cart']
]);