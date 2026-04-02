<?php
session_start();
require 'db.php';

$total=0;

foreach($_SESSION['cart'] as $id=>$qty){

$stmt=$conn->prepare("SELECT * FROM product WHERE product_id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$p=$stmt->get_result()->fetch_assoc();

$sub=$p['price']*$qty;
$total+=$sub;

echo "<p>{$p['product_name']} x $qty = RM $sub</p>";
}
?>

<h3>Total: RM <?php echo number_format($total,2); ?></h3>

<form action="checkout_process.php" method="POST">
<input type="hidden" name="total" value="<?php echo $total; ?>">
<button>Checkout</button>
</form>