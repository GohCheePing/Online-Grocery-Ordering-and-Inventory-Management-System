<?php
session_start();
require '../db.php';

if($_POST){
$user=$_POST['username'];
$pass=$_POST['password'];

$res=$conn->query("SELECT * FROM admin WHERE username='$user'");
$a=$res->fetch_assoc();

if($a && $pass==$a['password']){
$_SESSION['admin']=1;
header("Location: dashboard.php");
}
}
?>

<form method="POST">
<input name="username">
<input name="password">
<button>Admin Login</button>
</form>