<?php
require 'db.php';
if($_POST){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO customer (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $pass);
    if($stmt->execute()) echo "Success! <a href='login.php'>Login here</a>";
    else echo "Error: " . $conn->error;
}
?>
<form method="POST" style="padding: 50px;">
    <h2>Customer Register</h2>
    <input name="name" placeholder="Full Name" required><br><br>
    <input name="email" type="email" placeholder="Email" required><br><br>
    <input name="password" type="password" placeholder="Password" required><br><br>
    <button type="submit">Register</button>
</form>