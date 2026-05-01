<?php
session_start();
require '../db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Password not match";
    } else {

        $check = $conn->prepare("SELECT id FROM admin WHERE email=?");
        $check->bind_param("s",$email);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $error = "Email exists";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $username = strtolower(str_replace(['@','.'], '_', $email));

            $stmt = $conn->prepare("INSERT INTO admin(username,full_name,email,admin_password) VALUES(?,?,?,?)");
            $stmt->bind_param("ssss",$username,$full_name,$email,$hash);

            if ($stmt->execute()) {
                $success = "Register success";
            } else {
                $error = "Fail";
            }
        }
    }
}
?>

<h2>Register Admin</h2>

<?php
if ($error) echo "<p style='color:red'>" . htmlspecialchars($error) . "</p>";
if ($success) echo "<p style='color:green'>" . htmlspecialchars($success) . "</p>";
?>

<form method="POST">
Full Name: <input name="full_name" required><br><br>
Email: <input name="email" type="email" required><br><br>
Password: <input type="password" name="password" required><br><br>
Confirm: <input type="password" name="confirm_password" required><br><br>
<button>Register</button>
</form>