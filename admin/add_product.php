<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

$categories = $conn->query("SELECT * FROM category");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['product_name']);
    $price = $_POST['price'];
    $stock = $_POST['stock_quantity'];
    $min = $_POST['min_stock_level'];
    $category = $_POST['category_id'];
    $image = NULL;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $filename = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        $newName = time() . "_" . $filename;
        move_uploaded_file($tmp, "../images/" . $newName);
        $image = $newName;
    }

    $stmt = $conn->prepare("
        INSERT INTO product
        (product_name, price, stock_quantity, min_stock_level, category_id, image)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sdiiis", $name, $price, $stock, $min, $category, $image);

    if ($stmt->execute()) {
        header("Location: manage_products.php");
        exit();
    } else {
        $error = "Failed to add product";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Product</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}

body{
    min-height:100vh;
    padding:40px;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
        radial-gradient(circle at top left,#8ec5fc,transparent 30%),
        radial-gradient(circle at bottom right,#e0c3fc,transparent 30%),
        linear-gradient(135deg,#dfe9f3,#ffffff);
}

.card{
    width:560px;
    padding:40px;
    border-radius:36px;
    background:linear-gradient(
        135deg,
        rgba(255,255,255,0.32),
        rgba(255,255,255,0.12)
    );
    backdrop-filter:blur(28px) saturate(180%);
    -webkit-backdrop-filter:blur(28px) saturate(180%);
    border:1px solid rgba(255,255,255,0.42);
    box-shadow:
        inset 0 1px 1px rgba(255,255,255,0.45),
        0 20px 50px rgba(0,0,0,0.16);
}

h1{
    margin-bottom:28px;
    color:rgba(0,0,0,0.82);
    font-size:34px;
}

label{
    display:block;
    margin-bottom:8px;
    font-weight:700;
    color:#333;
}

input,
select{
    width:100%;
    padding:16px 18px;
    margin-bottom:22px;
    border:none;
    outline:none;
    border-radius:22px;
    font-size:15px;
    background:linear-gradient(
        135deg,
        rgba(255,255,255,0.42),
        rgba(255,255,255,0.16)
    );
    backdrop-filter:blur(18px);
    border:1px solid rgba(255,255,255,0.4);
    transition:0.25s;
}

input:focus,
select:focus{
    transform:translateY(-2px);
    box-shadow:
        0 0 0 4px rgba(0,122,255,0.18),
        0 10px 30px rgba(0,122,255,0.12);
}

button,
.back{
    width:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    position:relative;
    overflow:hidden;
    padding:17px;
    margin-top:10px;
    border:none;
    border-radius:24px;
    text-decoration:none;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    color:white;
    background:linear-gradient(
        135deg,
        rgba(0,122,255,0.65),
        rgba(88,86,214,0.42)
    );
    backdrop-filter:blur(24px);
    border:1px solid rgba(255,255,255,0.35);
    box-shadow:0 12px 30px rgba(0,122,255,0.2);
    transition:0.25s;
}

.back{
    background:linear-gradient(
        135deg,
        rgba(120,120,120,0.55),
        rgba(255,255,255,0.2)
    );
    color:#222;
}

button::before,
.back::before{
    content:"";
    position:absolute;
    inset:0;
    background:radial-gradient(
        circle at var(--x,50%) var(--y,50%),
        rgba(255,255,255,0.95),
        transparent 36%
    );
    opacity:0;
    transition:0.2s;
}

button:hover::before,
.back:hover::before{
    opacity:1;
}

button:hover,
.back:hover{
    transform:translateY(-5px) scale(1.03);
    box-shadow:0 22px 45px rgba(0,122,255,0.28);
}

.error{
    background:rgba(255,0,0,0.12);
    color:#b10020;
    padding:14px;
    border-radius:18px;
    margin-bottom:20px;
    text-align:center;
}
</style>
</head>

<body>

<div class="card">

<h1>Add Product</h1>

<?php if($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<label>Product Name</label>
<input type="text" name="product_name" required>

<label>Price</label>
<input type="number" step="0.01" name="price" required>

<label>Stock Quantity</label>
<input type="number" name="stock_quantity" required>

<label>Min Stock Level</label>
<input type="number" name="min_stock_level" value="5" required>

<label>Category</label>
<select name="category_id" required>
    <?php while($cat = $categories->fetch_assoc()): ?>
        <option value="<?php echo $cat['category_id']; ?>">
            <?php echo htmlspecialchars($cat['category_name']); ?>
        </option>
    <?php endwhile; ?>
</select>

<label>Image File</label>
<input type="file" name="image" required>

<button type="submit">
    Add Product
</button>

<a class="back" href="manage_products.php">
    Back
</a>

</form>

</div>

<script>
const buttons = document.querySelectorAll("button,.back");

buttons.forEach((btn)=>{
    btn.addEventListener("mousemove", function(e){
        const rect = btn.getBoundingClientRect();

        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        btn.style.setProperty("--x", x + "px");
        btn.style.setProperty("--y", y + "px");
    });
});
</script>

</body>
</html>