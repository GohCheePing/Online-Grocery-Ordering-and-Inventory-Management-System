<?php
session_start();
require 'db.php';

if($_POST){
    $email=$_POST['email'];
    $pass=$_POST['password'];

    $stmt=$conn->prepare("SELECT * FROM customer WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $user=$stmt->get_result()->fetch_assoc();

    if($user && password_verify($pass,$user['password'])){
        $_SESSION['user_id']=$user['customer_id'];
        header("Location: homepage.php");
    }else{
        echo "Login Failed";
    }
}
?>

<form method="POST">
<input name="email"><br>
<input name="password" type="password"><br>
<button>Login</button>
</form>