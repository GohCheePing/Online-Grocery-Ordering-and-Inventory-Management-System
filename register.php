<?php
session_start();
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error = "";

if(isset($_POST['send_otp'])){

    $name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $cpass = $_POST['confirm_password'];

    if($pass !== $cpass){
        $error = "Password not match";
    } else {

        // check email
        $check = $conn->prepare("SELECT * FROM customer WHERE email=?");
        $check->bind_param("s",$email);
        $check->execute();
        $res = $check->get_result();

        if($res->num_rows > 0){
            $error = "Email already exists";
        } else {

            $otp = rand(100000,999999);

            // save session
            $_SESSION['register'] = [
                'name'=>$name,
                'email'=>$email,
                'password'=>password_hash($pass,PASSWORD_DEFAULT),
                'otp'=>$otp,
                'expire'=>time()+300
            ];

            // send email
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = "mmufreshmart@gmail.com";
                $mail->Password = "uffyolfuleobwwsj";
                $mail->SMTPSecure = "tls";
                $mail->Port = 587;

                $mail->setFrom("mmufreshmart@gmail.com","FreshMart");
                $mail->addAddress($email);

                $mail->Subject = "Your OTP Code";
                $mail->Body = "Your OTP is: $otp";

                $mail->send();

                header("Location: verify_otp.php");
                exit();

            } catch(Exception $e){
                $error = "Email failed: " . $mail->ErrorInfo;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
<div class="card p-4 shadow">

<h3>Register</h3>

<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<form method="POST">

<input class="form-control mb-2" name="fullname" placeholder="Full Name">
<input class="form-control mb-2" name="email" placeholder="Email">
<input class="form-control mb-2" type="password" name="password" placeholder="Password">
<input class="form-control mb-2" type="password" name="confirm_password" placeholder="Confirm Password">

<button class="btn btn-success w-100" name="send_otp">Send OTP</button>

</form>

</div>
</div>

</body>
</html>