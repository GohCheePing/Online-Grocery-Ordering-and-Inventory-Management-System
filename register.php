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

        $check = $conn->prepare("SELECT * FROM customer WHERE email=?");
        $check->bind_param("s",$email);
        $check->execute();
        $res = $check->get_result();

        if($res->num_rows > 0){
            $error = "Email already exists";
        } else {

            $otp = rand(100000,999999);

            $_SESSION['register'] = [
                'name'=>$name,
                'email'=>$email,
                'password'=>password_hash($pass,PASSWORD_DEFAULT),
                'otp'=>$otp,
                'expire'=>time()+300
            ];

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
<link rel="stylesheet" href="auth.css">
</head>

<body>

<a href="homepage.php" class="home-btn">← Home</a>

<div class="auth-card">
    <h2>Create Account</h2>
    <p class="subtitle">Join Freshmart today</p>

    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateForm()">

        <div class="input-group">
            <label>Full Name</label>
            <input type="text" name="fullname" required>
        </div>

        <div class="input-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <div class="password-wrap">
                <input type="password" name="password" id="password" oninput="checkStrength()" required>
                <button type="button" class="toggle-password" onclick="togglePassword('password')">👁</button>
            </div>
            <small id="strengthText"></small>
        </div>

        <div class="input-group">
            <label>Confirm Password</label>
            <div class="password-wrap">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁</button>
            </div>
        </div>

        <button class="auth-btn" id="otpBtn" name="send_otp" disabled>Send OTP</button>

    </form>

    <p class="switch-text">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

<script>
function togglePassword(id){
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

// password strength check
function checkStrength(){
    const pw = document.getElementById("password").value;
    const btn = document.getElementById("otpBtn");
    const text = document.getElementById("strengthText");

    let strong =
        pw.length >= 8 &&
        /[A-Za-z]/.test(pw) &&
        /[0-9]/.test(pw);

    if(pw.length === 0){
        text.innerHTML = "";
        btn.disabled = true;
        return;
    }

    if(strong){
        text.style.color = "green";
        text.innerHTML = "Strong password ✔";
        btn.disabled = false;
    } else {
        text.style.color = "red";
        text.innerHTML = "Weak password (min 8 chars + letters + numbers)";
        btn.disabled = true;
    }
}

// final safety check
function validateForm(){
    const btn = document.getElementById("otpBtn");
    return !btn.disabled;
}
</script>

</body>
</html>