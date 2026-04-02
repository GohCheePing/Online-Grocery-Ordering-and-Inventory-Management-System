<?php
require 'db.php';

if($_POST){
$name=$_POST['name'];
$email=$_POST['email'];
$password=password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt=$conn->prepare("INSERT INTO customer(name,email,password) VALUES(?,?,?)");
$stmt->bind_param("sss",$name,$email,$password);
$stmt->execute();

echo "Registered. <a href='login.php'>Login</a>";
}
?>

<form method="POST">
<input name="name" placeholder="Name">
<input name="email" placeholder="Email">
<input name="password" type="password">
<button>Register</button>
</form>