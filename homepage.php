<?php
session_start();
require 'db.php';

$categories = $conn->query("SELECT * FROM category");

$res = $conn->query("SELECT * FROM product");
$products=[];
while($row=$res->fetch_assoc()) $products[]=$row;
?>

<h1>FreshMart</h1>

<a href="cart.php">Cart (<?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>)</a>
<a href="orders.php">My Orders</a>
<a href="logout.php">Logout</a>

<div>
<button onclick="filter('all')">ALL</button>
<?php while($c=$categories->fetch_assoc()): ?>
<button onclick="filter('<?php echo $c['category_id']; ?>')">
<?php echo $c['category_name']; ?>
</button>
<?php endwhile; ?>
</div>

<div id="grid"></div>

<script>
const products=<?php echo json_encode($products); ?>;

function show(list){
let html="";
list.forEach(p=>{
html+=`
<div>
<h3>${p.product_name}</h3>
<p>RM ${p.price}</p>
<p>Stock: ${p.stock_quantity}</p>
<button onclick="add(${p.product_id})">Add</button>
</div>`;
});
document.getElementById("grid").innerHTML=html;
}

function filter(c){
if(c==='all') show(products);
else show(products.filter(p=>p.category_id==c));
}

function add(id){
fetch('manage_cart.php?action=add&id='+id)
.then(()=>location.reload());
}

show(products);
</script>